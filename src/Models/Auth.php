<?php 

namespace App\Models;

use App\DB\Database;
use App\Mail\Mailer;
use App\Models\User;
use App\Utils\PasswordHelper;
use App\Traits\TokenGenerator;
use App\Enums\HttpStatus as HTTPStatus;
use App\Utils\AESCryptographer;
use App\Utils\ApiResponseFormatter;

class Auth {

  use TokenGenerator;

  public static function signup($data) 
	{

    try {

      $user = new User();

      $user->setAttributes($data);

      $userData = $user->create();

      $jwt = self::generateToken($userData);

      return ApiResponseFormatter::formatResponse(
        HTTPStatus::CREATED, 
        "success", 
        "Usuário cadastrado com sucesso",
        $jwt
      );

    } catch (\Exception $e) {

      return ApiResponseFormatter::formatResponse(
        $e->getCode(), 
        "error", 
        $e->getMessage(),
        NULL
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
        ":deslogin" => $credential,
        ":desemail" => $credential,
        ":nrcpf"    => $credential
      ));

      if (empty($results)) {
      
        throw new \Exception("Usuário inexistente ou senha inválida.", HTTPStatus::NOT_FOUND);

      }

      $userData = $results[0];

      if (!password_verify($password, $userData['despassword'])) {

        throw new \Exception("Usuário inexistente ou senha inválida.", HTTPStatus::NOT_FOUND);

      } 

      $jwt = self::generateToken($userData);
      
      return ApiResponseFormatter::formatResponse(
        HTTPStatus::OK, 
        "success", 
        "Usuário autenticado com sucesso.",
        $jwt
      );

    } catch (\PDOException $e) {
      
      return ApiResponseFormatter::formatResponse(
        HTTPStatus::INTERNAL_SERVER_ERROR, 
        "error", 
        "Erro ao tentar autenticar usuário. Tente novamente mais tarde.",
        NULL
      );

    } catch (\Exception $e) {
      
      return ApiResponseFormatter::formatResponse(
        $e->getCode(), 
        "error", 
        $e->getMessage(),
        NULL
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
        ":email" => $email
      ));

      if (empty($results)) {

        throw new \Exception("O endereço de e-mail informado não consta no banco de dados.", HTTPStatus::NOT_FOUND);        

      } 
      
      $userData = $results[0];

      $results = $db->select(
        "CALL sp_userspasswordsrecoveries_create(:iduser, :desip)", array(
          ":iduser" => $userData['iduser'],
          ":desip"  => $_SERVER['REMOTE_ADDR']
        )
      ); 

      if (empty($results))	{
        
        throw new \Exception("Não foi possível recuperar a senha", HTTPStatus::BAD_REQUEST);

      }

      $recoveryData = $results[0];

      $code = AESCryptographer::encrypt($recoveryData['idrecovery']);

      $link = $_ENV['BASE_URL'] . "/reset?code=$code";

      $mailer = new Mailer(
        $userData['desemail'], 
        $userData['desperson'], 
        "Redefinição de senha", 
        array(
          "name" => $userData['desperson'],
          "link" => $link
        )
      );				

      $mailer->send();

      return ApiResponseFormatter::formatResponse(
        HTTPStatus::OK, 
        "success", 
        "Link de redefinição de senha enviado para o endereço de e-mail informado",
        NULL
      );

    } catch (\PDOException $e) {
      
      return ApiResponseFormatter::formatResponse(
        HTTPStatus::INTERNAL_SERVER_ERROR, 
        "error", 
        "Erro ao gerar link para redefinição de senha. Tente novamente mais tarde.",
        NULL
      );

    } catch (\Exception $e) {
      
      return ApiResponseFormatter::formatResponse(
        $e->getCode(),
        "error", 
        $e->getMessage(),
        NULL
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
        ":idrecovery" => $idrecovery
      ));

      if (empty($results)) {

        throw new \Exception("O link de redefinição utilizado expirou", HTTPStatus::UNAUTHORIZED);
        
      } 
      
      return ApiResponseFormatter::formatResponse(
        HTTPStatus::OK, 
        "success", 
        "Token validado com sucesso",
        $results[0]
      );

    } catch (\Exception $e) {
      
      return ApiResponseFormatter::formatResponse(
        HTTPStatus::INTERNAL_SERVER_ERROR, 
        "error", 
        "Falha ao validar token: " . $e->getMessage(),
        NULL
      );

    }

  }

  public static function setNewPassword($password, $data)
  {

    $sql = "UPDATE tb_users 
            SET despassword = :despassword 
            WHERE iduser = :iduser";

    try {

      $db = new Database();

      $db->query($sql, array(
        ":despassword" => PasswordHelper::hashPassword($password),
        ":iduser"      => $data['iduser']
      ));

      self::setForgotUsed($data['idrecovery']);

      return ApiResponseFormatter::formatResponse(
        HTTPStatus::OK, 
        "success", 
        "Senha alterada com sucesso",
        NULL
      );

    } catch (\PDOException $e) {

      return ApiResponseFormatter::formatResponse(
        HTTPStatus::INTERNAL_SERVER_ERROR, 
        "error", 
        "Falha ao gravar nova senha: " . $e->getMessage(),
        NULL
      );

    }

  }

  private static function setForgotUsed($idrecovery)
  {

    $sql = "UPDATE tb_userspasswordsrecoveries 
            SET dtrecovery = NOW() 
            WHERE idrecovery = :idrecovery";

    try {

      $db = new Database();

      $db->query($sql, array(
        ":idrecovery" => $idrecovery
      ));

    } catch (\PDOException $e) {

      return ApiResponseFormatter::formatResponse(
        HTTPStatus::INTERNAL_SERVER_ERROR, 
        "error", 
        "Falha ao definir senha antiga como usada: " . $e->getMessage(),
        NULL
      );

    }

  }

}