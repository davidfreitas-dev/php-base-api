<?php 

namespace App\Model;

use App\DB\Database;
use App\Response;

class User {

	public static function get($iduser)
	{

		$sql = "SELECT * FROM tb_users a 
            INNER JOIN tb_persons b 
            USING(idperson) 
            WHERE a.iduser = :iduser";

		try {

			$db = new Database();

			$results = $db->select($sql, array(
				":iduser"=>$iduser
			));

      if (count($results)) {
			
			  return Response::handleResponse(
          200, 
          "success", 
          $results[0]
        );
        
      }

			return Response::handleResponse(
        404, 
        "error", 
        "Usuário não encontrado"
      );

		} catch (\PDOException $e) {
			
			return Response::handleResponse(
        500, 
        "error", 
        "Falha ao obter usuário: " . $e->getMessage()
      );
			
		}

	}

	public static function list() 
  {    
    $sql = "SELECT * FROM tb_users a 
            INNER JOIN tb_persons b 
            ON a.idperson = b.idperson";
		
		try {

			$db = new Database();

			$results = $db->select($sql);
			
			if (count($results)) {

				return Response::handleResponse(
          200, 
          "success", 
          $results
        );

			}
      
      return Response::handleResponse(
        204, 
        "success", 
        "Nenhum usuário encontrado"
      );

		} catch (\PDOException $e) {

			return Response::handleResponse(
        500, 
        "error", 
        "Falha ao obter usuários: " . $e->getMessage()
      );
			
		}

	}

	public static function save($user) 
	{

		$alreadyExists = User::checkExistingUser($user);
			
		if ($alreadyExists) {

			return Response::handleResponse(
        400, 
        "error", 
        "Usuário já cadastrado"
      );

		}		

		$sql = "CALL sp_users_save(
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

			$result = $db->select($sql, array(
				":desperson"=>$user['desperson'],
				":deslogin"=>$user['deslogin'],
				":despassword"=>User::getPasswordHash($user['despassword']),
				":desemail"=>$user['desemail'],
				":nrphone"=>$user['nrphone'],
				":nrcpf"=>$user['nrcpf'],
				":inadmin"=>$user['inadmin']
			));

			if (count($result) == 0) {

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

	public static function update($userId, $user) 
	{
		
		$sql = "CALL sp_usersupdate_save(
              :iduser, 
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
			
			$result = $db->select($sql, array(
				":iduser"=>$userId,
				":desperson"=>$user['desperson'],
				":deslogin"=>$user['deslogin'],
				":despassword"=>User::getPasswordHash($user['despassword']),
				":desemail"=>$user['desemail'],
				":nrphone"=>$user['nrphone'],
				":nrcpf"=>$user['nrcpf'],
				":inadmin"=>$user['inadmin']
			));

			if (count($result) == 0) {

				return Response::handleResponse(
          200, 
          "success", 
          "Usuário atualizado com sucesso"
        );
				
			}

		} catch (\PDOException $e) {

			return Response::handleResponse(
        500, 
        "error", 
        "Falha ao atualizar dados do usuário: " . $e->getMessage()
      );
			
		}		

	}

	public static function delete($userId) 
	{
		
		$sql = "CALL sp_users_delete(:iduser)";		
		
		try {

			$db = new Database();
			
			$db->query($sql, array(
				":iduser"=>$userId
			));
			
			return Response::handleResponse(
        200, 
        "success", 
        "Usuário excluido com sucesso"
      );

		} catch (\PDOException $e) {

			return Response::handleResponse(
        500, 
        "error", 
        "Falha ao excluir usuário: " . $e->getMessage()
      );
			
		}		

	}

	public static function getPasswordHash($password)
	{

		return password_hash($password, PASSWORD_BCRYPT, [
			'cost' => 12
		]);

	}

	private static function checkExistingUser($user) 
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

}

 ?>