<?php 

namespace App\Model;

use App\DB\Database;
use App\Mail\Mailer;
use App\Model\User;
use App\Utils\AESCryptographer;
use App\Utils\ApiResponseFormatter;

class Auth extends User {

  public static function signup($user) 
	{

		$userExists = User::getByCredentials($user);
			
		if ($userExists) {

			return ApiResponseFormatter::formatResponse(
        400, 
        "error", 
        "Usuário já cadastrado no banco de dados"
      );

		}
    
    return User::create($user);

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
          404, 
          "error", 
          "Usuário inexistente ou senha inválida."
        );
  
      }

      $data = $results[0];

      if (password_verify($password, $data['despassword'])) {

        return Auth::generateToken($data);

      } 
      
      return ApiResponseFormatter::formatResponse(
        404, 
        "error", 
        "Usuário inexistente ou senha inválida."
      );

    } catch (\PDOException $e) {
      
      return ApiResponseFormatter::formatResponse(
        500, 
        "error", 
        "Falha na autenticação do usuário: " . $e->getMessage()
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
          404, 
          "error", 
          "O e-mail informado não consta no banco de dados"
        );

      } 
      
      $data = $results[0];

      $query = $db->select(
        "CALL sp_userspasswordsrecoveries_create(:iduser, :desip)", array(
          ":iduser"=>$data['iduser'],
          ":desip"=>$_SERVER['REMOTE_ADDR']
        )
      ); 

      if (empty($query))	{

        return ApiResponseFormatter::formatResponse(
          400, 
          "error", 
          "Não foi possível recuperar a senha"
        );

      }

      $recoveryData = $query[0];

      $code = AESCryptographer::encrypt($recoveryData);

      $link = "http://application/forgot/reset?code=$code";

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
        200, 
        "success", 
        "Link de redefinição de senha enviado para o e-mail informado"
      );

    } catch (\PDOException $e) {
      
      return ApiResponseFormatter::formatResponse(
        500, 
        "error", 
        "Falha ao recuperar senha: " . $e->getMessage()
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
          401, 
          "error", 
          "O link de redefinição utilizado expirou"
        );

      } 
      
      return $results[0];

    } catch (\PDOException $e) {
      
      return ApiResponseFormatter::formatResponse(
        500, 
        "error", 
        "Falha ao validar token: " . $e->getMessage()
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
        500, 
        "error", 
        "Falha ao definir senha antiga como usada: " . $e->getMessage()
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
        ":despassword"=>Auth::getPasswordHash($password),
        ":iduser"=>$iduser
      ));

      return ApiResponseFormatter::formatResponse(
        200, 
        "success", 
        "Senha alterada com sucesso"
      );

    } catch (\PDOException $e) {

      return ApiResponseFormatter::formatResponse(
        500, 
        "error", 
        "Falha ao gravar nova senha: " . $e->getMessage()
      );

    }

  }

  private static function getPasswordHash($password)
	{

		return password_hash($password, PASSWORD_BCRYPT, [
			'cost' => 12
		]);

	}

  private static function generateToken($data)
  {

      $header = [
          'typ' => 'JWT',
          'alg' => 'HS256'
      ];

      $payload = [
          'name' => $data['desperson'],
          'email' => $data['desemail'],
      ];

      $header = json_encode($header);
      $payload = json_encode($payload);

      $header = self::base64UrlEncode($header);
      $payload = self::base64UrlEncode($payload);

      $sign = hash_hmac('sha256', $header . "." . $payload, $_ENV['JWT_SECRET_KEY'], true);
      $sign = self::base64UrlEncode($sign);

      $token = $header . '.' . $payload . '.' . $sign;

      $data['token'] = $token;

      return ApiResponseFormatter::formatResponse(200, "success", $data);

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