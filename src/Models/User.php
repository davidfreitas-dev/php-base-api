<?php 

namespace App\Models;

use App\DB\Database;
use App\Models\Model;
use App\Utils\PasswordHelper;
use App\Handlers\DocumentHandler;
use App\Enums\HttpStatus as HTTPStatus;

class User extends Model 
{

  public function __construct(Database $db)
  {
    
    parent::__construct($db);
    
  }

  public function get($userId)
	{

		$sql = "SELECT u.id, p.name, p.email, p.phone, p.cpfcnpj, u.is_active, u.created_at, u.updated_at
            FROM users u
            INNER JOIN persons p ON u.id = p.id
            WHERE u.id = :userId
            LIMIT 1";

    $results = $this->db->select($sql, array(
      ":userId" => $userId
    ));

    if (empty($results)) {

      throw new \Exception("Usuário não encontrado", HTTPStatus::NOT_FOUND);   
      
    }

    $user = $results[0];
    
    return $user;

	}

  public function create()
  {
    
    $conn = $this->db->getConnection();
    
    try {

      $conn->beginTransaction();
        
      $this->checkUserExists($this->getCpfcnpj(), $this->getEmail());

      $data = [
        'name'     => $this->getName(),
        'email'    => $this->getEmail(),
        'phone'    => $this->getPhone(),
        'cpfcnpj'  => $this->getCpfcnpj(),
        'password' => $this->getPassword()
      ];

      $data['person_id'] = $this->insertPerson($conn, $data);

      $userId = $this->insertUser($conn, $data);

      $conn->commit();

      return self::get($userId);

    } catch (\Throwable $e) {

      $conn->rollBack();
      
      throw $e;

    }
  
  }

	public function update() 
	{
		
		$name = mb_convert_case(
      trim(preg_replace('/\s+/', ' ', $this->getName())),
      MB_CASE_TITLE,
      "UTF-8"
    );

    $email = strtolower(trim($this->getEmail()));

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

      throw new \Exception("O formato do e-mail informado não é válido.", HTTPStatus::BAD_REQUEST);
      
    }

    $cpfcnpj = preg_replace('/[^0-9]/is', '', $this->getCpfcnpj());
    
    if (!DocumentHandler::validateDocument($cpfcnpj)) {
        
      throw new \Exception("CPF/CNPJ inválido.", HTTPStatus::BAD_REQUEST);
    
    }

    $phone = !empty($this->getPhone()) ? preg_replace('/\D/', '', $this->getPhone()) : NULL;

    $this->checkUserExists($cpfcnpj, $email, $this->getIdUsuario());
			
    $affectedRows = $this->db->query(
      "UPDATE persons 
       SET name = :name, email = :email, cpfcnpj = :cpfcnpj, phone = :phone
       WHERE id = :id",
      [
        ":name"    => $name,
        ":email"   => $email,
        ":phone"   => $phone,
        ":cpfcnpj" => $cpfcnpj,
        ":id"      => $this->getUserId()
      ]
    );

    $user = $this->get($this->getUserId());

    unset($user['password']);

    return $user;				

	}

	public function delete($userId) 
	{
		
		$sql = "DELETE FROM users WHERE id = :id";
		
		$affectedRows = $this->db->query($sql, array(
      ":id" => $userId
    ));

    if ($affectedRows === 0) {
        
      throw new \Exception("Usuário não encontrado.", HTTPStatus::NOT_FOUND);
      
    }

	}

  private function checkUserExists($cpfcnpj, $email, $userId = NULL) 
  {
    
    $sql = "SELECT u.id, p.email, p.cpfcnpj
            FROM users u
            INNER JOIN persons p ON u.id = p.id
            WHERE (p.email = :email OR p.cpfcnpj = :cpfcnpj)";

    if ($userId) {
        
      $sql .= " AND u.id != :userId";
    
    }

    $params = [
      ":email"   => $email,
      ":cpfcnpj" => $cpfcnpj
    ];

    if ($userId) {
        
      $params[":userId"] = $userId;
    
    }

    $results = $this->db->select($sql, $params);

    if (count($results) > 0) {
        
      $existing = $results[0];

      if (strtolower($existing["email"]) === strtolower($email)) {
          
        throw new \Exception("O e-mail informado já está sendo utilizado por outro usuário", HTTPStatus::CONFLICT);
      
      }

      if ($existing["cpfcnpj"] === $cpfcnpj) {
          
        throw new \Exception("O CPF/CNPJ informado já está sendo utilizado por outro usuário", HTTPStatus::CONFLICT);
      
      }

    }
  
  }

  private function insertPerson($conn, $data)
  {

    $sql = "INSERT INTO persons (name, email, phone, cpfcnpj) 
            VALUES (:name, :email, :phone, :cpfcnpj)";

    $stmt = $conn->prepare($sql);

    $stmt->execute([
      ":name"    => $data['name'],
      ":email"   => $data['email'],
      ":phone"   => $data['phone'],
      ":cpfcnpj" => $data['cpfcnpj']
    ]);

    return $conn->lastInsertId();
    
  }

  private function insertUser($conn, $data)
  {
    
    $sql = "INSERT INTO users (id, password, is_active, created_at, updated_at) 
            VALUES (:id, :password, 1, NOW(), NOW())";

    $stmt = $conn->prepare($sql);

    $stmt->execute([
      ":id"       => $data['person_id'],
      ":password" => PasswordHelper::hashPassword($data['password'])
    ]);

    return $data['person_id'];

  }

}

 ?>