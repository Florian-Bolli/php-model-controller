<?php

require_once __DIR__ . '/controller.php';
require_once __DIR__ . '/../models/session.php';


class AuthController extends Controller
{
    private $session;
    public $logged_in = false;

    function __construct($request_method, $id)
    {
        $session = new Session();
        $this->session = $session;
        $this->session->id = $id;
        parent::__construct($session, $request_method, $id);
    }

    /**
     * Controller functions are called by the api like the following:
     * https://api.example.ch/cats/{id}/increase_weight
     */
    function login()
    {
        $this->session->account_id = 2;
        $this->session->token = "HEHSDFNSDFNSDFNSDFUNSDFSUDN123123n123n123n123n123n123n";
        $this->session->save();

        echo $this->session->token;
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

                if (!isset($resp[0])) {
                    $errorText = 'pleaseLoginAgain';
                    echo $errorText;
                    http_response_code(403);
                    exit;
                }
                $loginid = $resp[0];
                return array($loginid, $token);
            }
        } else {
            $errorText = 'pleaseLoginAgain';
            echo $errorText;
            http_response_code(403);
            exit;
        }
    }
}
