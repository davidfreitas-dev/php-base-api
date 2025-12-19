<?php

declare(strict_types=1);

namespace App\Middleware;

use Throwable;
use App\Enums\HttpStatus;
use App\Utils\Responder;
use App\Services\TokenBlocklistService;
use JimTools\JwtAuth\Secret;
use JimTools\JwtAuth\Options;
use JimTools\JwtAuth\Rules\RequestPathRule;
use JimTools\JwtAuth\Decoder\FirebaseDecoder;
use JimTools\JwtAuth\Handlers\AfterHandlerInterface;
use JimTools\JwtAuth\Middleware\JwtAuthentication;
use JimTools\JwtAuth\Exceptions\ExpiredException;
use JimTools\JwtAuth\Exceptions\AuthorizationException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class JwtAuthMiddleware
{
    
  private const PUBLIC_PATHS = [
    '/images',
    '/auth/signin',
    '/auth/signup',
    '/auth/forgot',
    '/auth/verify',
    '/auth/reset',
    '/auth/token',
  ];

  public function __construct(
    private readonly TokenBlocklistService $blocklist
  ) {}

  public function __invoke(): callable
  {
    
    $authMiddleware = $this->createJwtAuthentication();

    return function (Request $request, RequestHandler $handler) use ($authMiddleware): Response {
      
      try {
        
        return $authMiddleware->process($request, $handler);
        
      } catch (ExpiredException $e) {
        
        return Responder::error('Sua sessão expirou. Por favor, faça login novamente.', HttpStatus::UNAUTHORIZED);
        
      } catch (AuthorizationException $e) {
        
        return Responder::error('Não foi possível autenticar sua requisição. Verifique suas credenciais.', HttpStatus::UNAUTHORIZED);
        
      } catch (Throwable $e) {            
        
        return Responder::error('Erro ao processar autenticação. Tente novamente.', HttpStatus::INTERNAL_SERVER_ERROR);
        
      }

    };

  }

  private function createJwtAuthentication(): JwtAuthentication
  {
    $options = $this->createOptions();
    $decoder = $this->createDecoder();
    $rules   = $this->createRules();

    return new JwtAuthentication($options, $decoder, $rules);
  }

  private function createOptions(): Options
  {
    
    $isProduction = $_ENV['APP_ENV'] === 'production';
    
    return new Options(
      isSecure: $isProduction,
      after: $this->createAfterHandler()
    );

  }

  private function createDecoder(): FirebaseDecoder
  {
      
    $secret = new Secret($_ENV['JWT_SECRET_KEY'], 'HS256');
      
    return new FirebaseDecoder($secret);

  }

  private function createRules(): array
  {
      
    return [
      new RequestPathRule(
        paths: ['/'],
        ignore: self::PUBLIC_PATHS
      ),
    ];
    
  }

  private function createAfterHandler(): AfterHandlerInterface
  {

    return new class($this->blocklist) implements AfterHandlerInterface {
        
      public function __construct(private readonly TokenBlocklistService $blocklist) {}

      public function __invoke(\Psr\Http\Message\ResponseInterface $response,array $arguments): \Psr\Http\Message\ResponseInterface {
        
        $token = $arguments['decoded'];

        if ($this->isTokenBlocked($token)) {

          return $this->handleBlockedToken($response);

        }

        if ($this->isInvalidTokenType($token)) {

          return Responder::error('Tipo de token inválido para autenticação.', HttpStatus::UNAUTHORIZED);

        }

        return $response;

      }

      private function isTokenBlocked(array $token): bool
      {
          
        return $this->blocklist->isBlocked($token['jti']);
        
      }

      private function isLogoutRequest(): bool
      {
          
        $requestUri = $_SERVER['REQUEST_URI'] ?? '';
          
        return str_contains($requestUri, '/auth/logout');
    
      }

      private function handleBlockedToken(\Psr\Http\Message\ResponseInterface $response): \Psr\Http\Message\ResponseInterface
      {

        if ($this->isLogoutRequest()) {
            
          return $response;
          
        }

        return Responder::error('Este token foi revogado.', HttpStatus::UNAUTHORIZED);

      }

      private function isInvalidTokenType(array $token): bool
      {
          
        return !isset($token['type']) || $token['type'] !== 'access';
        
      }

    };

  }

}
