<?php

use App\Models\User;
use App\Utils\Responder;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

$app->group('/users/me', function ($group) {

  $group->get('', function (Request $request, Response $response) {

    $user = $this->get(User::class);

    $jwt = $request->getAttribute('token');

    $id = (int)$jwt['sub'];

    $results = $user->get($id);

    return Responder::success('Dados do usuário.', $results);
  
});

  $group->put('', function (Request $request, Response $response) {

    $user = $this->get(User::class);

    $jwt = $request->getAttribute('token');

    $req = $request->getParsedBody();
    
    $req['user_id'] = (int)$jwt['sub'];

    $user->setAttributes($req);

    $user->update();

    return Responder::success('Dados do usuário atualizados com sucesso.');
      
  });

  $group->delete('', function (Request $request, Response $response) {

    $user = $this->get(User::class);

    $jwt = $request->getAttribute('token');

    $id = (int)$jwt['sub'];

    $user->delete($id);
    
    return Responder::success('Conta excluída com sucesso.');

  });

});