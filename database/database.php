<?php
// 2020-06-10
// To keep the database on order
// This is the start of refactoring:
// Database requests newer than 2020-06-10 should be functions of this class
//

require_once __DIR__ . '/../functions/ErrorHandler.php';
require_once __DIR__ . '/../controllers/logger.php';
require_once(__DIR__ . '/../config.php');

class MySQLDatabase
{
	private $connection;
	private $db_name;

	function __construct()
	{
		$this->open_connection();
	}

	public function open_connection()
	{
		$this->connection = mysqli_connect(Config::DB_HOST, Config::DB_USERNAME, Config::DB_PASSWORD, Config::DB_NAME);
		$this->db_name = Config::DB_NAME;

		mysqli_set_charset($this->connection, 'utf8');
		mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
		if (mysqli_connect_errno()) {
			die("Database connection failed: " .
				mysqli_connect_errno() .
				" (" . mysqli_connect_errno() . ")");
		}
	}

	public function open_connection_with_config($DB_HOST, $DB_USERNAME, $DB_PASSWORD, $DB_NAME)
	{
		$this->connection = mysqli_connect($DB_HOST, $DB_USERNAME, $DB_PASSWORD, $DB_NAME);
		$this->db_name = $DB_NAME;

		mysqli_set_charset($this->connection, 'utf8');
		mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
		if (mysqli_connect_errno()) {
			die("Database connection failed: " .
				mysqli_connect_errno() .
				" (" . mysqli_connect_errno() . ")");
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

	public function multi_query($sql)
	{
		$result = mysqli_multi_query($this->connection, $sql);
		$this->confirm_query($sql, $result);
		return $result;
	}

	public function prepare($sql)
	{
		$stmt = $this->connection->prepare($sql);
		return $stmt;
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
			$log = new Logger();
			$log->error("Database query failed: '$sql'");
			die("Database query failed: '$sql'");
		}
	}

	public function mysql_prep($string)
	{
		$escaped_string = mysqli_real_escape_string($this->connection, $string);
		return $escaped_string;
	}


	public function log($text, $level = "debug", $account_id = null, $authozied_by = null)
	{
		$sql = "INSERT INTO `log` (`id`, `time`, `account_id`, `level`, `text`) VALUES (NULL, CURRENT_TIMESTAMP, ?, ?, ?)";
		$stmt = $this->connection->prepare($sql);
		$stmt->bind_param("iiss", $account_id, $level, $text);
		$stmt->execute();
		$stmt->close();
	}

	public function get_logs_by_text($text)
	{

		$sql = "SELECT * FROM log WHERE text LIKE '%$text%';";
		if ($stmt = self::prepare($sql)) {
			$stmt->execute();
			$result = $stmt->get_result();
		}
		if ($result->num_rows != 0) {
			$object = (object) $result->fetch_assoc();
			return $object;
		} else {
			// throw new Exception("object not found");
			return false;
		}
	}


	public function runSQLReadRows($sql, $rowNames)
	{

		$result = $this->query($sql);
		//declare 2d array for return value
		$arr = array();
		foreach ($rowNames as $rowName) {
			$arr[$rowName] = array();
		}

		//fill 2d array
		$i = 0;
		while ($row = $result->fetch_assoc()) {

			foreach ($rowNames as $rowName) {
				$arr[$rowName][$i] = $row[$rowName];
			}
			$i++;
		}
		return $arr;
	}


	function get_table_names()
	{
		$sql = "SELECT table_name from information_schema.tables where TABLE_SCHEMA='" . $this->db_name . "' ";
		$table_names = array();

		$result = $this->query($sql);
		while ($row = $result->fetch_assoc()) {
			$table_names[] = $row['table_name'];
		}
		return $table_names;
	}

	public function get_column_names($table_name)
	{
		$sql = "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = N'" . $table_name . "'";
		if ($stmt = self::prepare($sql)) {
			$stmt->execute();
			$result = $stmt->get_result();
		}
		$column_names = array();
		while ($column = $result->fetch_assoc()) {
			$column_name = $column['COLUMN_NAME'];
			$column_names[] = $column_name;
		}
		return $column_names;
	}

	public function get_columns($table_name)
	{
		$sql = "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = N'" . $table_name . "' AND TABLE_SCHEMA = '" .  $this->db_name . "' ORDER BY COLUMN_NAME ASC";
		// $sql = "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = N'" . $table_name . "'";

		if ($stmt = self::prepare($sql)) {
			$stmt->execute();
			$result = $stmt->get_result();
		}
		$columns = array();
		while ($column = $result->fetch_assoc()) {
			$column = (object)$column;

			$col_data = (object)[];

			$col_data->COLUMN_NAME = $column->COLUMN_NAME;
			// $col_data->ORDINAL_POSITION = $column->ORDINAL_POSITION;
			$col_data->COLUMN_DEFAULT = $column->COLUMN_DEFAULT;
			$col_data->IS_NULLABLE = $column->IS_NULLABLE;
			$col_data->DATA_TYPE = $column->DATA_TYPE;
			$col_data->NUMERIC_PRECISION = $column->NUMERIC_PRECISION;
			$col_data->COLUMN_TYPE = $column->COLUMN_TYPE;

			$columns[] = $col_data;
			// array_push($columns, $column);
		}
		return array_values($columns);
	}

	function get_all_foreign_key_constraints()
	{

		echo $sql = "SELECT * FROM INFORMATION_SCHEMA.`REFERENTIAL_CONSTRAINTS` WHERE CONSTRAINT_SCHEMA = '" . $this->db_name . "' ORDER BY TABLE_NAME, REFERENCED_TABLE_NAME ASC";

		$columns = array();
		if ($stmt = self::prepare($sql)) {
			$stmt->execute();
			$result = $stmt->get_result();
			while ($column = $result->fetch_assoc()) {
				// echo json_encode((object)$column);
				$columns[] = $column;
			}
		} else
			ErrorHandler::throwException("Invalid sql");


		return $columns;
	}

	function get_foreign_key_constraints($table_name)
	{

		$sql = "SELECT * FROM INFORMATION_SCHEMA.`REFERENTIAL_CONSTRAINTS` WHERE CONSTRAINT_SCHEMA = '" . $this->db_name . "' AND TABLE_NAME = '$table_name' ORDER BY TABLE_NAME, REFERENCED_TABLE_NAME, CONSTRAINT_NAME ASC";

		$columns = array();
		if ($stmt = self::prepare($sql)) {
			$stmt->execute();
			$result = $stmt->get_result();
			while ($column = $result->fetch_assoc()) {
				// echo json_encode((object)$column);
				$columns[] = $column;
			}
		} else
			ErrorHandler::throwException("Invalid sql");


		return $columns;
	}


	function backup()
	{
		$password = Config::DB_PASSWORD;
		$username = Config::DB_USERNAME;
		$db_name = Config::DB_NAME;
		$env = Config::ENV;

		$date = date('Y-m-d-h:i:s', time());
		$command = "mysqldump $db_name >/home/boatpark/www/db_backups/$env/bp_$date.sql -u $username -p'$password' --ignore-table=$db_name.log";
		$output = null;
		exec($command, $output);

		if ($output != []) {
			$log = new Logger();
			$log->emergency("DB backup went wrong..." . json_encode($output));
			throw (new Exception("something went wrong :("));
		}
	}
}

$database = new MySQLDatabase();
$db = &$database; // both variables hold the class