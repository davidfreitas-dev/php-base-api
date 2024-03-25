<?php 

namespace App\Model;

use App\DB\Database;
use App\Utils\ApiResponseFormatter;

class User {

  public static function create($data)
  {

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
				":desperson"=>$data['desperson'],
				":deslogin"=>$data['deslogin'],
				":despassword"=>User::getPasswordHash($data['despassword']),
				":desemail"=>$data['desemail'],
				":nrphone"=>$data['nrphone'],
				":nrcpf"=>$data['nrcpf'],
				":inadmin"=>$data['inadmin']
			));

      return ApiResponseFormatter::formatResponse(
        201, 
        "success", 
        "Usuário cadastrado com sucesso",
        $results[0]
      );

    } catch (\PDOException $e) {
			
			return ApiResponseFormatter::formatResponse(
        500, 
        "error", 
        "Falha ao inserir usuário: " . $e->getMessage(),
        []
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
			
			if (empty($results)) {

				return ApiResponseFormatter::formatResponse(
          204, 
          "success", 
          "Nenhum usuário encontrado",
          $results
        );

			}

      return ApiResponseFormatter::formatResponse(
        200, 
        "success", 
        "Lista de usuários",
        $results
      );

		} catch (\PDOException $e) {

			return ApiResponseFormatter::formatResponse(
        500, 
        "error", 
        "Falha ao obter usuários: " . $e->getMessage(),
        []
      );
			
		}

	}

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

      if (empty($results)) {

        return ApiResponseFormatter::formatResponse(
          404, 
          "error", 
          "Usuário não encontrado",
          $results
        );
        
      }
			
      return ApiResponseFormatter::formatResponse(
        200, 
        "success", 
        "Dados do usuário",
        $results
      );

		} catch (\PDOException $e) {
			
			return ApiResponseFormatter::formatResponse(
        500, 
        "error", 
        "Falha ao obter usuário: " . $e->getMessage(),
        []
      );
			
		}

	}

	public static function update($iduser, $data) 
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
			
			$results = $db->select($sql, array(
				":iduser"=>$iduser,
				":desperson"=>$data['desperson'],
				":deslogin"=>$data['deslogin'],
				":despassword"=>User::getPasswordHash($data['despassword']),
				":desemail"=>$data['desemail'],
				":nrphone"=>$data['nrphone'],
				":nrcpf"=>$data['nrcpf'],
				":inadmin"=>$data['inadmin']
			));

			return ApiResponseFormatter::formatResponse(
        200, 
        "success", 
        "Usuário atualizado com sucesso",
        $results
      );

		} catch (\PDOException $e) {

			return ApiResponseFormatter::formatResponse(
        500, 
        "error", 
        "Falha ao atualizar dados do usuário: " . $e->getMessage(),
        []
      );
			
		}		

	}

	public static function delete($iduser) 
	{
		
		$sql = "CALL sp_users_delete(:iduser)";		
		
		try {

			$db = new Database();
			
			$db->query($sql, array(
				":iduser"=>$iduser
			));
			
			return ApiResponseFormatter::formatResponse(
        200, 
        "success", 
        "Usuário excluido com sucesso",
        []
      );

		} catch (\PDOException $e) {

			return ApiResponseFormatter::formatResponse(
        500, 
        "error", 
        "Falha ao excluir usuário: " . $e->getMessage(),
        []
      );
			
		}		

	}

  private static function getPasswordHash($password)
	{

		return password_hash($password, PASSWORD_BCRYPT, [
			'cost' => 12
		]);

	}

}

 ?>