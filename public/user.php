<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Model\User;

$app->get('/users', function (Request $request, Response $response) {

  $data = User::list();

  $response->getBody()->write(json_encode($data));

  return $response
    ->withHeader('content-type', 'application/json')
    ->withStatus($data['code']);

});

$app->get('/users/{id}', function (Request $request, Response $response) {

  $id = $request->getAttribute('id');

  $data = User::get($id);

  $response->getBody()->write(json_encode($data));

  return $response
    ->withHeader('content-type', 'application/json')
    ->withStatus($data['code']);

});

$app->put('/users/update/{id}', function (Request $request, Response $response) {

  $id = $request->getAttribute('id');

  $payload = $request->getParsedBody();

  $data = User::update($id, $payload);

  $response->getBody()->write(json_encode($data));

  return $response
    ->withHeader('content-type', 'application/json')
    ->withStatus($data['code']);

});

$app->delete('/users/delete/{id}', function (Request $request, Response $response) {

  $id = $request->getAttribute('id');

  $data = User::delete($id);

  $response->getBody()->write(json_encode($data));

  return $response
    ->withHeader('content-type', 'application/json')
    ->withStatus($data['code']);

});