<?php

require_once __DIR__ . '/controller.php';
require_once __DIR__ . '/../models/session.php';


class AuthController extends Controller
{
    public $session;
    public $logged_in = false;

    function __construct($request_method)
    {
        $session = new Session();
        $this->session = $session;
        parent::__construct($session, null, $request_method, 0);
    }

    /**
     * Controller functions are called by the api like the following:
     * https://api.example.ch/auth/login
     */
    function login()
    {
        require_once './util/utils.php';

        $post = (object)json_decode(file_get_contents('php://input'), true);
        //TODO: Take email and password from account table
        if ($post->email == "test@sample.ch" && $post->password == "test") {
            $this->session->account_id = 2;
            $this->session->token = Utils::generateRandomString(100);
            $this->session->save();
            $response = (object)[];
            $response->status = "success";
            $response->message = "Authentication successful";
            $response->token = $this->session->token;
            echo json_encode($response);
        } else {
            $response = (object)[];
            $response->status = "fail";
            $response->message = "Authentication failed.";
            //http_response_code(400);
            echo json_encode($response);
        }
    }

    function logout()
    {

        // echo "\n LOGOUT: ";
        // echo json_encode($this->session);

        $response = (object)[];

        $this->session->delete();
        $response->status = "success";
        $response->message = "Logout successful";

        echo json_encode($response);
    }

    function is_authenticated()
    {
        if (isset($this->session->id)) return $this->session->id;
        else $this->session->id = null;
    }

    function require_authentification()
    {
        if (isset($this->session->id)) return $this->session->id;
        else $this->session_expired();
    }


    function session_expired()
    {
        $errorText = '{"Error":"Session expired"}';
        echo $errorText;
        http_response_code(403);
        exit;
    }


    function set_logged_in_id()
    {
        //Get auth header
        $headers = null;
        if (isset($_SERVER['Authorization'])) {
            $headers = trim($_SERVER["Authorization"]);
        } else if (isset($_SERVER['HTTP_AUTHORIZATION'])) { //Nginx or fast CGI
            $headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
        } elseif (function_exists('apache_request_headers')) {
            $requestHeaders = apache_request_headers();
            // Server-side fix for bug in old Android versions (a nice side-effect of this fix means we don't care about capitalization for Authorization)
            $requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
            //print_r($requestHeaders);
            if (isset($requestHeaders['Authorization'])) {
                $headers = trim($requestHeaders['Authorization']);
            }
        }
        //Get bearer 
        if (!empty($headers)) {
            if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
                $matches[1];
                $token = $matches[1];
                $this->session->retreive_by_token($token);


                // echo "\n set_logged_in_id: ";
                // echo json_encode($this->session);
            }
        }
    }
}