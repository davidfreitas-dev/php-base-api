<?php 

namespace App\Models;

use App\DB\Database;
use App\Mail\Mailer;
use App\Models\User;
use App\Enums\HttpStatus as HTTPStatus;
use App\Helpers\AESCryptographer;
use App\Helpers\ApiResponseFormatter;

class Auth {

  public static function signup($data) 
	{

    try {

      if (self::checkUserExists($data)) {
        
        return ApiResponseFormatter::formatResponse(
          HTTPStatus::CONFLICT,
          "error", 
          "Usuário já cadastrado no banco de dados",
          []
        );

      }
    
      $response = User::create($data);

      if ($response['status'] == 'error') {
        
        return $response;

      }

      return self::generateToken($response['data']);

    } catch (\PDOException $e) {

      return ApiResponseFormatter::formatResponse(
        HTTPStatus::INTERNAL_SERVER_ERROR, 
        "error", 
        "Falha ao cadastrar usuário: " . $e->getMessage(),
        []
      );

    }

	}
        
  public static function signin($credential, $password)
  {

    $sql = "SELECT * FROM tb_users a 
            INNER JOIN tb_persons b 
            ON a.idperson = b.idperson 
            WHERE a.deslogin = :deslogin 
            OR b.desemail = :desemail	
            OR b.nrcpf = :nrcpf";

    try {
      
      $db = new Database();

      $results = $db->select($sql, array(
        ":deslogin"=>$credential,
        ":desemail"=>$credential,
        ":nrcpf"=>$credential
      ));

      if (empty($results)) {

        return ApiResponseFormatter::formatResponse(
          HTTPStatus::NOT_FOUND, 
          "error", 
          "Usuário inexistente ou senha inválida",
          []
        );
  
      }

      $data = $results[0];

      if (password_verify($password, $data['despassword'])) {

        return self::generateToken($data);

      } 
      
      return ApiResponseFormatter::formatResponse(
        HTTPStatus::NOT_FOUND, 
        "error", 
        "Usuário inexistente ou senha inválida",
        []
      );

    } catch (\PDOException $e) {
      
      return ApiResponseFormatter::formatResponse(
        HTTPStatus::INTERNAL_SERVER_ERROR, 
        "error", 
        "Falha na autenticação do usuário: " . $e->getMessage(),
        []
      );

    }
    
  }

  public static function getForgotLink($email)
  {

    $sql = "SELECT * FROM tb_persons a 
            INNER JOIN tb_users b 
            USING(idperson) 
            WHERE a.desemail = :email";

    try {
      
      $db = new Database();

      $results = $db->select($sql, array(
        ":email"=>$email
      ));

      if (empty($results)) {

        return ApiResponseFormatter::formatResponse(
          HTTPStatus::NOT_FOUND, 
          "error", 
          "O e-mail informado não consta no banco de dados",
          []
        );

      } 
      
      $data = $results[0];

      $results = $db->select(
        "CALL sp_userspasswordsrecoveries_create(:iduser, :desip)", array(
          ":iduser"=>$data['iduser'],
          ":desip"=>$_SERVER['REMOTE_ADDR']
        )
      ); 

      if (empty($results))	{

        return ApiResponseFormatter::formatResponse(
          HTTPStatus::BAD_REQUEST,  
          "error", 
          "Não foi possível recuperar a senha",
          []
        );

      }

      $recoveryData = $results[0];

      $code = AESCryptographer::encrypt($recoveryData['idrecovery']);

      $link = $_ENV['BASE_URL'] . "/reset?code=$code";

      $mailer = new Mailer(
        $data['desemail'], 
        $data['desperson'], 
        "Redefinição de senha", 
        array(
          "name"=>$data['desperson'],
          "link"=>$link
        )
      );				

      $mailer->send();

      return ApiResponseFormatter::formatResponse(
        HTTPStatus::OK, 
        "success", 
        "Link de redefinição de senha enviado para o e-mail informado",
        []
      );

    } catch (\PDOException $e) {
      
      return ApiResponseFormatter::formatResponse(
        HTTPStatus::INTERNAL_SERVER_ERROR, 
        "error", 
        "Falha ao recuperar senha: " . $e->getMessage(),
        []
      );

    }		

  }

