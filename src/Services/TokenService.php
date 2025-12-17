<?php

namespace App\Services;

class TokenService
{
    
  private static function base64UrlEncode(string $data): string
  {
      
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    
  }
  
  private static function generateToken(array $payload): string
  {

    $header = self::base64UrlEncode(json_encode([
      'typ' => 'JWT',
      'alg' => 'HS256'
    ]));

    $payload = self::base64UrlEncode(json_encode($payload));

    $signature = hash_hmac(
      'sha256',
      "$header.$payload",
      $_ENV['JWT_SECRET_KEY'],
      true
    );

    $signature = self::base64UrlEncode($signature);

    return "$header.$payload.$signature";

  }
  
  public static function generatePrivateToken(array $user): string
  {

    $payload = [
      "sub"   => $user["id"],
      "iat"   => time(),
      "exp"   => time() + 604800
    ];

    return self::generateToken($payload);

  }

}
