<?php

namespace App\Clients;

class HttpClient {
  
  private string $baseUrl;

  public function __construct(
    string $baseUrl,
    private array $defaultHeaders = [],
    private ?array $auth = null
  ) {
    
    $this->baseUrl = rtrim($baseUrl, '/');
    
  }

  public function request(string $method, string $path, array $headers = [], array $body = []): object
  {    
    
    $url = $this->baseUrl . $path;

    $curl = curl_init();

    $allHeaders = array_merge($this->defaultHeaders, $headers);

    $options = [
      CURLOPT_URL            => $url,
      CURLOPT_CUSTOMREQUEST  => strtoupper($method),
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_HTTPHEADER     => $this->formatHeaders($allHeaders),
    ];

    if (!empty($body) && in_array(strtoupper($method), ['POST','PUT','PATCH'])) {
      
      $options[CURLOPT_POSTFIELDS] = json_encode($body);
      
    }

    if ($this->auth) {
      
      $options[CURLOPT_HTTPAUTH] = CURLAUTH_BASIC;      
      $options[CURLOPT_USERPWD]  = $this->auth['username'] . ':' . $this->auth['password'];
    
    }

    curl_setopt_array($curl, $options);

    $raw = curl_exec($curl);
    
    $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

    curl_close($curl);

    $response = json_decode($raw);

    if ($status >= 400 || $response === NULL) {
      
      throw new \Exception("Erro HTTP {$status}: " . ($response->error ?? 'Resposta inválida'));
      
    }

    return $response;
  }

  private function formatHeaders(array $headers): array
  {
    
    $formatted = [];

    foreach ($headers as $key => $value) {
      
      $formatted[] = "$key: $value";
      
    }
    
    return $formatted;

  }

}
