<?php 

namespace App;

class Response {

	public static function handleResponse($code, $status, $response)
	{

		return array(
      "code" => $code,
      "status" => $status,
      "data" => $response
    );

	}
    
}
