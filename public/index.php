<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Selective\BasePath\BasePathMiddleware;
use Slim\Factory\AppFactory;

require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');

$dotenv->load();

$app = AppFactory::create();

$app->addBodyParsingMiddleware();

$app->addRoutingMiddleware();

$app->add(new BasePathMiddleware($app));

$app->addErrorMiddleware(true, true, true);

$app->add(new Tuupola\Middleware\JwtAuthentication([
  "header" => "X-Token",
  "regexp" => "/(.*)/",
  "path" => "/",
  "secure" => "false",
  "ignore" => ["/signin", "/signup", "/forgot", "/forgot/token", "/forgot/reset"],
  "secret" => $_ENV['JWT_SECRET_KEY'],
  "algorithm" => "HS256",
  "error" => function ($response, $arguments) {
    $data["status"] = "error";
    $data["message"] = $arguments["message"];
    $response->getBody()->write(json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
    return $response->withHeader('content-type', 'application/json');
  }
]));

require_once('auth.php');
require_once('user.php');

$app->run();