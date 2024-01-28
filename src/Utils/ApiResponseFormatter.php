<?php 

namespace App\Utils;

class ApiResponseFormatter {

	public static function formatResponse($code, $status, $response)
	{

		return array(
      "code" => $code,
      "status" => $status,
      "data" => $response
    );

	}
    
}
