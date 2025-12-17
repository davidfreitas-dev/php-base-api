<?php

namespace App\Middleware;

use Throwable;
use App\Utils\Responder;
use App\Services\ErrorLogService;
use Slim\Interfaces\ErrorHandlerInterface;
use Psr\Http\Message\ResponseInterface as Response;

class GlobalErrorMiddleware implements ErrorHandlerInterface
{

  public function __construct(
    private ErrorLogService $logger
  ) {}

  public function __invoke(
    $request,
    Throwable $exception,
    bool $displayErrorDetails,
    bool $logErrors,
    bool $logErrorDetails
  ): Response {

    $status = $this->resolveStatus($exception->getCode());

    $message = $this->resolveMessage($exception, $status, $displayErrorDetails);

    if ($this->shouldLog($exception, $status)) {
      
      $this->logger->log($exception, [
        "method" => $request->getMethod(),
        "url"    => (string)$request->getUri()
      ]);
    
    }

    return Responder::error($message, $status);

  }

  private function resolveStatus(int|string|null $code): int
  {

    return (is_int($code) && $code >= 100 && $code < 600)
      ? $code
      : 500;
  
  }

  private function resolveMessage(Throwable $exception, int $status, bool $displayErrorDetails): string
  {

    if ($status >= 500 && !$displayErrorDetails) {
      
      return "Erro interno no servidor.";
      
    }

    return $exception->getMessage();

  }

  private function shouldLog(Throwable $e, int $status): bool
  {
    
    return $status >= 500
      || $e instanceof \PDOException
      || $e->getCode() === 0;

    return false;
  
  }

}
