<?php

/**
 * This is the base Models class for all kinds of php object classes
 * for creating MVC applications
 * 
 * If a php class extends Model
 * 
 * 1) some basic database helper functions are added, such as:
 * get_by_id($id), get_all(), count()
 * 
 */

require_once __DIR__ . '/../database/database.php';
require_once __DIR__ . '/../functions/ErrorHandler.php';


class ModelsBase
{
	//Required: the table name of the database
	protected static $table_name;

	//Required: the name of the column of the primary key
	protected static $id_name;

	//Optional: defines the Class of the requred and returned objects
	protected static $class_name;


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


	// experimental: Not all atributes of a model have to be in database
	public static function get_db_column_names()
	{
		global $db;
		return $db->get_column_names(static::$table_name);
	}


	/**
	 * Get all objects of this kind from the DB 
	 * returns a list of all objects
	 */
	public static function get_all()
	{
		$table_name = static::$table_name;
		$sql = "SELECT * FROM $table_name;";
		$result = self::query($sql);
		$objects = [];
		while ($row = $result->fetch_assoc()) {
			$object = (object)$row;

			//Convert to desired class
			if (isset(static::$class_name)) {
				$object = new static::$class_name($object);
			}

			$objects[] = $object;
		}
		return $objects;
	}

	/**
	 * Gets an new object from the database
	 * returns new object
	 */
	// DEPRECATED: Use Model.php model->load_by_id() instead

	public static function get_by_id($id)
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
			//Convert to desired class
			if (isset(static::$class_name)) {
				$object = new static::$class_name($object);
			}

