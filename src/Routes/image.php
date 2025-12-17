<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteCollectorProxy;
use App\Utils\Responder;
use App\Handlers\UploadHandler;

$app->group('/images', function (RouteCollectorProxy $group) {
    
  $group->post('', function (Request $request, Response $response) {
    
    $uploadHandler = $this->get(UploadHandler::class);
    
    $result = $uploadHandler->uploadImage($request);
    
    return Responder::success($result);

  });

});
