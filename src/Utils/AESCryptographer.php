<?php 

namespace App\Utils;

class AESCryptographer {

	public static function encrypt($data) 
  {

    if (is_array($data)) {
      
      $data = json_encode($data);

    }

    $code = openssl_encrypt(
      $data, 
      'AES-128-CBC', 
      pack("a16", $_ENV['SECRET']), 
      0, 
      pack("a16", $_ENV['SECRET_IV'])
    );

    return base64_encode($code);

  }

  public static function decrypt($code) 
  {

    $data = base64_decode($code);

    $decrypted = openssl_decrypt(
      $data, 
      'AES-128-CBC', 
      pack("a16", $_ENV['SECRET']), 
      0, 
      pack("a16", $_ENV['SECRET_IV'])
    );

    $decoded = json_decode($decrypted, true);

    return $decoded !== NULL ? $decoded : $decrypted;

  }
    
}
