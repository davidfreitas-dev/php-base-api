<?php

declare(strict_types=1);

namespace App\Services;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class TokenService
{

  private string $jwtSecretKey;
  private string $apiUrl;
  private string $appUrl;
  private int $accessTokenExp;
  private int $refreshTokenExp;

  /**
   * TokenService constructor.
   * Reads JWT configuration from environment variables.
   */
  public function __construct()
  {
    
    $this->jwtSecretKey    = $_ENV['JWT_SECRET_KEY'];
    $this->apiUrl          = $_ENV['API_URL'];
    $this->appUrl          = $_ENV['APP_URL'];
    $this->accessTokenExp  = (int)$_ENV['JWT_ACCESS_TOKEN_EXP_SECONDS'];
    $this->refreshTokenExp = (int)$_ENV['JWT_REFRESH_TOKEN_EXP_SECONDS'];
    
  }

  public function generateTokenPair(array $user): array
  {
    
    $accessToken = $this->generateAccessToken($user);
    
    $refreshToken = $this->generateRefreshToken($user);

    return [
      'access_token'  => $accessToken,
      'refresh_token' => $refreshToken,
    ];

  }

  public function generateAccessToken(array $user): string
  {
      
    $payload = [
      'iss'  => $this->apiUrl,
      'aud'  => $this->appUrl,
      'iat'  => time(),
      'nbf'  => time(),
      'exp'  => time() + $this->accessTokenExp,
      'sub'  => (string) $user['id'],
      'type' => 'access',
      'jti'  => bin2hex(random_bytes(16)),
    ];

    return JWT::encode($payload, $this->jwtSecretKey, 'HS256');

  }

  private function generateRefreshToken(array $user): string
  {

    $payload = [
      'iss'  => $this->appUrl,
      'aud'  => $this->appUrl,
      'iat'  => time(),
      'nbf'  => time(),
      'exp'  => time() + $this->refreshTokenExp,
      'sub'  => (string) $user['id'],
      'type' => 'refresh',
      'jti'  => bin2hex(random_bytes(16)),
    ];

    return JWT::encode($payload, $this->jwtSecretKey, 'HS256');

  }

  public function decodeToken(string $token): object
  {
      
    return JWT::decode($token, new Key($this->jwtSecretKey, 'HS256'));
    
  }

}