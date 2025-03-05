<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Models\User;

$app->get('/users', function (Request $request, Response $response) {

  $results = User::list();

  $response->getBody()->write(json_encode($results));

  return $response
    ->withHeader('content-type', 'application/json')
    ->withStatus($results['code']);

});

$app->get('/users/{id}', function (Request $request, Response $response, array $args) {

  $id = (int)$args['id'];

  $results = User::get($id);

  $response->getBody()->write(json_encode($results));

  return $response
    ->withHeader('content-type', 'application/json')
    ->withStatus($results['code']);

});

$app->put('/users/update', function (Request $request, Response $response, array $args) {

  $data = $request->getParsedBody();
  
  $jwt = $request->getAttribute("jwt");

  $data['iduser'] = (int)$jwt['iduser'];

  $user = new User();

  $user->setAttributes($data);

  $results = $user->update();

  $response->getBody()->write(json_encode($results));

  return $response
    ->withHeader('content-type', 'application/json')
    ->withStatus($results['code']);

});

$app->delete('/users/delete/{id}', function (Request $request, Response $response, array $args) {

  $id = (int)$args['id'];

  $results = User::delete($id);

  $response->getBody()->write(json_encode($results));

  return $response
    ->withHeader('content-type', 'application/json')
    ->withStatus($results['code']);

});