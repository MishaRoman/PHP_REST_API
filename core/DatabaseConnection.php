<?php

namespace App\core;

use PDO;

final class DatabaseConnection
{
	private static $instance = null;
	private static $connection;

	private function __construct() {}

	private function __clone() {}
	
	private function __wakeup() {}

	public static function getInstance()
	{
		if (is_null(self::$instance)){
            self::$instance = new DatabaseConnection();
        }
        
        return self::$instance;
	}

	public static function connect(string $host, string $dbName, string $username, string $password)
	{
		try {
			self::$connection = new PDO('mysql:host=' . $host . ';dbname=' . $dbName, $username, $password);
	        self::$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		} catch (PDOException $e) {
			echo 'Connection error: ' . $e->getMessage();
		}
	}

	public static function getConnection()
	{
		return self::$connection;
	}

}