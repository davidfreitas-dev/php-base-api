<?php 

namespace App\Mail;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../../vendor/autoload.php';

class Mailer {

	private $mail;

	public function __construct($toAddress, $toName, $subject, $data = array())
	{
		
		$this->mail = new PHPMailer(true);

		//Enable SMTP debugging
		// 0 = off (for production use)
		// 1 = client messages
		// 2 = client and server messages
		$this->mail->SMTPDebug = 0;

		//Tell PHPMailer to use SMTP
		$this->mail->isSMTP();		

		//Ask for HTML-friendly debug output
		// $this->mail->Debugoutput = 'html';

		//Set the hostname of the mail server
		$this->mail->Host = 'smtp.gmail.com';
		// use
		// $this->mail->Host = gethostbyname('smtp.gmail.com');
		// if your network does not support SMTP over IPv6

		//Whether to use SMTP authentication
		$this->mail->SMTPAuth = true;

		//Username to use for SMTP authentication - use full email address for gmail
		$this->mail->Username = $_ENV['MAILER_USERNAME'];

		//Password to use for SMTP authentication
		$this->mail->Password = $_ENV['MAILER_PASSWORD'];

		//Set the encryption system to use - ssl (deprecated) or tls
		$this->mail->SMTPSecure = 'tls';

		//Set the SMTP port number - 587 for authenticated TLS, a.k.a. RFC4409 SMTP submission
		$this->mail->Port = 587;

		$this->mail->SMTPOptions = array(
			'ssl' => array(
				'verify_peer' => false,
				'verify_peer_name' => false,
				'allow_self_signed' => true
			)
		);

		//Set who the message is to be sent from
		$this->mail->setFrom($_ENV['MAILER_USERNAME'], $_ENV['MAILER_NAME_FROM']);

		//Set an alternative reply-to address
		//$this->mail->addReplyTo('replyto@example.com', 'First Last');

		//Set who the message is to be sent to
		$this->mail->addAddress($toAddress, $toName);

		//Read an HTML message body from an external file, convert referenced images to embedded,
		//convert HTML into a basic plain-text alternative body
		$this->mail->isHTML(true);

		$this->mail->CharSet = 'UTF-8';

		//Set the subject line
		$this->mail->Subject = $subject;
		//Replace the plain text body with one created manually
		$this->mail->Body    = 'Hi, ' . $data['name'] . '<br /><br />Click on the link below to set new password <br /><br /> ' . $data['link'];
		$this->mail->AltBody = 'This is a plain-text message body';

		//Attach an image file
		//$mail->addAttachment('images/phpmailer_mini.png');

	}

	public function send()
	{

		try {
			
			return $this->mail->send();

		} catch (Exception $e) {
    		
			echo "Message could not be sent. Mailer Error: {$this->mail->ErrorInfo}";
		
		}

	}

}

 ?>