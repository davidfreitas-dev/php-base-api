<?php 

namespace App\Models;

class Model {

	private $values = [];

	public function __call($name, $args)
	{

		$method = substr($name, 0, 3);
		$fieldName = substr($name, 3, strlen($name));

		switch ($method)
		{

			case "get":
        $fieldName = $this->convertToCamelCase($fieldName);
				return (isset($this->values[$fieldName])) ? $this->values[$fieldName] : NULL;
			break;

			case "set":
        $fieldName = $this->convertToCamelCase($fieldName);
				$this->values[$fieldName] = $args[0];
			break;

		}

	}

	public function setAttributes($data = array())
	{

		foreach ($data as $key => $value) {
			
			$this->{"set".$this->convertToPascalCase($key)}($value);

		}

	}

	public function getAttributes()
	{

		return $this->values;

	}

  private function convertToCamelCase($string)
  {
      
    return lcfirst($this->convertToPascalCase($string));

  }

  private function convertToPascalCase($string)
  {
      
    return str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $string)));

  }

}

 ?>