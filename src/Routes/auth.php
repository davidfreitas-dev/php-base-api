<?php

use App\Utils\Responder;
use App\Services\AuthService;
use App\Services\MailService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

$app->group('/auth', function ($group) {

  $group->post('/signup', function (Request $request, Response $response) {

    $auth = $this->get(AuthService::class);
  
    $req = $request->getParsedBody();

    $results = $auth->signup($req);

    return Responder::success('Cadastro efetuado com sucesso.', $results, 201);

  });

  $group->post('/signin', function (Request $request, Response $response) {

    $auth = $this->get(AuthService::class);
  
    $req = $request->getParsedBody();

    $authData = $auth->signin($req['login'], $req['password']);

    return Responder::success('Autenticação efetuada com sucesso.', $authData);

  });

  $group->post('/logout', function (Request $request, Response $response) {
      
    $auth = $this->get(AuthService::class);
    
    $result = $auth->logout($request);
    
    return Responder::success($result['message']);
      
  });

  $group->post('/forgot', function (Request $request, Response $response) {

    $auth = $this->get(AuthService::class);
  
    $mail = $this->get(MailService::class);
    
    $req = $request->getParsedBody();

    $authData = $auth->requestPasswordReset($req['email']);

    $mail->sendPasswordReset(
      $authData['user']['email'],
      $authData['user']['name'],
      $authData['link']
    );

    return Responder::success('E-mail de recuperação enviado com sucesso.');

  });

  $group->post('/verify', function (Request $request, Response $response) {

    $auth = $this->get(AuthService::class);
  
    $req = $request->getParsedBody();

    $auth->verifyResetToken($req['token']);

    return Responder::success('Token de recuperação validado com sucesso.');

  });

  $group->post('/reset', function (Request $request, Response $response) {

    $auth = $this->get(AuthService::class);
  
    $req = $request->getParsedBody();

    $auth->resetPassword($req['token'], $req['password']);

    return Responder::success('Senha redefinida com sucesso.');

  });

  $group->post('/token', function (Request $request, Response $response) {

    $auth = $this->get(AuthService::class);
  
    $req = $request->getParsedBody();

    $authData = $auth->refreshToken($req['refresh_token']);

    return Responder::success('Token atualizado com sucesso.', $authData);

  });

});