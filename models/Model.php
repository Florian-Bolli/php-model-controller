<?php

require_once __DIR__ . '/../database/database.php';
require_once __DIR__ . '/../functions/ErrorHandler.php';



class Model
{
    //Required: the table name of the database
    protected static $table_name;

    //Required: the name of the column of the primary key
    protected static $id_name;

    // Constructor
    // object: 
    // either (int/string) id => object is loaded from db
    // or (object/array) object => atribtes are overwritten
    public function __construct($object = null)
    {
        if (gettype($object) == 'string') $object = (int)$object;
        if (gettype($object) == 'integer') { // Initialized by id
            $this->load_by_id($object);
        } else {
            // array or object
            $this->setObject($object);
        }
    }

    // Database functions //
    static function query($sql)
    {
        global $db;
        return $db->query($sql);
    }

    static function queryInsertId($sql)
    {
        global $db;
        return $db->queryInsertId($sql);
    }

    static function prepare($sql)
    {
        global $db;
        return $db->prepare($sql);
    }


    /**
     * Gets an new object from the database
     * returns new object
     */
    public function load_by_id($id)
    {
        if (!isset($id)) return false;
        $table_name = static::$table_name;
        $id_name = static::$id_name;

        $sql = "SELECT * FROM $table_name WHERE $id_name = ?;";
        if ($stmt = self::prepare($sql)) {
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
        }
        if ($result->num_rows != 0) {
            $object = (object) $result->fetch_assoc();
            $this->setObject($object);
            return $this;
        } else {
            // throw new Exception("object not found");
            return false;
        }
    }

    /**
     * Gets an new object from the database
     * returns new object
     */
    public function load_by($atribute, $value)
    {
        if (!isset($atribute)) return false;
        $table_name = static::$table_name;
        $id_name = static::$id_name;

        $sql = "SELECT * FROM $table_name WHERE $atribute = ?;";
        if ($stmt = self::prepare($sql)) {
            $stmt->bind_param("s", $value);
            $stmt->execute();
            $result = $stmt->get_result();
        }
        if ($result->num_rows != 0) {
            $object = (object) $result->fetch_assoc();
            $this->setObject($object);
            return $this;
        } else {
            return false;
        }
    }
    public function insert()
    {
        $table_name = static::$table_name;
        $id_name = static::$id_name;
        $db_column_names = $this->get_db_column_names();

        //Make instance of Model class (Delete all atributes that are not part of the database)
        $object = $this;

        //Get all key/values object of the calss as sting3
        $values = array();
        $keyString = "";
        $valueString = "";
        foreach ($object as $key => $value) {
            if ($value == null) continue;
            if (!in_array($key, $db_column_names)) continue; // only update columns that are in db
            $key = strval($key);
            $value = strval($value);
            $keyString .= "`$key`, ";
            $valueString .= "?, ";
            array_push($values, $value);
        }

        //Remove ", " at the end
        $keyString = substr($keyString, 0, -2);
        $valueString = substr($valueString, 0, -2);

        $table_name = static::$table_name;
        //Prepare SQL, without data arguments
        $sql = "INSERT INTO `$table_name` ($keyString) VALUES ($valueString);";
        if ($stmt = self::prepare($sql)) {

            //Bind Params
            $types = str_repeat('s', count($values));
            $stmt->bind_param($types, ...$values);

            $stmt->execute();
            // var_dump($stmt);
            $id = $stmt->insert_id;
            // $this->$id_name = $id;
            return $id;
        } else {
            throw new Exception("Error: Database query failed: $sql with object " . json_encode($object));
        }
        return false;
    }



    public function update()
    {

        $table_name = static::$table_name;
        $id_name = static::$id_name;
        $db_column_names = $this->get_db_column_names();
        $object = $this;

        //Get all key/values properties of the calss as sting3
        $values = array();
        $updateString = "";
        foreach ($object as $key => $value) {
            //if ($value == null) continue;
            $key = strval($key);
            // $value = strval($value);
            if ($key == $id_name) continue; //Dont update id
            if (!in_array($key, $db_column_names)) continue; // only update columns that are in db
            $updateString .= "`$key` = ?, ";
            array_push($values, $value);
        }

        //Remove ", " at the end
        $updateString = substr($updateString, 0, -2);

        //Prepare SQL, without data arguments
        $object_id = $object->$id_name;
        $sql = "UPDATE `$table_name` SET $updateString WHERE $id_name = $object_id;";

        if ($stmt = self::prepare($sql)) {
            //Bind Params
            $types = str_repeat('s', count($values));
            $stmt->bind_param($types, ...$values);

            $stmt->execute();
            return true;
        }
        return false;
    }

    /**
     * Delete the object by ID from Database
     */
    public function delete()
    {
        $id_name = static::$id_name;
        $table_name = static::$table_name;
        $id = $this->$id_name;

        global $db;

        $sql = "DELETE FROM `$table_name` WHERE `$table_name`.`$id_name` = ?;";
        if ($stmt = $db->prepare($sql)) {
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($stmt->error) ErrorHandler::throwError("SomethingWentWrong");
        }
        return $result;
    }


    // experimental: Not all atributes of a model have to be in database
    public function get_db_column_names()
    {
        global $db;
        return $db->get_column_names(static::$table_name);
    }



    /**
     * Set the class variables according to the input object
     * The undefined class variables are set to null 
     */
    public function setObject($object)
    {
        if ($object) {
            $object = (object)$object;
            $class_vars = get_class_vars(get_class($this));
            foreach ($class_vars as $name => $v) {
                if ($name == 'table_name') continue;
                if ($name == 'id_name') continue;
                $value = isset($object->$name) ? $object->$name : null;
                //TODO: Exception on type error ( correction in obvious cases )
                if ($value !== null) {
                    $this->$name = $value;
                } else {
                    $this->$name = null;
                }
            }
        }
    }

    public function print()
    {
        echo json_encode($this);
    }

    public function __toString()
    {
        return json_encode($this);
    }
}
