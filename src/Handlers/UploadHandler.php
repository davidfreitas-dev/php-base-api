<?php

namespace App\Handlers;

use App\Enums\HttpStatus as HTTPStatus;

class UploadHandler
{

  public static function uploadPhoto($id, $file, $directory)
	{

		if (!isset($file['name'])) {

      return false;
      
    }

    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    self::validateExtension($extension);
    
    self::validateSize($file['size']);
    
    self::validateResolution($file['tmp_name']);
    
    $image = self::createImage($file["tmp_name"], $extension);

    if (!$image) {

      throw new \Exception("Não foi possível criar a imagem.", HTTPStatus::INTERNAL_SERVER_ERROR);
    
    }

    $dist = $_ENV['STORAGE_PATH'] . DIRECTORY_SEPARATOR . $directory;

    if (!is_dir($dist) && !mkdir($dist, 0777, true)) {
      
      throw new \Exception("Falha ao criar o diretório: $dist", HTTPStatus::INTERNAL_SERVER_ERROR);
  
    }

    self::deletePhoto($id, $directory);

    $timestamp = date('YmdHis');
    
    $imageName = $directory . "_" . $timestamp . $id;
    
    $fullPath = $dist . DIRECTORY_SEPARATOR . $imageName . ".jpg";

    imagejpeg($image, $fullPath, 85);

    imagedestroy($image);

    return $imageName;

	}

  public static function deletePhoto($id, $directory)
  {

    $dist = $_ENV['STORAGE_PATH'] . DIRECTORY_SEPARATOR . $directory;

    if (!is_dir($dist)) {
      
      exit;

    }
    
    $existingFileName = self::checkExistingPhoto($id, $dist);
    
    if ($existingFileName) {
      
      $imagePath = $dist . DIRECTORY_SEPARATOR . $existingFileName;
    
      if (file_exists($imagePath)) {

        unlink($imagePath);

      }

    }
    
  }

  private static function checkExistingPhoto($id, $dist)
  {

    $files = scandir($dist);

    $dirName = basename($dist);
    
    foreach ($files as $file) {
        
      if (preg_match('/^' . preg_quote($dirName . '_') . '\d{14}' . preg_quote($id) . '\.jpg$/', $file)) {
          
        return $file; 

      }
        
    }    

    return null; 

  }

  private static function validateExtension($extension)
  {
    
    $allowed = ['jpg', 'jpeg', 'png'];
    
    if (!in_array($extension, $allowed)) {
      
      throw new \Exception("Extensão inválida: $extension. Permitido apenas jpg, jpeg e png", HTTPStatus::BAD_REQUEST);
      
    }

  }

  private static function validateSize($size)
  {
    
    $maxSizeMB = 5;
    
    if ($size > $maxSizeMB * 1024 * 1024) {
        
      throw new \Exception("Arquivo muito grande. Máximo permitido: {$maxSizeMB}MB", HTTPStatus::BAD_REQUEST);
      
    }

  }

  private static function validateResolution($tmpFile)
  {
    
    list($width, $height) = getimagesize($tmpFile);
    
    $max = 800;
    $min = 200;

    if ($width > $max || $height > $max) {
        
      throw new \Exception("Resolução muito grande. Máximo permitido: {$max}x{$max}px", HTTPStatus::BAD_REQUEST);
      
    }

    if ($width < $min || $height < $min) {
        
      throw new \Exception("Resolução muito pequena. Mínimo permitido: {$min}x{$min}px", HTTPStatus::BAD_REQUEST);
      
    }

  }

  private static function createImage($tmpFile, $extension)
  {
    
    switch ($extension) {
      case 'jpg':
      case 'jpeg':
        return imagecreatefromjpeg($tmpFile);
      case 'png':
        return imagecreatefrompng($tmpFile);
      default:
        throw new \Exception("Erro ao criar a imagem.", HTTPStatus::BAD_REQUEST);
    }
    
  }

}
