<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Model\User;

$app->get('/users', function (Request $request, Response $response) {

  $result = User::list();

  $response->getBody()->write(json_encode($result));

  return $response->withHeader('content-type', 'application/json');

});

$app->get('/users/{id}', function (Request $request, Response $response) {

  $id = $request->getAttribute('id');

  $result = User::getById($id);

  $response->getBody()->write(json_encode($result));

  return $response->withHeader('content-type', 'application/json');

});

$app->put('/users/update/{id}', function (Request $request, Response $response) {

  $id = $request->getAttribute('id');

  $data = $request->getParsedBody();

  $result = User::update($id, $data);

  $response->getBody()->write(json_encode($result));

  return $response->withHeader('content-type', 'application/json');

});

$app->delete('/users/delete/{id}', function (Request $request, Response $response) {

  $id = $request->getAttribute('id');

  $result = User::delete($id);

  $response->getBody()->write(json_encode($result));

  return $response->withHeader('content-type', 'application/json');

});