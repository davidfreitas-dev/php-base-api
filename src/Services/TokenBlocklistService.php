<?php

declare(strict_types=1);

namespace App\Services;

use App\DB\Database;

class TokenBlocklistService
{
  
  public function __construct(private Database $db)
  {
  }

  /**
   * Adds a token's JTI to the blocklist.
   *
   * @param string $jti The JWT ID.
   * @param int $expiresAt The Unix timestamp when the token expires.
   * @return void
   */
  public function block(string $jti, int $expiresAt): void
  {
    
    if ($this->isBlocked($jti)) {
      return;
    }
    
    $this->db->insert(
      "INSERT INTO jwt_blocklist (jti, expires_at) VALUES (:jti, FROM_UNIXTIME(:expires_at))",
      [
        ':jti'        => $jti,
        ':expires_at' => $expiresAt,
      ]
    );

  }

  /**
   * Checks if a token's JTI is in the blocklist.
   *
   * @param string $jti The JWT ID.
   * @return bool True if the token is blocklisted, false otherwise.
   */
  public function isBlocked(string $jti): bool
  {
    
    $results = $this->db->select(
      "SELECT 1 FROM jwt_blocklist WHERE jti = :jti",
      [':jti' => $jti]
    );

    return count($results) > 0;

  }

  /**
   * Deletes all expired tokens from the blocklist.
   * This is intended to be run periodically by a cron job.
   *
   * @return int The number of rows deleted.
   */
  public function purgeExpiredTokens(): int
  {
    
    return $this->db->query("DELETE FROM jwt_blocklist WHERE expires_at < NOW()");
    
  }

}
