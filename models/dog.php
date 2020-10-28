<?php


require_once __DIR__ . '/ModelBase.php';
require_once __DIR__ . '/ModelsBase.php';

class Dog extends ModelBase
{

    //properties
    public $id;                 //int(11)
    public $name;               //text
    public $gender;             //text
    public $date_of_birth;      //date
    public $weight;             //decimal(9,2)
    public $strong;             //bool
    public $race;               //text
    public $teeth;              //int(11)
    //endproperties


    public function __construct($object)
    {
        $this->overwrite_atributes($object);
        parent::__construct("dogs");
    }
}



class Dogs extends ModelsBase
{
    protected static $table_name = "dogs";
    protected static $object_name = "Dog";
}