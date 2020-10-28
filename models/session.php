<?php

require_once __DIR__ . '/ModelBase.php';

class Session extends ModelBase
{
    public $table_name = "sessions";

    //properties
    public $id;                 //int(11)
    public $account_id;         //int(11)
    public $token;              //text
    //endproperties


    public function __construct()
    {
        parent::__construct($this->table_name);
    }

    public function retreive_by_token($token)
    {
        // echo "Retreive by token: ";
        // echo $token;
        $sql = "SELECT * FROM $this->table_name WHERE token = ?;";
        if ($stmt = $this->prepare($sql)) {
            $stmt->bind_param("s", $token);
            $stmt->execute();
            $result = $stmt->get_result();
        }
        if ($row = (object) $result->fetch_assoc()) {
            if ($result->num_rows == 0) return false; //throw new Exception("Object does not exist in DB");
            $this->overwrite_atributes($row);
        } else {
            throw new Exception('Something went wrong');
        }
    }

    public function logout()
    {
        echo "\n Session object: ";
        echo json_encode($this);
        $this->delete();
    }
}