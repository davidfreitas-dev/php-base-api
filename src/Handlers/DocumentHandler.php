<?php

namespace App\Handlers;

class DocumentHandler
{
  private const CPF_LENGTH = 11;
  private const CNPJ_LENGTH = 14;

  public static function formatDocument($documento) 
  {
    $documento = preg_replace('/\D/', '', $documento);

    if (strlen($documento) === self::CPF_LENGTH) {
      return preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $documento);
    }
    
    if (strlen($documento) === self::CNPJ_LENGTH) {
      return preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $documento);
    }

    return $documento;
  }

  public static function validateDocument($document)
  {
    $document = preg_replace('/[^0-9]/is', '', $document);

    if (strlen($document) === self::CPF_LENGTH) {
      return self::validateCPF($document);
    }

    if (strlen($document) === self::CNPJ_LENGTH) {
      return self::validateCNPJ($document);
    }

    return false;
  }

  private static function validateCPF($cpf) 
  {   
    // Verifica se foi informado todos os digitos corretamente
    if (strlen($cpf) != self::CPF_LENGTH) {
      return false;
    }

    // Verifica se foi informada uma sequência de digitos repetidos. Ex: 111.111.111-11
    if (preg_match('/(\d)\1{10}/', $cpf)) {
      return false;
    }

    // Faz o calculo para validar o CPF
    for ($t = 9; $t < 11; $t++) {
      for ($d = 0, $c = 0; $c < $t; $c++) {
        $d += $cpf[$c] * (($t + 1) - $c);
        }

      $d = ((10 * $d) % 11) % 10;

      if ($cpf[$c] != $d) {
        return false;
      }
    }

    return true;
  }

  private static function validateCNPJ($cnpj) 
  {
    // Verifica se foi informado todos os digitos corretamente
    if (strlen($cnpj) != self::CNPJ_LENGTH) {
      return false;
    }      

    // Verifica se todos os digitos são iguais
    if (preg_match('/(\d)\1{13}/', $cnpj)) {
      return false;	
    }      

    // Valida primeiro dígito verificador
    for ($i = 0, $j = 5, $soma = 0; $i < 12; $i++) {
      $soma += $cnpj[$i] * $j;
      $j = ($j == 2) ? 9 : $j - 1;
    }

    $resto = $soma % 11;

    if ($cnpj[12] != ($resto < 2 ? 0 : 11 - $resto)) {
      return false;
    }

    // Valida segundo dígito verificador
    for ($i = 0, $j = 6, $soma = 0; $i < 13; $i++) {
      $soma += $cnpj[$i] * $j;
      $j = ($j == 2) ? 9 : $j - 1;
    }

    $resto = $soma % 11;

    return $cnpj[13] == ($resto < 2 ? 0 : 11 - $resto);
  }
}
