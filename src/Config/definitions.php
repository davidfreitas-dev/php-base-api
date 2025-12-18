<?php

return [

  // CORE
  \App\DB\Database::class => DI\create(\App\DB\Database::class),

  // MODELS
  \App\Models\User::class => DI\autowire()->constructor(DI\get(\App\DB\Database::class)),

  // SERVICES
  \App\Services\MailService::class     => DI\autowire(),
  \App\Services\ErrorLogService::class => DI\autowire(),
  \App\Services\AuthService::class     => DI\autowire(),
  \App\Services\TokenService::class    => DI\autowire(),

  // INTERFACES
  \App\Interfaces\MailerInterface::class => DI\autowire(\App\Mail\Mailer::class),

  // MIDDLEWARE
  \App\Middleware\GlobalErrorMiddleware::class => DI\autowire(),
  \App\Middleware\JwtAuthMiddleware::class     => DI\autowire(),

];
