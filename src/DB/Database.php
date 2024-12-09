<?php

namespace App\DB;

class Database {

	private $conn;

	public function __construct()
	{

		$this->conn = new \PDO(
			"mysql:dbname=".$_ENV['DB_NAME'].";host=".$_ENV['DB_HOST'], 
			$_ENV['DB_USER'],
			$_ENV['DB_PASS'], 
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

  public function insert($rawQuery, $params = array()):int
  {
    try {
      
      $this->conn->beginTransaction();

      $stmt = $this->conn->prepare($rawQuery);

      $this->setParams($stmt, $params);

      $stmt->execute();

      $lastInsertId = $this->conn->lastInsertId();

      $this->conn->commit();

      return $lastInsertId;

    } catch (\PDOException $e) {
        
      $this->conn->rollBack();
      
      throw $e;
    }
  }

	public function select($rawQuery, $params = array()):array
	{

		try {

      $this->conn->beginTransaction();
      
      $stmt = $this->conn->prepare($rawQuery);

      $this->setParams($stmt, $params);

      $stmt->execute();

      $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

      $stmt->closeCursor();

      $this->conn->commit();

      return $results;

    } catch (\PDOException $e) {
      
      $this->conn->rollBack();
      
      throw $e;

    }

	}

	public function query($rawQuery, $params = array()):int
	{

		try {
      
      $this->conn->beginTransaction();

      $stmt = $this->conn->prepare($rawQuery);

      $this->setParams($stmt, $params);

      $stmt->execute();

      $this->conn->commit();

      return $stmt->rowCount();

    } catch (\PDOException $e) {
      
      $this->conn->rollBack();
      
      throw $e;

    }

	}
    
}