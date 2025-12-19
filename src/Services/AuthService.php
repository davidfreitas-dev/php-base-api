<?php

namespace App\Services;

use App\DB\Database;
use App\Models\User;
use App\Utils\PasswordHelper;
use App\Services\TokenService;
use App\Utils\AESCryptographer;
use App\Handlers\DocumentHandler;
use App\Enums\HttpStatus as HTTPStatus;
use Psr\Http\Message\ServerRequestInterface;

class AuthService
{
  public function __construct(
    private Database $db,
    private User $user,
    private TokenService $tokenService,
    private TokenBlocklistService $blocklistService
  ) { }

  public function signup(array $data): array
	{
		
		$this->validateRequiredFields($data);

    $data = $this->normalizeInput($data);

    $this->validateInput($data);

    $this->user->setAttributes($data);

    $user = $this->user->create();

    unset($user['password']);

    $tokens = $this->tokenService->generateTokenPair($user);

    return [
      'access_token'  => $tokens['access_token'],
      'expires_in'    => $_ENV['JWT_ACCESS_TOKEN_EXP_SECONDS'],
      'token_type'    => 'Bearer',
      'refresh_token' => $tokens['refresh_token'],
    ];

	}
        
  public function signin(string $login, string $password): array
  {

    if (trim($login) === "" || trim($password) === "") {
        
      throw new \Exception("Login e senha são obrigatórios.", HTTPStatus::BAD_REQUEST);
      
    }

    $login = trim($login);
      
    if (filter_var($login, FILTER_VALIDATE_EMAIL)) {
      
      $login = strtolower($login);
    
    }

    $results = $this->db->select(
      "SELECT u.id, p.name, u.password
       FROM users u
       INNER JOIN persons p ON u.id = p.id
       WHERE (p.email = :login OR p.cpfcnpj = :login)", 
      array(
        ":login" => $login
      )
    );

    if (empty($results)) {
      
      throw new \Exception("Usuário inexistente ou password inválida.", HTTPStatus::UNAUTHORIZED);
    
    }

    $user = $results[0];

    if (!password_verify($password, $user["password"])) {
      
      throw new \Exception("Usuário inexistente ou password inválida.", HTTPStatus::UNAUTHORIZED);
    
    }

    unset($user["password"]);

    $tokens = $this->tokenService->generateTokenPair($user);

    return [
      'access_token'  => $tokens['access_token'],
      'expires_in'    => $_ENV['JWT_ACCESS_TOKEN_EXP_SECONDS'],
      'token_type'    => 'Bearer',
      'refresh_token' => $tokens['refresh_token'],
    ];
  
  }

  public function logout(ServerRequestInterface $request): array
  {
    
    $token = $request->getAttribute('token');

    if (empty($token)) {
      
      throw new \Exception("Token não encontrado na requisição.", HTTPStatus::UNAUTHORIZED);
      
    }

    $this->blocklistService->block($token['jti'], $token['exp']);

    return ['message' => 'Logout realizado com sucesso.'];

  }

  public function requestPasswordReset(string $email): array
  {
    
    $sql = "SELECT u.id, p.name, p.email
            FROM users u
            INNER JOIN persons p ON u.id = p.id
            WHERE p.email = :email
            LIMIT 1";
    
    $results = $this->db->select($sql, array(
      ":email" => strtolower(trim($email))
    ));

    if (empty($results)) {
        
      throw new \Exception("Não foi possível encontrar um usuário com esse e-mail.", 404);
    
    }

    $user = $results[0] ?? NULL;

    $recoveryId = $this->db->insert(
      "INSERT INTO password_resets (user_id, ip_address, created_at, updated_at) 
      VALUES (:user_id, :ip_address, NOW(), NOW())",
      [
        ":user_id"    => $user["id"],
        ":ip_address" => $_SERVER["REMOTE_ADDR"]
      ]
    );

    $code = AESCryptographer::encrypt([
      "recovery_id" => $recoveryId,
      "user_id"     => $user["id"]
    ]);

    return [
      "link"        => $_ENV["APP_URL"] . "/reset?code=$code",
      "user"        => $user,
      "recovery_id" => $recoveryId
    ];

  }

