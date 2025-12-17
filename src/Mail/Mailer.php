<?php 

namespace App\Mail;

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use App\Interfaces\MailerInterface;
use App\Enums\HttpStatus as HTTPStatus;

class Mailer implements MailerInterface {

	private PHPMailer $mail;

  public function __construct(array $config = [])
  {

    $cfg = array_merge([
      'host'        => $_ENV['MAILER_HOST'],
      'port'        => $_ENV['MAILER_PORT'],
      'username'    => $_ENV['MAILER_USERNAME'],
      'password'    => $_ENV['MAILER_PASSWORD'],
      'from_name'   => $_ENV['MAILER_FROM_NAME'],
      'from_email'  => $_ENV['MAILER_FROM_EMAIL'],
      'smtp_secure' => $_ENV['MAILER_SMTP_SECURE']
    ], $config);

    $this->mail = $this->setupMailer($cfg);
  
  }

  private function setupMailer(array $config): PHPMailer
  {
    
    $mail = new PHPMailer(true);

    $mail->isSMTP();
    $mail->SMTPDebug  = 0;
    $mail->Host       = $config['host'];
    $mail->Port       = $config['port'];
    $mail->SMTPAuth   = true;
    $mail->Username   = $config['username'];
    $mail->Password   = $config['password'];
    $mail->SMTPSecure = $config['smtp_secure'];

    $mail->setFrom($config['from_email'], $config['from_name']);
    $mail->isHTML(true);
    $mail->CharSet = 'UTF-8';

    $mail->SMTPOptions = [
      'ssl' => [
        'verify_peer'       => false,
        'verify_peer_name'  => false,
        'allow_self_signed' => true,
      ],
    ];

    return $mail;

  }

	public function send(string $toEmail, string $toName, string $subject, string $contentHtml): bool
  {
    
    try {
    
      $this->mail->clearAddresses();
      $this->mail->addAddress($toEmail, $toName);
      $this->mail->Subject = $subject;
      $this->mail->Body    = $this->renderTemplate($subject, $contentHtml);

      return $this->mail->send();
    
    } catch (Exception $e) {
    
      throw new Exception("Erro ao enviar e-mail: {$this->mail->ErrorInfo}", HTTPStatus::SERVICE_UNAVAILABLE);
    
    }
  
  }

  private function renderTemplate(string $subject, string $contentHtml): string
  {

    ob_start();
        
    include __DIR__ . '/../../res/views/emails/default.php';
        
    return ob_get_clean();

  }

}

 ?>