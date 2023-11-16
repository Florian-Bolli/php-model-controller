<?php

require_once __DIR__ . "/../database/database.php";
require_once __DIR__ . "/../functions/mailer.php";
require_once __DIR__ . "/../config.php";

define('EMERGENCY', 'emergency');
define('ALERT', 'alert');
define('CRITICAL', 'critical');
define('ERROR', 'error');
define('WARNING', 'warning');
define('NOTICE', 'notice');
define('INFO', 'info');
define('DEBUG', 'debug');

class Logger
{
    private $account_id = null;
    private $authorized_by = null;

    function __construct($account_id = null, $authorized_by = null)
    {
        $this->account_id = $account_id;
        $this->authorized_by = $authorized_by;
    }
    function log(string $level, string $message, array $context = [])
    {
        global $db;
        $db->log($message, $level, $this->account_id, $this->authorized_by);
    }
    function debug(string $message, array $context = [])
    {
        $this->log(DEBUG, $message, $context);
    }
    function info(string $message, array $context = [])
    {
        $this->log(INFO, $message, $context);
    }
    function notice(string $message, array $context = [])
    {
        $this->log(NOTICE, $message, $context);
    }
    function warning(string $message, array $context = [])
    {
        $this->log(WARNING, $message, $context);
    }
    function error(string $message, array $context = [])
    {
        $this->log(ERROR, $message, $context);
    }
    function critical(string $message, array $context = [])
    {
        $this->log(CRITICAL, $message, $context);
    }
    function emergency(string $message, array $context = [])
    {
        Mailer::sendEmail("mail@florianbolli.ch", "Boatpark Emergency Log", $message);
        // if (Config::ENV != "dev")  Mailer::sendEmail("info@boatpark.app", "Boatpark Emergency Log", $message);

        $this->log(EMERGENCY, $message, $context);
    }
}


$log = new Logger();

function myErrorHandler($errno, $errstr, $errfile, $errline)
{
    global $log;
    $text = "SERVER ERROR: [$errno] $errstr Error on line $errline in $errfile";
    $log->error($text);
}

// error listeners
set_error_handler("myErrorHandler", E_ALL);
register_shutdown_function(
    function () {
        $err = error_get_last();
        if (!is_null($err)) {
            $err = (object)$err;
            global $log;
            $text = "FATAL SERVER ERROR: [$err->type] $err->message Error on line $err->line in $err->file";
            $log->error($text);
        }
    }
);
