<?php

require_once __DIR__.'/model.php';

class Dog extends Model{
    public $table_name = "dogs";

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


    public function __construct()
    {
        parent::__construct($this->table_name);
    } 
}

?>