<?php

require_once __DIR__ . '/Model.php';
require_once __DIR__ . '/ModelsBase.php';



class Cat extends Model
{
    protected static $table_name = "cats";
    protected static $id_name = "id";

    public $id;
    public $name;
    public $gender;
    public $date_of_birth;
    public $weight;
    public $pretty;
    public $teeth;

    // Not represented in database:
    public $color;

    public function __construct($object = null)
    {
        parent::__construct($object);
    }
}

class Cats extends ModelsBase
{
    protected static $table_name = "cats";
    protected static $id_name = "id";
    protected static $class_name = "Cat";

    public static function get_all_males()
    {
        $cats = array();

        $sql = "SELECT * FROM cats WHERE gender = 'male';";
        $stmt = self::prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($poi = $result->fetch_assoc()) {
            $cats[] = new Cat((object)$poi);
        }

        return $cats;
    }
};
