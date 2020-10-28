<?php

/**
 * This is the base Model class for all kinds of php object classes
 * for creating MVC applications
 * 
 * If a php class extends Model
 * 
 * some basic database interaction functions are added, such as:
 * retreive($id), save(), update(), delete(), is_valid()
 * 
 */

require_once __DIR__ . '/../database/database.php';


class ModelBase
{

	public $table_name;
	private $db;

	/**
	 * The Base model, with the basic functions for all Objects, which need to be stored in the database
	 * Needs to be constructed from a Object class
	 * @$table_name: The name of the table in the database
	 */
	function __construct($table_name)
	{
		global $db;
		$this->db = (object)$db;
		$this->table_name = $table_name;
	}

	function query($sql)
	{
		return $this->db->query($sql);
	}

	function prepare($sql)
	{
		return $this->db->prepare($sql);
	}

	function getMyDb()
	{
		return $this->db;
	}

	///////////////////////////////////////////////////
	// Basic model functions => Save, Update, Delete
	///////////////////////////////////////////////////


	/**
	 * overrites properties of initialized element with the data of Properties array
	 */
	public function overwrite_atributes($properties)
	{
		foreach ($properties as $key => $value) {
			$this->$key = $value;
		}
	}



	/**
	 * Update all propertys of the initialized object in the DB
	 * TODO: Make injection save
	 */
	public function update()
	{
		$properties = get_class_vars(get_class($this));
		$sql = "UPDATE `$this->table_name` SET ";

		foreach ($properties as $key => $value) {
			if ($key == "table_name" || $key == "id" || $key == "db") continue;
			$key = strval($key);
			$value = strval($this->$key);
			$sql .= "`$key` = '$value', ";
		}

		$sql = substr($sql, 0, -2);
		$sql .= " WHERE `$this->table_name`.`id` = $this->id;";

		$result = $this->db->query($sql);
		return $result;
	}


	/**
	 * Save the initialized object (all properties) to the database
	 */
	public function save()
	{
		//Get all key/values properties of the calss as sting
		$properties = get_class_vars(get_class($this));
		$keyString = "";
		$valueString = "";
		$values = array();
		foreach ($properties as $key => $value) {
			if ($key == "table_name" || $key == "id" || $key == "db") continue;
			$key = strval($key);
			$value = strval($this->$key);
			$keyString .= "`$key`, ";
			$valueString .= "?, ";
			array_push($values, $value);
		}

		//Remove ", " at the end
		$keyString = substr($keyString, 0, -2);
		$valueString = substr($valueString, 0, -2);

		//Prepare SQL, without data arguments
		$sql = "INSERT INTO `$this->table_name` ($keyString) VALUES ($valueString);";
		if ($stmt = $this->prepare($sql)) {

			//Bind Params
			$types = str_repeat('s', count($values));
			$stmt->bind_param($types, ...$values);

			$stmt->execute();
			$id = mysqli_insert_id($this->db->connection);
			return $id;
		}
		return false;
	}


	/**
	 * Delete the initialized object from DB
	 */
	public function delete()
	{

		$sql = "DELETE FROM `$this->table_name` WHERE `$this->table_name`.`id` = ?;";
		$this->id;

		if ($stmt = $this->db->prepare($sql)) {
			$stmt->bind_param("i", $this->id);
			$stmt->execute();
			$result = $stmt->get_result();
			//Clean class obj
			$properties = get_class_vars(get_class($this));
			foreach ($properties as $key => $value) {
				$this->$key = null;
			}
		} else {
			$result = false;
		}
		return $result;
	}


	/**
	 * Validate Object:
	 * Returns true if all variables are set
	 */
	public function is_valid()
	{
		$properties = get_class_vars(get_class($this));

		foreach ($properties as $key => $value) {
			if ($key == "table_name" || $key == "id" || $key == "db") continue;
			$key = strval($key);
			if (!isset($this->$key)) return false;
			$value = strval($this->$key);
		}

		return true;
	}


	/**
	 * Return JSON representation of object
	 * TODO: Remove class variabes: db, table_name
	 */
	public function text()
	{
		return json_encode($this);
	}




	///////////////////////////////////////////////////
	// 2) DATABASE ACTIONS (Experimental)
	///////////////////////////////////////////////////

	/**
	 * Experimental:
	 * Creates a new table in the database
	 * 
	 * The .php file of the class should have the same name but in singular
	 * Example: class "Cat" in "cat.php" should have table name "cats"
	 * 
	 * To declare the atribute types, use comments:
	 * 
	 * //properties
	 * public $atributeName   //data type
	 * ... list all atributes
	 * //endproperties
	 * 
	 * for example
	 * //properties
	 * public $id;                 //int(11)
	 * public $name;               //text
	 * public $date_of_birth;      //date
	 * public $weight;             //decimal(9,2)
	 * public $pretty;             //bool
	 * public $teeth;              //int(11)
	 * //endproperties
	 * 
	 */
	public function create_db_table()
	{

		if ($this->db->does_table_exist($this->table_name)) {
			echo "Table already Exists <br>";
			return;
		}

		//get the real path of the file in folder if necessary
		$filename = __DIR__ . "/" . substr($this->table_name, 0, -1) . ".php";
		$path = realpath($filename);
		//read the file
		$lines = file($path, FILE_IGNORE_NEW_LINES);

		$lineIndex = 0;
		$propertiesIndexStart = 0;
		$propertiesIndexEnd = 0;
		//Find property lines
		foreach ($lines as $line) {
			if ($line == "    //properties") $propertiesIndexStart = $lineIndex + 1;
			if ($line == "    //endproperties") $propertiesIndexEnd = $lineIndex - 1;
			$lineIndex += 1;
		}

		$propertyLines = array();
		for ($i = $propertiesIndexStart; $i <= $propertiesIndexEnd; $i++) {
			array_push($propertyLines, $lines[$i]);
		}

		$properties = array();
		foreach ($propertyLines as $propertyLine) {
			$propertyLine = str_replace("public $", "", $propertyLine);
			$propertyLine = str_replace(";", "", $propertyLine);

			$arr = explode("//", $propertyLine, 2);
			$variableName = str_replace(' ', '', $arr[0]);;
			$variableType = $arr[1];

			$properties["$variableName"] = $variableType;
		}

		//Create query to create new table
		$sql = "CREATE TABLE `$this->table_name` ( ";
		foreach ($properties as $key => $value) {
			if ($key == "table_name" || $key == "db") continue;
			$key = strval($key);
			$value = strval($value);
			$sql .= "`$key` $value, ";
		}
		$sql = substr($sql, 0, -2);
		$sql .= ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
		$this->db->query($sql);

		//Add table Properties
		$sql = "ALTER TABLE `$this->table_name` ADD PRIMARY KEY (`id`);";
		$this->db->query($sql);
		$sql = "ALTER TABLE `$this->table_name` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;";
		$this->db->query($sql);

		echo "Creation Successful <br>";
	}
}