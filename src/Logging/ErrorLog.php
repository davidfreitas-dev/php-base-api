<?php

namespace App\Logging;

use App\DB\Database;
use Throwable;

class ErrorLog
{
  
  /**
   * Captura e salva o erro no banco
   */
  public static function log(Throwable $exception, array $context = []): void
  {
    
    $db = new Database();

    $db->insert(
      "INSERT INTO error_logs (level, message, trace, context) 
        VALUES (:level, :message, :trace, :context)",
      [
        ":level"   => "ERROR",
        ":message" => $exception->getMessage(),
        ":trace"   => $exception->getTraceAsString(),
        ":context" => json_encode($context)
      ]
    );

  }

}