  public function verifyResetToken(string $code)
  {

    if (empty($code)) {
        
      throw new \Exception("Falha ao validar token: token inexistente.", HTTPStatus::UNAUTHORIZED);
      
    }
    
    $decryptedData = AESCryptographer::decrypt($code);

    if (!is_array($decryptedData) || !isset($decryptedData["recovery_id"], $decryptedData["user_id"])) {
      
      throw new \Exception("Token inválido ou corrompido.", HTTPStatus::UNAUTHORIZED);
    
    }
    
    $recoveryId = $decryptedData["recovery_id"];

    $sql = "SELECT pr.*, u.id AS user_id, p.name, p.email
            FROM password_resets pr
            INNER JOIN users u ON u.id = pr.user_id
            INNER JOIN persons p ON p.id = u.id
            WHERE pr.id = :id
              AND pr.used_at IS NULL
              AND DATE_ADD(pr.created_at, INTERVAL 1 HOUR) >= NOW()";

    $results = $this->db->select($sql, array(
      ":id" => $recoveryId
    ));

    if (empty($results)) {
      
      throw new \Exception("O link de redefinição utilizado expirou.", 401);
    
    }

    return [
      "user_id"     => $results[0]["user_id"],
      "recovery_id" => $results[0]["id"]
    ];
  
  }

  public function resetPassword(string $token, string $password)
  {

    $data = $this->verifyResetToken($token);
    
    PasswordHelper::checkPasswordStrength($password);

    $sql = "UPDATE users SET password = :password WHERE id = :user_id";
    
    $affectedRows = $this->db->query($sql, [
      ":password" => PasswordHelper::hashPassword($password),
      ":user_id"  => $data["user_id"]
    ]);

    if ($affectedRows === 0) {
        
      throw new \Exception("Erro ao redefinir senha.", HTTPStatus::NOT_FOUND);
      
    }

    self::setForgotUsed($data["recovery_id"]);

    return true;
  
  }

  private function setForgotUsed(int $recoveryId)
  {
    
    $sql = "UPDATE password_resets 
            SET used_at = NOW() 
            WHERE id = :id";
    
    $this->db->query($sql, array(
      ":id" => $recoveryId
    ));
  
  }

  private function validateRequiredFields(array $data): void
  {
    
    $required = ["name", "email", "cpfcnpj", "password"];

    foreach ($required as $field) {
      
      if (empty($data[$field]) || trim($data[$field]) === "") {
          
        throw new \Exception("O campo '$field' é obrigatório.", HTTPStatus::BAD_REQUEST);
        
      }

    }

  }

  private function normalizeInput(array $data): array
  {
    
    $data['name'] = mb_convert_case(
      trim(preg_replace('/\s+/', ' ', $data['name'])),
      MB_CASE_TITLE,
      "UTF-8"
    );

    $data['email'] = strtolower(trim($data['email']));
    
    $data['cpfcnpj'] = preg_replace('/\D/', '', $data['cpfcnpj']);

    if (!empty($data['phone'])) {
        
      $data['phone'] = preg_replace('/\D/', '', $data['phone']);
      
    } else {
        
      $data['phone'] = NULL;
      
    }

    return $data;

  }

  private function validateInput(array $data): void
  {
    
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        
      throw new \Exception("Email inválido.", HTTPStatus::BAD_REQUEST);
      
    }

    if (!DocumentHandler::validateDocument($data['cpfcnpj'])) {
        
      throw new \Exception("CPF/CNPJ inválido.", HTTPStatus::BAD_REQUEST);
      
    }

    if ($data['phone'] !== NULL && (strlen($data['phone']) < 10 || strlen($data['phone']) > 11)) {
        
      throw new \Exception("phone inválido.", HTTPStatus::BAD_REQUEST);
      
    }

    PasswordHelper::checkPasswordStrength($data['password']);
    
  }

  public function refreshToken(string $refreshToken): array
  {
    
    if (empty($refreshToken)) {
        
      throw new \Exception("Refresh token não fornecido.", HTTPStatus::BAD_REQUEST);
      
    }

    try {
        
      $decoded = $this->tokenService->decodeToken($refreshToken);
      
    } catch (\Exception $e) {
        
      throw new \Exception("Refresh token inválido ou expirado.", HTTPStatus::UNAUTHORIZED);
      
    }

    if ($this->blocklistService->isBlocked($decoded->jti)) {
      
      throw new \Exception("Refresh token inválido ou expirado.", HTTPStatus::UNAUTHORIZED);
      
    }

    if ($decoded->type !== "refresh") {
        
      throw new \Exception("Token inválido para esta operação.", HTTPStatus::UNAUTHORIZED);
      
    }

    $this->blocklistService->block($decoded->jti, $decoded->exp);

    $userId = $decoded->sub;
    
    $user = $this->user->get((int)$userId);

    if (!$user) {
        
      throw new \Exception("Usuário não encontrado.", HTTPStatus::NOT_FOUND);
      
    }

    $tokens = $this->tokenService->generateTokenPair($user);

    return [
      'access_token'  => $tokens['access_token'],
      'expires_in'    => $_ENV['JWT_ACCESS_TOKEN_EXP_SECONDS'],
      'token_type'    => 'Bearer',
      'refresh_token' => $tokens['refresh_token'],
    ];

  }

}
