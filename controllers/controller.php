<?php

require_once __DIR__ . '/../functions/ErrorHandler.php';
require_once __DIR__ . '/../functions/Response.php';

/**
 * Object Controllers reachable through the API should extend the Controller class, to add standard functions and the process_request function
 */
class Controller
{

    /**
     * Processes the standard requests: 
     * Some standard (get, index, create, update, delete ) methodes are already implemented
     * The rights have to be set for  the functions to work
     */
    function process_request($request_type)
    {
        //Non standard request
        if ($request_type != NULL) {
            if (method_exists($this, $request_type)) {
                try {
                    $this->$request_type();
                } catch (ResponseException $e) {
                    ErrorHandler::handleExceptionAndDie($e);
                }
                return;
            } else {
                $this->functionDoesNotExist();
            }
        }
    }

    // Respond standard request to api_endpoint
    // The function has to be implemented in a controller named "function_name_request" and has to reveice ($post, $get, $auth_controller)
    // The api endpoint has to return a Response object, which is echoed here
    function respond_request($funcion_name)
    {
        $post = (object)json_decode(file_get_contents('php://input'), true);
        $get = (object)$_GET;
        $auth_controller = new AuthController();

        //Non standard request
        if ($funcion_name != NULL) {
            $funcion_name = $funcion_name . "_request";
            if (method_exists($this, $funcion_name)) {
                try {
                    $response = $this->$funcion_name($post, $get, $auth_controller);
                    if ($response instanceof Response) {
                        echo $response;
                    } else {
                        ErrorHandler::throwError("No response object");
                    }
                } catch (ResponseException $e) {
                    ErrorHandler::handleExceptionAndDie($e);
                }
                return;
            } else {
                $this->functionDoesNotExist();
            }
        }
    }

    // Check if all required vars occur in the request object
    public static function checkRequiredVars(object $object, array $required_vars)
    {
        foreach ($required_vars as $var) {
            if (!isset($object->$var)) {
                ErrorHandler::throwException("required variables: " . json_encode($required_vars), "invalidArguments");
            }
        }
    }

    // Set optional vars of request object to null if they are nor defined
    public static function checkOptionalVars(object $object, array $optional_vars)
    {
        foreach ($optional_vars as $var) {
            $object->$var = isset($object->$var) ? $object->$var : null;
        }
        return $object;
    }

    // Remove all variables of the request object that are not defined in required_vars or optional_vars
    public static function removeRedundantVars(object $object, $required_vars, $optional_vars)
    {
        $possible_vars = array_merge($required_vars, $optional_vars);
        foreach ($object as $key => $value) {
            $key = strval($key);
            if (!in_array($key, $possible_vars)) {
                unset($object->$key);
            }
        }
        return $object;
    }

    // Returns a clean object, that contains and contains only required and optional vars
    public static function checkVars(object $object, array $required_vars, array $optional_vars = [])
    {
        self::checkRequiredVars($object, $required_vars);
        $object = self::removeRedundantVars($object, $required_vars, $optional_vars);
        $object = self::checkOptionalVars($object, $optional_vars);
        return $object;
    }

    // Standard outputs$
    function notAuthenticated()
    {
        echo '{"Error": "Not authenticated"}';
        die();
    }

    function notAuthorized()
    {
        echo '{"Error": "Not authorized"}';
        http_response_code(403);
        die();
    }

    function functionDoesNotExist()
    {
        echo '{"Error": "Function does not exist"}';
        die();
    }
}
