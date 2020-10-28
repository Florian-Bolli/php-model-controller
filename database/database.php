<?php
// 2020-06-10
// To keep the database on order
// This is the start of refactoring:
// Database requests newer than 2020-06-10 should be functions of this class
//


class Database
{
	public $connection;

	public function __construct()
	{
		$this->open_connection();
	}

	public function open_connection()
	{
		$config = require_once(__DIR__ . '/config.php');
		$this->connection = mysqli_connect($config["DB_HOST"], $config["DB_USERNAME"], $config["DB_PASSWORD"], $config["DB_NAME"]);
		mysqli_set_charset($this->connection, 'utf8');
		if (mysqli_connect_errno()) {
			die("Database connection failed: " .
				mysql_connect_error() .
				" (" . mysql_connect_errorno() . ")");
		}
	}

	public function close_connection()
	{
		if (isset($this->connection)) {
			mysqli_close($this->connection);
			unset($this->connection);
		}
	}

	public function query($sql)
	{
		$result = mysqli_query($this->connection, $sql);
		$this->confirm_query($sql, $result);
		return $result;
	}

	public function queryInsertId($sql)
	{
		$result = mysqli_query($this->connection, $sql);
		$this->confirm_query($sql, $result);
		return mysqli_insert_id($this->connection);
	}

	private function confirm_query($sql, $result)
	{
		if (!$result) {
			die("Database query failed: '$sql'");
		}
	}

	public function prepare($sql)
	{
		$stmt = $this->connection->prepare($sql);
		return $stmt;
	}

	public function does_table_exist($table_name)
	{
		if (mysqli_query($this->connection, "DESCRIBE `$table_name`")) {
			return true;
		}
		return false;
	}


	public function log($text, $account_id = 0)
	{
		$sql = "INSERT INTO `log` (`log_id`, `api_version`, `time`, `account_id`, `text`) VALUES (NULL, 'V2.5', CURRENT_TIMESTAMP, ?, ?)";
		$stmt = $this->connection->prepare($sql);
		$stmt->bind_param("is", $account_id, $text);
		$stmt->execute();
		$stmt->close();
	}
}


$database = new Database();
$db = &$database; // both variables hold the class