  public static function validateForgotLink($code)
  {

    $idrecovery = AESCryptographer::decrypt($code);

    $sql = "SELECT * FROM tb_userspasswordsrecoveries a
            INNER JOIN tb_users b USING(iduser)
            INNER JOIN tb_persons c USING(idperson)
            WHERE a.idrecovery = :idrecovery
            AND a.dtrecovery IS NULL
            AND DATE_ADD(a.dtregister, INTERVAL 1 HOUR) >= NOW()";
    
    try {
      
      $db = new Database();

      $results = $db->select($sql, array(
        ":idrecovery"=>$idrecovery
      ));

      if (empty($results)) {

        return ApiResponseFormatter::formatResponse(
          HTTPStatus::UNAUTHORIZED,  
          "error", 
          "O link de redefinição utilizado expirou",
          []
        );

      } 
      
      return ApiResponseFormatter::formatResponse(
        HTTPStatus::OK, 
        "success", 
        "Token validado com sucesso",
        $results[0]
      );

    } catch (\PDOException $e) {
      
      return ApiResponseFormatter::formatResponse(
        HTTPStatus::INTERNAL_SERVER_ERROR, 
        "error", 
        "Falha ao validar token: " . $e->getMessage(),
        []
      );

    }

  }

  public static function setForgotUsed($idrecovery)
  {

    $sql = "UPDATE tb_userspasswordsrecoveries 
            SET dtrecovery = NOW() 
            WHERE idrecovery = :idrecovery";

    try {

      $db = new Database();

      $db->query($sql, array(
        ":idrecovery"=>$idrecovery
      ));

    } catch (\PDOException $e) {

      return ApiResponseFormatter::formatResponse(
        HTTPStatus::INTERNAL_SERVER_ERROR, 
        "error", 
        "Falha ao definir senha antiga como usada: " . $e->getMessage(),
        []
      );

    }

  }

  public static function setNewPassword($password, $iduser)
  {

    $sql = "UPDATE tb_users 
            SET despassword = :despassword 
            WHERE iduser = :iduser";

    try {

      $db = new Database();

      $db->query($sql, array(
        ":despassword"=>self::getPasswordHash($password),
        ":iduser"=>$iduser
      ));

      return ApiResponseFormatter::formatResponse(
        HTTPStatus::OK, 
        "success", 
        "Senha alterada com sucesso",
        []
      );

    } catch (\PDOException $e) {

      return ApiResponseFormatter::formatResponse(
        HTTPStatus::INTERNAL_SERVER_ERROR, 
        "error", 
        "Falha ao gravar nova senha: " . $e->getMessage(),
        []
      );

    }

  }

  private static function checkUserExists($data) 
  {

    $sql = "SELECT * FROM tb_users a 
            INNER JOIN tb_persons b 
            ON a.idperson = b.idperson 
            WHERE a.deslogin = :deslogin 
            OR b.desemail = :desemail	
            OR b.nrcpf = :nrcpf";

    try {

      $db = new Database();
        
      $results = $db->select($sql, array(
        ":deslogin" => $data['deslogin'],
        ":desemail" => $data['desemail'],
        ":nrcpf" => $data['nrcpf']
      ));

      return !empty($results);

    } catch (\PDOException $e) {

      return false;
      
    }

  }

  private static function getPasswordHash($password)
	{

		return password_hash($password, PASSWORD_BCRYPT, [
			'cost' => 12
		]);

	}

  private static function generateToken($payload)
  {

      $header = [
          'typ' => 'JWT',
          'alg' => 'HS256'
      ];

      $header = json_encode($header);
      $payload = json_encode($payload);

      $header = self::base64UrlEncode($header);
      $payload = self::base64UrlEncode($payload);

      $sign = hash_hmac('sha256', $header . "." . $payload, $_ENV['JWT_SECRET_KEY'], true);
      $sign = self::base64UrlEncode($sign);

      $token = $header . '.' . $payload . '.' . $sign;

      return ApiResponseFormatter::formatResponse(
        HTTPStatus::OK, 
        "success", 
        "Autenticação efetuada com sucesso",
        $token
      );

  }
  
  private static function base64UrlEncode($data)
  {

    $b64 = base64_encode($data);

    if ($b64 === false) {
        return false;
    }

    $url = strtr($b64, '+/', '-_');

    return rtrim($url, '=');
      
  }

}