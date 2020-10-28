<?php

require_once __DIR__ . '/ModelBase.php';
require_once __DIR__ . '/ModelsBase.php';

class Cat extends ModelBase
{
    //properties
    public $id;                 //int(11)
    public $name;               //text
    public $gender;             //text
    public $date_of_birth;      //date
    public $weight;             //decimal(9,2)
    public $pretty;             //bool
    public $teeth;              //int(11)
    //endproperties


    public function __construct($object)
    {
        if ($object) {
            $this->overwrite_atributes($object);
        }
        parent::__construct("cats");
    }


    public function increase_weight($delta)
    {
        $sql = "UPDATE `cats` SET `weight` = weight + $delta WHERE `cats`.`id` = $this->id; ";
        return $this->query($sql);
    }
}


class Cats extends ModelsBase
{
    protected static $table_name = "cats";
    protected static $object_name = "Cat";

    public function showOddId()
    {
        $sql = "SELECT * FROM `cats` WHERE ( id % 2 ) = 0";
        return $this->query($sql);
    }
}