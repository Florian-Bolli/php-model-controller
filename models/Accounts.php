<?php

require_once __DIR__ . '/Model.php';
require_once __DIR__ . '/ModelsBase.php';



class Account extends Model
{
    protected static $table_name = "accounts";
    protected static $id_name = "id";

    public $id;
    public $email;
    public $firstname;
    public $lastname;

    // Not represented in database:


    public function __construct($object = null)
    {
        parent::__construct($object);
    }
}

class Accounts extends ModelsBase
{
    protected static $table_name = "accounts";
    protected static $id_name = "id";
    protected static $class_name = "Account";
};
