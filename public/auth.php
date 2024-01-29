<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Model\Auth;

$app->post('/signup', function (Request $request, Response $response) {
 
  $data = $request->getParsedBody();

  $result = Auth::signup($data);

  $response->getBody()->write(json_encode($result));

  return $response->withHeader('content-type', 'application/json');
 
});

$app->post('/signin', function (Request $request, Response $response) {

  $data = $request->getParsedBody();

  $result = Auth::signin($data['deslogin'], $data['despassword']);

  $response->getBody()->write(json_encode($result));

  return $response->withHeader('content-type', 'application/json');

});

$app->post('/forgot', function (Request $request, Response $response) {
 
  $data = $request->getParsedBody();

  $result = Auth::getForgot($data['desemail']);

  $response->getBody()->write(json_encode($result));

  return $response->withHeader('content-type', 'application/json');
 
});

$app->post('/forgot/token', function (Request $request, Response $response) {
 
  $data = $request->getParsedBody();

  $result = Auth::validateForgotDecrypt($data['code']);

  $response->getBody()->write(json_encode($result));

  return $response->withHeader('content-type', 'application/json');
 
});

$app->post('/forgot/reset', function (Request $request, Response $response) {
 
  $data = $request->getParsedBody();

  $forgot = Auth::validateForgotDecrypt($data['code']);

  if (is_array($forgot)) {

    Auth::setForgotUsed($forgot['idrecovery']);

    $result = Auth::setNewPassword($data['despassword'], $forgot['iduser']);

  } else {

    $result = $forgot;

  }    

  $response->getBody()->write(json_encode($result));

  return $response->withHeader('content-type', 'application/json');
 
});