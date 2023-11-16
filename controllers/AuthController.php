<?php
require_once __DIR__ . '/controller.php';

require_once __DIR__ . '/../functions/ErrorHandler.php';
require_once __DIR__ . '/../models/Accounts.php';
require_once __DIR__ . '/../models/LoginSessions.php';



class AuthController extends Controller
{
    public $account_id;
    public $authorized_by;
    public $session_token = false;

    function __construct()
    {
        $this->check_authentification();
    }

    /**
     * Set AuthController::account_id if logged in
     */
    function check_authentification()
    {
        $this->account_id = null;

        $headers = null;
        if (isset($_SERVER['Authorization'])) {
            $headers = trim($_SERVER["Authorization"]);
        } else if (isset($_SERVER['HTTP_AUTHORIZATION'])) { //Nginx or fast CGI
            $headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
        } elseif (function_exists('apache_request_headers')) {
            $requestHeaders = apache_request_headers();
            // Server-side fix for bug in old Android versions (a nice side-effect of this fix means we don't care about capitalization for Authorization)
            $requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
            if (isset($requestHeaders['Authorization'])) {
                $headers = trim($requestHeaders['Authorization']);
            }
        }

        if (!empty($headers)) {
            if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
                $matches[1];
                $token = $matches[1];

                if ($login_session = LoginSessions::get_by_token($token)) {
                    $this->account_id = $login_session->account_id;
                    $this->authorized_by = $login_session->authorized_by;
                    $this->session_token = $token;
                    return $this->account_id;
                };
                return null;
            }
        }
    }


    function check_authentification_test_request($post, $get, AuthController $auth_controller)
    {
        $response = new Response('success');
        $response->data->account_id = $auth_controller->check_authentification();
        return $response;
    }


    /**
     * Set AuthController::account_id if logged in
     * Returns error if not logged in
     */
    function require_authentification()
    {
        $this->account_id = false;

        $headers = null;
        if (isset($_SERVER['Authorization'])) {
            $headers = trim($_SERVER["Authorization"]);
        } else if (isset($_SERVER['HTTP_AUTHORIZATION'])) { //Nginx or fast CGI
            $headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
        } elseif (function_exists('apache_request_headers')) {
            $requestHeaders = apache_request_headers();
            // Server-side fix for bug in old Android versions (a nice side-effect of this fix means we don't care about capitalization for Authorization)
            $requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
            if (isset($requestHeaders['Authorization'])) {
                $headers = trim($requestHeaders['Authorization']);
            }
        }

        if (!empty($headers)) {
            if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
                $matches[1];
                $token = $matches[1];

                if ($login_session = LoginSessions::get_by_token($token)) {
                    $this->account_id = $login_session->account_id;
                    $this->session_token = $token;
                    return $this->account_id;
                } else {
                    ErrorHandler::throwError("Not Authenticated", "somethingWentWrong", 8003, 401);
                }
            } else {
                ErrorHandler::throwError("Not Authenticated", "somethingWentWrong", 8003, 401);
            }
        } else {
            if ($_SERVER["REQUEST_METHOD"] != "OPTIONS") {
                ErrorHandler::throwError("Not Authenticated", "somethingWentWrong", 8003, 401);
            }
        }
    }


    function require_authentification_test_request($post, $get, AuthController $auth_controller)
    {
        $response = new Response('success');
        $response->data->account_id = $this->require_authentification();
        return $response;
    }

    /**
     * Arguments: email, password, platform, app_version
     * Creates login session and returns token
     */
    function login_request($post, $get, AuthController $auth_controller)
    {
        require_once __DIR__ . '/../models/LoginSessions.php';
        require_once __DIR__ . '/../database/database.php';


        if ($_SERVER["REQUEST_METHOD"] == "POST") {

            $post = $this->checkVars($post, ['email', 'password'], ['device_token', 'platform', 'app_version']);

            $email_login = AccountUtilities::correct_input_email($post->email);
            $pw_login = $post->password;
            $device_token = $post->device_token;
            $platform = $post->platform;
            $app_version = $post->app_version;

            $account = Accounts::find_by('email', $email_login);
            if (!$account) {
                ErrorHandler::throwError("Account does not exists", "loginFailed", 8003, 403);
            }



            //Compare Userdata from login form and database
            $pw_hash = hash('sha256', $pw_login . $account->salt);
            if ($pw_hash == $account->password && $email_login != "") {

                //check if account is activated
                //error 423 if not mobile verified
                //error 424 if not email verified
                if (!$account->mobile_verified) {
                    $response_data = (object)[];
                    $response_data->email = $account->email;
                    $response_data->mobilenumber = $account->mobilenumber;
                    $response_data->account_id = $account->account_id;
                    $response_data->communication_language = $account->communication_language;

                    ErrorHandler::throwError("Mobile not verified. Please verify mobile.", "pleaseActivateMobile", 8004, 423, $response_data);
                }
                if (!$account->email_verified) {
                    $response_data = (object)[];
                    $response_data->email = $account->email;
                    $response_data->mobilenumber = $account->mobilenumber;
                    $response_data->account_id = $account->account_id;
                    $response_data->communication_language = $account->communication_language;
                    ErrorHandler::throwError("Email not verified. Please verify email.", "pleaseActivateEmail", 8005, 424, $response_data);
                }

                $token = "$account->account_id" . generateRandomString(80);

                $login_session = new LoginSession();
                $login_session->account_id = $account->account_id;
                $login_session->token = $token;


                LoginSessions::insert($login_session);

                $response = new Response('success');
                $response->data->account_id = $account->account_id;
                $response->data->account = $account;

                $response->data->role = $account->role;
                $response->data->email = $account->email;
                $response->data->communication_language = strtolower($account->communication_language);
                $response->data->token = $token;
                return $response;
            } else {
                ErrorHandler::throwError("Wrong credentials, login failed.", "loginFailed", 8003, 403);
            }
        }
    }

    function logout_request($post, $get, AuthController $auth_controller)
    {
        require_once __DIR__ . '/../functions/ApiResponseHeaders.php';
        require_once __DIR__ . '/../database/database.php';
        require_once __DIR__ . '/../models/LoginSessions.php';
        $auth_controller->check_authentification();

        if ($auth_controller->session_token) {
            $session = LoginSessions::get_by_token($auth_controller->session_token);
            LoginSessions::delete_by_id($session->id);
        }

        return new Response('success');
    }
}
