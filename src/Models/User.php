<?php 

namespace App\Models;

use App\DB\Database;
use App\Models\Model;
use App\Utils\PasswordHelper;
use App\Traits\TokenGenerator;
use App\Utils\ApiResponseFormatter;
use App\Enums\HttpStatus as HTTPStatus;

class User extends Model {

  use TokenGenerator;

  public function create()
  {

    $sql = "CALL sp_users_create(
      :desperson, 
      :deslogin, 
      :despassword, 
      :desemail, 
      :nrphone, 
      :nrcpf
    )";

    try {

      if (!filter_var(strtolower($this->getdesemail()), FILTER_VALIDATE_EMAIL)) {

        throw new \Exception("O formato do endereço de e-mail informado não é válido.", HTTPStatus::BAD_REQUEST);
        
      }

      PasswordHelper::checkPasswordStrength($this->getdespassword());

      $this->checkUserExists($this->getdeslogin(), $this->getnrcpf(), strtolower($this->getdesemail()));
      
      $db = new Database();

			$results = $db->select($sql, array(
				":desperson"   => $this->getdesperson(),
				":deslogin"    => $this->getdeslogin(),
				":despassword" => PasswordHelper::hashPassword($this->getdespassword()),
				":desemail"    => strtolower($this->getdesemail()),
				":nrphone"     => preg_replace('/[^0-9]/is', '', $this->getnrphone()),
				":nrcpf"       => preg_replace('/[^0-9]/is', '', $this->getnrcpf())
			));

      return ApiResponseFormatter::formatResponse(
        HTTPStatus::CREATED, 
        "success", 
        "Usuário cadastrado com sucesso.",
        $results[0]
      );

    } catch (\PDOException $e) {
			
			return ApiResponseFormatter::formatResponse(
        HTTPStatus::INTERNAL_SERVER_ERROR, 
        "error", 
        "Erro ao cadastrar usuário. Tente novamente mais tarde.",
        NULL
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

				throw new \Exception("Nenhum usuário encontrado", HTTPStatus::NO_CONTENT);        

			}

      return ApiResponseFormatter::formatResponse(
        HTTPStatus::OK, 
        "success", 
        "Lista de usuários",
        $results
      );

		} catch (\Exception $e) {

			return ApiResponseFormatter::formatResponse(
        HTTPStatus::INTERNAL_SERVER_ERROR, 
        "error", 
        "Falha ao obter usuários: " . $e->getMessage(),
        NULL
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
				":iduser" => $iduser
			));

      if (empty($results)) {

        throw new \Exception("Usuário não encontrado", HTTPStatus::NOT_FOUND);   
        
      }
			
      return ApiResponseFormatter::formatResponse(
        HTTPStatus::OK, 
        "success", 
        "Dados do usuário",
        $results
      );

		} catch (\Exception $e) {
			
			return ApiResponseFormatter::formatResponse(
        HTTPStatus::INTERNAL_SERVER_ERROR, 
        "error", 
        "Falha ao obter usuário: " . $e->getMessage(),
        NULL
      );
			
		}

	}

	public function update() 
	{
		
		$sql = "CALL sp_users_update(
              :iduser, 
              :desperson, 
              :deslogin, 
              :desemail, 
              :nrphone, 
              :nrcpf
            )";
		
		try {

      if (!filter_var(strtolower($this->getdesemail()), FILTER_VALIDATE_EMAIL)) {

        throw new \Exception("O formato do e-mail informado não é válido.", HTTPStatus::BAD_REQUEST);
        
      }

      $this->checkUserExists($this->getdeslogin(), $this->getnrcpf(), strtolower($this->getdesemail()), $this->getiduser());

			$db = new Database();
			
			$results = $db->select($sql, array(
				":iduser"      => $this->getiduser(),
				":desperson"   => $this->getdesperson(),
				":deslogin"    => $this->getdeslogin(),
				":desemail"    => strtolower($this->getdesemail()),
				":nrphone"     => preg_replace('/[^0-9]/is', '', $this->getnrphone()),
				":nrcpf"       => preg_replace('/[^0-9]/is', '', $this->getnrcpf())
			));

      $jwt = self::generateToken($results[0]);

			return ApiResponseFormatter::formatResponse(
        HTTPStatus::OK, 
        "success", 
        "Usuário atualizado com sucesso.",
        $jwt
      );

		} catch (\PDOException $e) {

			return ApiResponseFormatter::formatResponse(
        HTTPStatus::INTERNAL_SERVER_ERROR, 
        "error", 
        "Erro ao atualizar dados do usuário. Tente novamente mais tarde.",
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

	public static function delete($iduser) 
	{
		
		$sql = "CALL sp_users_delete(:iduser)";		
		
		try {

			$db = new Database();
			
			$db->query($sql, array(
				":iduser"=>$iduser
			));
			
			return ApiResponseFormatter::formatResponse(
        HTTPStatus::OK, 
        "success", 
        "Usuário excluido com sucesso.",
        NULL
      );

		} catch (\PDOException $e) {

			return ApiResponseFormatter::formatResponse(
        HTTPStatus::INTERNAL_SERVER_ERROR, 
        "error", 
        "Erro ao excluir usuário. Tente novamente mais tarde.",
        NULL
      );
			
		}		

	}

  private function checkUserExists($deslogin, $nrcpf, $desemail, $iduser = NULL) 
  {

    $nrcpf = $nrcpf !== NULL ? preg_replace('/[^0-9]/is', '', $nrcpf) : '';

    $sql = "SELECT * FROM tb_users u
            INNER JOIN tb_persons p
            USING(idperson) 
            WHERE (u.deslogin = :deslogin 
              OR p.nrcpf = :nrcpf 
              OR p.desemail = :desemail)";

    if ($iduser) {

      $sql .= " AND u.iduser != :iduser";

    }

    try {

      $db = new Database();

      $params = [
        ":deslogin" => $deslogin,
        ":nrcpf"    => $nrcpf,
        ":desemail" => $desemail,
      ];

      if ($iduser) {

        $params[":iduser"] = $iduser;
  
      }
        
      $results = $db->select($sql, $params);

      if (count($results) > 0) {
        
        if ($results[0]['deslogin'] === $deslogin) {
            
          throw new \Exception("O nome de usuário informado já está em uso.", HTTPStatus::BAD_REQUEST);
          
        }

        if ($results[0]['nrcpf'] === $nrcpf) {
          
          throw new \Exception("O CPF informado já está cadastrado.", HTTPStatus::BAD_REQUEST);
          
        }

        if ($results[0]['desemail'] === $desemail) {
          
          throw new \Exception("O endereço de e-mail informado já está cadastrado.", HTTPStatus::BAD_REQUEST);
          
        }

      }

    } catch (\PDOException $e) {

      throw new \Exception("Erro ao checar se o usuário já está cadastrado. Tente novamente mais tarde.", HTTPStatus::INTERNAL_SERVER_ERROR);
      
    }

  }

}

 ?>