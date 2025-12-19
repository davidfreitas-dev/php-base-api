<?php

namespace App\Services;

use App\Interfaces\MailerInterface;

class MailService
{
  
  public function __construct(private MailerInterface $mailer)
  {
  }
  
  public function sendPasswordReset(string $toEmail, string $toName, string $resetLink): bool
  {
      
    $subject = "Redefinição de senha";
      
    $content = "
      <p>Olá <strong>{$toName}</strong>,</p>
      <p>Recebemos uma solicitação para redefinir sua senha. Clique no botão abaixo para continuar:</p>
      <div style='text-align:center; margin:30px 0;'>
        <a href='{$resetLink}' style='display:inline-block; padding:12px 28px; background:#038de7; color:#fff; text-decoration:none; border-radius:12px; font-weight:bold;'>Redefinir Senha</a>
      </div>
      <p>Se você não solicitou essa alteração, pode ignorar este e-mail.</p>
    ";

    return $this->mailer->send($toEmail, $toName, $subject, $content);
  
  }
  
  public function sendSignupConfirmation(string $toEmail, string $toName, string $welcomeLink): bool
  {

    $subject = "Bem-vindo à Loja Exemplo!";
    
    $content = "
      <p>Olá <strong>{$toName}</strong>,</p>
      <p>Seu cadastro foi realizado com sucesso. Clique no link abaixo para acessar sua conta:</p>
      <div style='text-align:center; margin:30px 0;'>
        <a href='{$welcomeLink}' style='display:inline-block; padding:12px 28px; background:#038de7; color:#fff; text-decoration:none; border-radius:12px; font-weight:bold;'>Acessar Conta</a>
      </div>
    ";
    
    return $this->mailer->send($toEmail, $toName, $subject, $content);
  
  }

}
