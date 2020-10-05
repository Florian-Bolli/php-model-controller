<?php

/**
 * This is the base Model class for all kinds of php object classes
 * for creating MVC applications
 * 
 * If a php class extends Model
 * 
 * 1) some basic database interaction functions are added, such as:
 * retreive($id), save(), update(), delete(), is_valid()
 * 
 * 2) some basic database helper functions are added, such as:
 * get_by_id($id), get_all(), count()
 * 
 * 3) Some (experimental) database actions are added such as:
 * create_db_table() => Creates a table in the database representing the child class
 * TODO: update_db_table() => Update talbe structure according to child class
 */

require_once __DIR__.'/../database/database.php';


class Model
{
	
	public $table_name;
	private $db;

	/**
	 * Needs to be constructed from a Object class
	 * @$table_name: The name of the table in the database 
	 * 
	 * For all functions to work:
	 * (the .php file of the class should have the same name but in singular)
	 * Example: class "Cat" in "cat.php" should have table name "cats"
	 */
	function __construct($table_name)
	{
		global $db;
		$this->db = (object)$db;
		$this->table_name = $table_name;
	}

	function query($sql){
		return $this->db->query($sql);
	}

	///////////////////////////////////////////////////
	// 1) Basic model functions
	///////////////////////////////////////////////////


	/**
	 * overrites properties of initialized element with the data of Properties array
	 */
	public function overwrite_atributes($properties){
        foreach($properties as $key => $value){
            $this->$key = $value;
        }
	}
	


	/**
	 * retreives ojbect by id from database
	 * Overwrites all atributes of the initialized object with data from DB
	 */
    public function retreive($id)
	{
        $sql = "SELECT * FROM $this->table_name WHERE id = ?;";
		if($stmt = $this->db->prepare($sql)){
			$stmt->bind_param("i", $id);
			$stmt->execute();
			$result = $stmt->get_result();
		}
		if ($row = (object) $result->fetch_assoc()) {
			if($result->num_rows == 0) throw new Exception("Object does not exist in DB");
            $this->overwrite_atributes($row);
		}
		else{
			throw new Exception('Something went wrong');
		}
	}


	
    
	/**
	 * Update all propertys of the initialized object in the DB
	 */
	public function update(){
        $properties = get_class_vars(get_class($this));
        $sql = "UPDATE `$this->table_name` SET ";

        foreach($properties as $key => $value){
            if($key == "table_name" || $key == "id"|| $key == "db") continue;
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
	public function save(){

        $properties = get_class_vars(get_class($this));
        $keyString = "";
        $valueString = "";
        foreach($properties as $key => $value){
            if($key == "table_name" || $key == "id"|| $key == "db") continue;
            $key = strval($key);
            $value = strval($this->$key);
            $keyString .= "`$key`, ";
            $valueString .= "'$value', ";
        }
        $keyString = substr($keyString, 0, -2);   
        $valueString = substr($valueString, 0, -2);   

        $sql = "INSERT INTO `$this->table_name` ($keyString) VALUES ($valueString);";
		$id = $this->db->queryInsertId($sql);
		$this->id = $id;
        return $id;
	}
	
	/**
	 * Delete the initialized object from DB
	 */
	public function delete(){
		$sql = "DELETE FROM `$this->table_name` WHERE `$this->table_name`.`id` = ?;";
		
		if($stmt = $this->db->prepare($sql)){
			$stmt->bind_param("i", $this->id);
			$stmt->execute();
			$result = $stmt->get_result();
		}
        return $result;
	}

		
	/**
	 * Validate Object:
	 * Returns true if all variables are set
	 */
	public function is_valid(){
		$properties = get_class_vars(get_class($this));

        foreach($properties as $key => $value){
            if($key == "table_name" || $key == "id"|| $key == "db") continue;
            $key = strval($key);
			if(!isset($this->$key)) return false;
			$value = strval($this->$key);
        }
	
		return true;
	}


	/**
	 * Return JSON representation of object
	 * TODO: Remove class variabes: db, table_name
	 */
	public function text(){
		return json_encode($this);
   }



   ///////////////////////////////////////////////////
   // 2) Database helper functions
   // Those functions will not change the 
   // initialized object but return new ones
   //
   // TODO: find a better solution, maybe outsouce?
   ///////////////////////////////////////////////////


	/**
	 * Get all objects of this kind from the DB 
	 * returns a list of all objects
	 */
	public function get_all()
	{
		$sql = "SELECT * FROM $this->table_name;";
		$result = $this->db->query($sql);
		$objects = [];
		while ($row = $result->fetch_assoc()) {
			$objects[] = (object) $row;
		}
		return $objects;
	}

	/**
	 * Gets an new object from the database
	 * returns new object
	 */
    public function get_by_id($id)
	{
        $sql = "SELECT * FROM $this->table_name WHERE id = ?;";
		if($stmt = $this->db->prepare($sql)){
			$stmt->bind_param("i", $id);
			$stmt->execute();
			$result = $stmt->get_result();
		}
		return (object) $result->fetch_assoc();
	}
	
	/**
	 * Delete the object by ID from Database
	 */
	public function delete_by_id($id){

		$sql = "DELETE FROM `$this->table_name` WHERE `$this->table_name`.`id` = ?;";
		if($stmt = $this->db->prepare($sql)){
			$stmt->bind_param("i", $id);
			$stmt->execute();
			$result = $stmt->get_result();
		}
        return $result;
	}

	/**
	 * Count all objects of this type in the DB
	 * returns the total number
	 */
	public function count(){
		$sql = "SELECT COUNT(*) FROM `$this->table_name`;";

		$result = $this->db->query($sql);
		$number_total = 0;
		if ($row = $result->fetch_assoc()) {
			$number_total = $row['COUNT(*)'];
		}
		return($number_total);	
    }
	
	



	///////////////////////////////////////////////////
	// 3) DATABASE ACTIONS (Experimental)
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
	public function create_db_table(){

		if($this->db->does_table_exist($this->table_name)){
			echo "Table already Exists <br>";
			return;
		}

		//get the real path of the file in folder if necessary
	    $filename = __DIR__."/".substr($this->table_name, 0, -1).".php";
		$path = realpath($filename);
		//read the file
		$lines = file($path,FILE_IGNORE_NEW_LINES);

		$lineIndex = 0;
		$propertiesIndexStart = 0;
		$propertiesIndexEnd = 0;
		//Find property lines
		foreach($lines as $line){
			if($line == "    //properties") $propertiesIndexStart = $lineIndex + 1;
			if($line == "    //endproperties") $propertiesIndexEnd = $lineIndex - 1;
			$lineIndex += 1;
		}

		$propertyLines = array();
		for($i = $propertiesIndexStart; $i<=$propertiesIndexEnd; $i++){
			array_push($propertyLines, $lines[$i]);
		}

		$properties = array();
		foreach($propertyLines as $propertyLine){
			$propertyLine = str_replace("public $","",$propertyLine);
			$propertyLine = str_replace(";","",$propertyLine);

			$arr = explode("//", $propertyLine, 2);
			$variableName = str_replace(' ','',$arr[0]);;
			$variableType = $arr[1];

			$properties["$variableName"] = $variableType;
		}	

		//Create query to create new table
		$sql = "CREATE TABLE `$this->table_name` ( ";
		foreach($properties as $key => $value){
            if($key == "table_name" || $key == "db") continue;
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
