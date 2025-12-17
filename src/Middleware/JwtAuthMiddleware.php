<?php

declare(strict_types=1);

namespace App\Middleware;

use JimTools\JwtAuth\Middleware\JwtAuthentication;
use JimTools\JwtAuth\Decoder\FirebaseDecoder;
use JimTools\JwtAuth\Rules\RequestPathRule;
use JimTools\JwtAuth\Options;
use JimTools\JwtAuth\Secret;

class JwtAuthMiddleware
{
  
  public function __invoke(): JwtAuthentication
  {
    
    $isProduction = $_ENV['APP_ENV'] === 'production';
    
    $jwtSecret = $_ENV['JWT_SECRET_KEY'];

    $options = new Options(
      isSecure: $isProduction
    );

    $decoder = new FirebaseDecoder(new Secret($jwtSecret, 'HS256'));

    $rules = [
      new RequestPathRule(
        paths: ['/'],
        ignore: [
          "/images",
          "/auth/signin",
          "/auth/signup",
          "/auth/forgot",
          "/auth/verify",
          "/auth/reset",
          "/auth/token"
        ]
      )
    ];

    return new JwtAuthentication($options, $decoder, $rules);
    
  }

}