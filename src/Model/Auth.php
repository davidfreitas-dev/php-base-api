<?php 

namespace App\Model;

use App\DB\Database;
use App\Mail\Mailer;
use App\Response;

class Auth {

  public static function signup($user) 
	{

		$userExists = Auth::checkUserExistence($user);
			
		if ($userExists) {

			return Response::handleResponse(
        400, 
        "error", 
        "Usuário já cadastrado no banco de dados"
      );

		}		

		$sql = "CALL sp_users_create(
              :desperson, 
              :deslogin, 
              :despassword, 
              :desemail, 
              :nrphone, 
              :nrcpf, 
              :inadmin
            )";
		
		try {

			$db = new Database();

			$results = $db->select($sql, array(
				":deslogin"=>$user['deslogin'],
				":desperson"=>$user['desperson'],
				":despassword"=>Auth::getPasswordHash($user['despassword']),
				":desemail"=>$user['desemail'],
				":nrphone"=>$user['nrphone'],
				":nrcpf"=>$user['nrcpf'],
				":inadmin"=>$user['inadmin']
			));

			if (count($results)) {

				return Response::handleResponse(
          201, 
          "success", 
          "Cadastro efetuado com sucesso"
        );

			}

		} catch (\PDOException $e) {
			
			return Response::handleResponse(
        500, 
        "error", 
        "Falha ao cadastrar usuário: " . $e->getMessage()
      );
			
		}		

	}
        
  public static function signin($login, $password)
  {

    $sql = "SELECT * FROM tb_users a 
            INNER JOIN tb_persons b 
            ON a.idperson = b.idperson 
            WHERE a.deslogin = :deslogin 
            OR b.desemail = :deslogin";

    $db = new Database();

    $results = $db->select($sql, array(
      ":deslogin"=>$login
    )); 

    if (empty($results)) {

      return Response::handleResponse(
        404, 
        "error", 
        "Usuário inexistente ou senha inválida."
      );

    }

    $data = $results[0];

    if (password_verify($password, $data['despassword'])) {

      return Auth::generateToken($data);

    } else {

      return Response::handleResponse(
        404, 
        "error", 
        "Usuário inexistente ou senha inválida."
      );

    }

  }

  private static function checkUserExistence($user) 
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
				":deslogin"=>$user['deslogin'],
				":desemail"=>$user['desemail'],
				":nrcpf"=>$user['nrcpf']
			));

			return count($results);

		} catch (\PDOException $e) {

			return Response::handleResponse(
        500, 
        "error", 
        "Falha ao obter usuário: " . $e->getMessage()
      );

		}		

	}

  public static function getPasswordHash($password)
	{

		return password_hash($password, PASSWORD_BCRYPT, [
			'cost' => 12
		]);

	}

  public static function getForgot($email)
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

        return Response::handleResponse(
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

        return Response::handleResponse(
          400, 
          "error", 
          "Não foi possível recuperar a senha"
        );

      } 
      
      $dataRecovery = $query[0];

      $code = openssl_encrypt(
        $dataRecovery['idrecovery'], 
        'AES-128-CBC', 
        pack("a16", $_ENV['SECRET']), 
        0, 
        pack("a16", $_ENV['SECRET_IV'])
      );

      $code = base64_encode($code);

      $link = "http://127.0.0.1:3000/forgot/reset?code=$code";

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

      return Response::handleResponse(
        200, 
        "success", 
        "Link de redefinição de senha enviado para o e-mail informado"
      );

    } catch (\PDOException $e) {
      
      return Response::handleResponse(
        500, 
        "error", 
        "Falha ao recuperar senha: " . $e->getMessage()
      );

    }		

  }

  public static function validateForgotDecrypt($code)
  {

    $code = base64_decode($code);

    $idrecovery = openssl_decrypt(
      $code, 
      'AES-128-CBC', 
      pack("a16", $_ENV['SECRET']), 
      0, 
      pack("a16", $_ENV['SECRET_IV'])
    );

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

        return Response::handleResponse(
          204, 
          "error", 
          "Não foi possível recuperar a senha"
        );

      } 
      
      return $results[0];

    } catch (\PDOException $e) {
      
      return Response::handleResponse(
        401, 
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

      return Response::handleResponse(
        500, 
        "error", 
        "Falha ao gravar senha antiga: " . $e->getMessage()
      );

    }

  }

  public static function setNewPassword($password, $iduser)
  {

    $sql = "UPDATE tb_users 
            SET despassword = :password 
            WHERE iduser = :iduser";

    try {

      $db = new Database();

      $db->query($sql, array(
        ":password"=>$password,
        ":iduser"=>$iduser
      ));

      return Response::handleResponse(
        200, 
        "success", 
        "Senha alterada com sucesso"
      );

    } catch (\PDOException $e) {

      return Response::handleResponse(
        500, 
        "error", 
        "Falha ao gravar nova senha: " . $e->getMessage()
      );

    }

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

      return Response::handleResponse(200, "success", $data);

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