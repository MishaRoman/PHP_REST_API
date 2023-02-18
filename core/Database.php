<?php

namespace App\core;

use PDO;

class Database
{
	private $conn;

	public function connect()
	{
		$dotenv = \Dotenv\Dotenv::createImmutable(dirname(__DIR__));
		$dotenv->load();

		$this->conn = null;
		$host = $_ENV['DB_HOST'];
		$db_name = $_ENV['DB_DATABASE'];
		$username = $_ENV['DB_USER'];
		$password = $_ENV['DB_PASSWORD'];

		try {
			$this->conn = new PDO('mysql:host=' . $host . ';dbname=' . $db_name, $username, $password);
	        $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		} catch (PDOException $e) {
			echo 'Connection error: ' . $e->getMessage();
		}

		return $this->conn;
	}

}