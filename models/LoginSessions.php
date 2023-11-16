<?php


require_once __DIR__ . '/Model.php';
require_once __DIR__ . '/ModelsBase.php';

class LoginSession extends Model
{
    protected static $table_name = "login_sessions";
    protected static $id_name = "id";

    public $id;
    public $account_id;
    public $token;


    public function __construct($object = null)
    {
        parent::__construct($object);
    }
}

class LoginSessions extends ModelsBase
{
    protected static $table_name = "login_sessions";
    protected static $id_name = "id";
    protected static $class_name = "LoginSession";

    public static function get_by_token($token)
    {
        $sql = "SELECT * FROM login_sessions WHERE token = ?;";
        if ($stmt = self::prepare($sql)) {
            $stmt->bind_param("s", $token);
            $stmt->execute();
            $result = $stmt->get_result();
        }
        if ($result->num_rows != 0) {
            $object = (object) $result->fetch_assoc();
            return $object;
        } else {
            return false;
        }
    }
};
