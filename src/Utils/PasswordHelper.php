<?php

namespace App\Utils;

use App\Enums\HttpStatus as HTTPStatus;

class PasswordHelper
{

  public static function checkPasswordStrength($password) 
  {

    if (strlen($password) < 8) {

      throw new \Exception("A senha deve conter pelo menos 8 caracteres.", HTTPStatus::BAD_REQUEST);

    }

    if (!preg_match('/[a-z]/', $password) || !preg_match('/[A-Z]/', $password)) {

      throw new \Exception("A senha deve conter letras maiúsculas e minúsculas.", HTTPStatus::BAD_REQUEST);

    }

    if (!preg_match('/[0-9]/', $password)) {

      throw new \Exception("A senha deve conter pelo menos um número.", HTTPStatus::BAD_REQUEST);

    }

    if (!preg_match('/[\W_]/', $password)) {

      throw new \Exception("A senha deve conter pelo menos um caractere especial.", HTTPStatus::BAD_REQUEST);

    }

  }

  public static function hashPassword($password)
	{

		return password_hash($password, PASSWORD_BCRYPT, [
			'cost' => 12
		]);

	}

}