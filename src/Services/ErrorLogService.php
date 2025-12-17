<?php

namespace App\Services;

use Throwable;
use App\DB\Database;

class ErrorLogService
{
  
  private string $logPath;
  
  public function __construct(private Database $db) 
  {

    $this->logPath = __DIR__ . '/../../storage/logs/error.log';

  }

  public function log(Throwable $e, array $context = []): void
  {

    $payload = [
      "level"   => "ERROR",
      "message" => $e->getMessage(),
      "trace"   => $e->getTraceAsString(),
      "context" => json_encode(array_merge([
        "file" => $e->getFile(),
        "line" => $e->getLine(),
        "code" => $e->getCode()
      ], $context))
    ];
    
    try {
      
      $this->db->insert(
        "INSERT INTO error_logs (level, message, trace, context)
         VALUES (:level, :message, :trace, :context)",
        [
          ":level"   => $payload["level"],
          ":message" => $payload["message"],
          ":trace"   => $payload["trace"],
          ":context" => $payload["context"]
        ]
      );
    
    } catch (Throwable $dbError) {
      
      $this->writeToFile($payload, $dbError);
      
    }
  
  }

  private function writeToFile(array $payload, Throwable $dbError): void
  {
    try {
      
      $dir = dirname($this->logPath);

      if (!is_dir($dir)) {
          
        mkdir($dir, 0775, true);
        
      }

      $log = sprintf(
        "[%s] DB_LOG_FAIL: %s\nOriginal Error: %s\nTrace: %s\nContext: %s\n\n",
        date("Y-m-d H:i:s"),
        $dbError->getMessage(),
        $payload["message"],
        $payload["trace"],
        $payload["context"]
      );

      file_put_contents($this->logPath, $log, FILE_APPEND);

    } catch (Throwable $fileError) {

      error_log("FALHA AO GRAVAR LOG EM file: " . $fileError->getMessage());

    }
  
  }

}
