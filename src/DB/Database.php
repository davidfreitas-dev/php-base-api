<?php

namespace App\DB;

use \PDO;

class Database {

	private $conn;

	public function __construct()
	{

		$this->conn = new \PDO(
			"mysql:dbname=".$_ENV['DB_NAME'].";host=".$_ENV['DB_HOST'], 
			$_ENV['DB_USER'],
			$_ENV['DB_PASSWORD'], 
			array(\PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8;SET time_zone='America/Sao_Paulo'")
		);

	}

  private function setParams($statement, $parameters = array())
	{

		foreach ($parameters as $key => $value) {
			
			$this->bindParam($statement, $key, $value);

		}

	}

	private function bindParam($statement, $key, $value)
	{

		$statement->bindParam($key, $value);

	}

	public function query($rawQuery, $params = array())
	{

		$stmt = $this->conn->prepare($rawQuery);

		$this->setParams($stmt, $params);

		$stmt->execute();

	}

	public function select($rawQuery, $params = array()):array
	{

		$stmt = $this->conn->prepare($rawQuery);

		$this->setParams($stmt, $params);

		$stmt->execute();

		return $stmt->fetchAll(\PDO::FETCH_ASSOC);

	}
    
}
