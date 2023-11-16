<?php

require_once __DIR__ . '/../controllers/logger.php';
class ErrorHandler
{
    public $error;
    function __construct()
    {
        $this->error = null;
    }

    public static function throwError($message, $language_variable = "somethingWentWrong", $error_code = 0, $http_statuscode = 400, $data = [])
    {
        $data = (object) $data;
        $error = $data;
        $error->message = $message;
        $error->language_variable = $language_variable;
        $error->error_code = $error_code;
        $error->http_statuscode = $http_statuscode;
        $error->status = "error";

        echo json_encode($error);
        http_response_code($error->http_statuscode);

        $log = new Logger();
        $log->error($error->message);
        # Throw exception and catch at top level
        die();
    }

    public static function throwException($message, $language_variable = "somethingWentWrong", $error_code = 0, $http_statuscode = 400, $data = [])
    {
        $data = (object) $data;
        $error = $data;
        $error->message = $message;
        $error->language_variable = $language_variable;
        $error->error_code = $error_code;
        $error->http_statuscode = $http_statuscode;
        $error->status = "error";

        throw new ResponseException("Error", $error);


        # Throw exception and catch at top level

    }
    public static function handleExceptionAndDie(ResponseException $e)
    {
        $error = $e->getData();
        echo json_encode($error);
        http_response_code($error->http_statuscode);
    }
}


class ResponseException extends Exception
{
    private $_data = '';

    public function __construct($message, $data = [])
    {
        $this->_data = (object)$data;
        parent::__construct($message);
    }

    public function getData()
    {
        return $this->_data;
    }
}
