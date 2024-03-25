<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Models\Auth;

$app->post('/signup', function (Request $request, Response $response) {
 
  $payload = $request->getParsedBody();

  $data = Auth::signup($payload);

  $response->getBody()->write(json_encode($data));

  return $response
    ->withHeader('content-type', 'application/json')
    ->withStatus($data['code']);
 
});

$app->post('/signin', function (Request $request, Response $response) {

  $payload = $request->getParsedBody();

  $data = Auth::signin($payload['deslogin'], $payload['despassword']);

  $response->getBody()->write(json_encode($data));

  return $response
    ->withHeader('content-type', 'application/json')
    ->withStatus($data['code']);

});

$app->post('/forgot', function (Request $request, Response $response) {
 
  $payload = $request->getParsedBody();

  $data = Auth::getForgotLink($payload['desemail']);

  $response->getBody()->write(json_encode($data));

  return $response
    ->withHeader('content-type', 'application/json')
    ->withStatus($data['code']);
 
});

$app->post('/forgot/token', function (Request $request, Response $response) {
 
  $payload = $request->getParsedBody();

  $data = Auth::validateForgotLink($payload['code']);

  $response->getBody()->write(json_encode($data));

  return $response
    ->withHeader('content-type', 'application/json')
    ->withStatus($data['code']);
 
});

$app->post('/forgot/reset', function (Request $request, Response $response) {
 
  $payload = $request->getParsedBody();

  $forgot = Auth::validateForgotLink($payload['code']);

  if (is_array($forgot)) {

    Auth::setForgotUsed($forgot['idrecovery']);

    $data = Auth::setNewPassword($payload['despassword'], $forgot['iduser']);

  } else {

    $data = $forgot;

  }    

  $response->getBody()->write(json_encode($data));

  return $response
    ->withHeader('content-type', 'application/json')
    ->withStatus($data['code']);
 
});