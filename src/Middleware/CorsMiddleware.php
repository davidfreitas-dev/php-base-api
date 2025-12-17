<?php

namespace App\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Factory\ResponseFactory;

class CorsMiddleware
{
  
  public function __invoke(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
  {

    if ($request->getMethod() === 'OPTIONS') {
        
      $response = (new ResponseFactory())->createResponse(204);
      
    } else {
        
      $response = $handler->handle($request);
      
    }

    return $response
      ->withHeader('Access-Control-Allow-Origin', '*')
      ->withHeader('Access-Control-Allow-Headers', 'Origin, Content-Type, Authorization')
      ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS')
      ->withHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
      ->withHeader('Pragma', 'no-cache');
      
  }

}
