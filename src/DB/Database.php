<?php

declare(strict_types=1);

namespace App\DB;

use PDO;
use PDOException;

class Database {

  private string $host;
  private string $dbname;
  private string $user;
  private string $pass;
  private PDO $pdo;

  public function __construct()
  {
    
    $this->host   = $_ENV['DB_HOST'];
    $this->dbname = $_ENV['DB_NAME'];
    $this->user   = $_ENV['DB_USER'];
    $this->pass   = $_ENV['DB_PASS'];

    $this->connect();

  }

  private function connect()
  {
    
    try {
      
      $this->pdo = new PDO(
        "mysql:host={$this->host};dbname={$this->dbname};charset=utf8",
        $this->user,
        $this->pass,
        [
          PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
          PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
          PDO::ATTR_EMULATE_PREPARES   => true,
        ]
      );

      $this->pdo->exec("SET time_zone = '+00:00'");

    } catch (PDOException $e) {
      
      error_log("Erro na conexão com o banco de dados: " . $e->getMessage());
      
      throw new PDOException("Database connection failed.");
      
    }
    
  }

  public function getConnection()
  {
      
    return $this->pdo;
    
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
      
      $this->pdo->beginTransaction();

      $stmt = $this->pdo->prepare($rawQuery);

      $this->setParams($stmt, $params);

      $stmt->execute();

      $lastInsertId = (int) $this->pdo->lastInsertId();

      $this->pdo->commit();

      return $lastInsertId;

    } catch (PDOException $e) {
        
      $this->pdo->rollBack();
      
      throw $e;
    }

  }

	public function select($rawQuery, $params = array()):array
	{

		try {
      
      $stmt = $this->pdo->prepare($rawQuery);

      $this->setParams($stmt, $params);

      $stmt->execute();

      $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

      $stmt->closeCursor();

      return $results;

    } catch (PDOException $e) {
      
      throw $e;

    }

	}

	public function query($rawQuery, $params = array()):int
	{

		try {
      
      $this->pdo->beginTransaction();

      $stmt = $this->pdo->prepare($rawQuery);

      $this->setParams($stmt, $params);

      $stmt->execute();

      $this->pdo->commit();

      return $stmt->rowCount();

    } catch (PDOException $e) {
      
      $this->pdo->rollBack();
      
      throw $e;

    }

	}
    
}