			return $object;
		} else {
			return false;
		}
	}

	// DEPRECATED: Use Model.php model->load_by($attribute, $value) instead
	public static function find_by($attribute, $value)
	{
		$table_name = static::$table_name;
		$sql = "SELECT * FROM $table_name WHERE $attribute = ?;";
		if ($stmt = self::prepare($sql)) {
			$stmt->bind_param("s", $value);
			$stmt->execute();
			$result = $stmt->get_result();
		}
		if ($result->num_rows != 0) {
			$object = (object) $result->fetch_assoc();
			//Convert to desired class
			if (isset(static::$class_name)) {
				$object = new static::$class_name($object);
			}

			return $object;
		} else {
			return false;
		}
	}

	/**
	 * returns all objects from the database where object->attribute = value 
	 */
	public static function get_by($attribute, $value)
	{
		$table_name = static::$table_name;

		$objects = array();

		$sql = "SELECT * FROM $table_name WHERE $attribute = ? ;";
		$stmt = self::prepare($sql);
		$stmt->bind_param("s", $value);
		$stmt->execute();
		$result = $stmt->get_result();
		while ($object = $result->fetch_assoc()) {
			//Convert to desired class
			if (isset(static::$class_name)) {
				$object = new static::$class_name($object);
			} else {
				$object = (object)$object;
			}
			$objects[] = $object;
		}

		return $objects;
	}



	/**
	 * Gets an new object from the database by token
	 * returns new object
	 */
	public static function get_by_token($token)
	{
		if (!isset($token)) return false;
		$table_name = static::$table_name;
		$sql = "SELECT * FROM $table_name WHERE token = ?;";
		if ($stmt = self::prepare($sql)) {
			$stmt->bind_param("s", $token);
			$stmt->execute();
			$result = $stmt->get_result();
		}
		if ($result->num_rows != 0) {
			$object = (object) $result->fetch_assoc();

			//Convert to desired class
			if (isset(static::$class_name)) {
				$object = new static::$class_name($object);
			}

			return $object;
		} else {
			return false;
		}
	}

	// DEPRECATED: Use Model.php model->delete() instead
	public static function delete_by_id($id)
	{
		$id_name = static::$id_name;
		$table_name = static::$table_name;

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

	/**
	 * Count all objects of this type in the DB
	 * returns the total number
	 */
	public static function count()
	{
		global $db;
		$table_name = static::$table_name;

		$sql = "SELECT COUNT(*) FROM `$table_name`;";

		$result = $db->query($sql);
		$number_total = 0;
		if ($row = $result->fetch_assoc()) {
			$number_total = $row['COUNT(*)'];
		}
		return (intval($number_total));
	}

	// DEPRECATED: Use Model.php model->insert() instead
	public static function insert($object)
	{
		$table_name = static::$table_name;
		$class_name = static::$class_name;
		$db_column_names = self::get_db_column_names();

		//Make instance of Model class (Delete all atributes that are not part of the database)
		$object = new $class_name($object);

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

			// TODO: check if failed...
			// var_dump($stmt);
			$id = $stmt->insert_id;
			return $id;
		} else {
			throw new Exception("Error: Database query failed: $sql with object " . json_encode($object));
		}
		return false;
	}

	// DEPRECATED: Use Model.php model->update() instead
	public static function update($object)
	{

		$table_name = static::$table_name;
		$id_name = static::$id_name;
		$class_name = static::$class_name;
		$db_column_names = self::get_db_column_names();

		// Instance of model class
		$object = new $class_name($object);

		//Get all key/values properties of the calss as sting3
		$values = array();
		$updateString = "";
		foreach ($object as $key => $value) {
			$key = strval($key);
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


	public static function get_by_account_id($account_id)
	{
		$table_name = static::$table_name;

		$objects = array();

		$sql = "SELECT * FROM $table_name WHERE account_id = ? ;";
		$stmt = self::prepare($sql);
		$stmt->bind_param("i", $account_id);
		$stmt->execute();
		$result = $stmt->get_result();
		while ($object = $result->fetch_assoc()) {
			//Convert to desired class
			if (isset(static::$class_name)) {
				$object = new static::$class_name($object);
			} else {
				$object = (object)$object;
			}
			$objects[] = $object;
		}

		return $objects;
	}

	public static function get_by_contact_id($account_id)
	{
		$table_name = static::$table_name;

		$objects = array();

		$sql = "SELECT * FROM $table_name WHERE contact_id = ? ;";
		$stmt = self::prepare($sql);
		$stmt->bind_param("i", $account_id);
		$stmt->execute();
		$result = $stmt->get_result();
		while ($object = $result->fetch_assoc()) {
			//Convert to desired class
			if (isset(static::$class_name)) {
				$object = new static::$class_name($object);
			} else {
				$object = (object)$object;
			}
			$objects[] = $object;
		}

		return $objects;
	}


	public static function get_by_mooring_id($account_id)
	{
		$table_name = static::$table_name;

		$objects = array();

		$sql = "SELECT * FROM $table_name WHERE mooring_id = ? ;";
		$stmt = self::prepare($sql);
		$stmt->bind_param("i", $account_id);
		$stmt->execute();
		$result = $stmt->get_result();
		while ($object = $result->fetch_assoc()) {
			//Convert to desired class
			if (isset(static::$class_name)) {
				$object = new static::$class_name($object);
			} else {
				$object = (object)$object;
			}
			$objects[] = $object;
		}

		return $objects;
	}

	public static function get_by_booking_id($account_id)
	{
		$table_name = static::$table_name;

		$objects = array();

		$sql = "SELECT * FROM $table_name WHERE booking_id = ? ;";
		$stmt = self::prepare($sql);
		$stmt->bind_param("i", $account_id);
		$stmt->execute();
		$result = $stmt->get_result();
		while ($object = $result->fetch_assoc()) {
			//Convert to desired class
			if (isset(static::$class_name)) {
				$object = new static::$class_name($object);
			} else {
				$object = (object)$object;
			}
			$objects[] = $object;
		}

		return $objects;
	}


	/**
	 * Only Possible when the object has an atribute account_id
	 */
	public static function delete_by_account_id($account_id)
	{
		$table_name = static::$table_name;
		$sql = "DELETE FROM $table_name WHERE account_id = $account_id ";
		self::query($sql);
	}
}
