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
 * 2) Some (experimental) database actions are added such as:
 * create_db_table() => Creates a table in the database representing the child class
 * TODO: update_db_table() => Update talbe structure according to child class
 */

require_once __DIR__ . '/../database/database.php';


class ModelsBase
{
	protected static $table_name;
	protected static $object_name;


	static function query($sql)
	{
		global $db;
		return $db->query($sql);
	}

	static function prepare($sql)
	{
		global $db;
		return $db->prepare($sql);
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
			$object = new static::$object_name($row);
			$objects[] = $object;
		}
		return $objects;
	}

	/**
	 * Gets an new object from the database
	 * returns new object
	 */
	public static function get_by_id($id)
	{
		$table_name = static::$table_name;
		$sql = "SELECT * FROM $table_name WHERE id = ?;";
		if ($stmt = self::prepare($sql)) {
			$stmt->bind_param("i", $id);
			$stmt->execute();
			$result = $stmt->get_result();
		}
		if ($result->num_rows != 0) {
			$attributes = (object) $result->fetch_assoc();
			return new static::$object_name($attributes);
		} else {
			return false;
		}
	}

	/**
	 * Delete the object by ID from Database
	 */
	public static function delete_by_id($id)
	{
		global $db;
		$table_name = static::$table_name;

		$sql = "DELETE FROM `$table_name` WHERE `$table_name`.`id` = ?;";
		if ($stmt = $db->prepare($sql)) {
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
}