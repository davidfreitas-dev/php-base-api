<?php

namespace App\Utils;

use Slim\Psr7\Response;
use Psr\Http\Message\ResponseInterface;

class Responder
{
    
  public static function success(
    string $message = 'OK',
    mixed $data = NULL,
    int $status = 200
  ): ResponseInterface
  {
    
    $payload = [
      'success' => true,
      'message' => $message,
      'code'    => $status
    ];

    if (!is_null($data)) {
        
      $payload['data'] = $data;
      
    }

    return self::json($payload, $status);

  }

  public static function error(
    string $message,
    int $status
  ): ResponseInterface
  {
    
    $payload = [
      'success' => false,
      'message' => $message,
      'code'    => $status
    ];

    return self::json($payload, $status);

  }

  private static function json(array $payload, int $status): ResponseInterface
  {
    
    $response = new Response($status);

    $response->getBody()->write(
      json_encode($payload, JSON_UNESCAPED_UNICODE)
    );

    return $response->withHeader('Content-Type', 'application/json; charset=utf-8');

  }

}
