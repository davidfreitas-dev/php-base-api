<?php

namespace App\Traits;

use App\Utils\ApiResponseFormatter;
use App\Enums\HttpStatus as HTTPStatus;

trait TokenGenerator {

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

    return $token;

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