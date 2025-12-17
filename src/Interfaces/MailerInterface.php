<?php

namespace App\Interfaces;

interface MailerInterface
{

  public function send(string $toEmail, string $toName, string $subject, string $contentHtml): bool;

}