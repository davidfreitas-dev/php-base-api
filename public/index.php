<?php

declare(strict_types=1);

use DI\ContainerBuilder;
use Slim\Factory\AppFactory;
use App\Utils\Responder;
use App\Middleware\CorsMiddleware;
use App\Middleware\GlobalErrorMiddleware;
use Selective\BasePath\BasePathMiddleware;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use JimTools\JwtAuth\Middleware\JwtAuthentication;
use JimTools\JwtAuth\Decoder\FirebaseDecoder;
use JimTools\JwtAuth\Rules\RequestPathRule;
use JimTools\JwtAuth\Options;
use JimTools\JwtAuth\Secret;

define('APP_ROOT', dirname(__DIR__));

require APP_ROOT . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(APP_ROOT);

$dotenv->load();

$containerBuilder = new ContainerBuilder();

$containerBuilder->addDefinitions(APP_ROOT . '/src/Config/definitions.php');

$container = $containerBuilder->build();

AppFactory::setContainer($container);

$app = AppFactory::create();

$app->addBodyParsingMiddleware();

$app->add(new BasePathMiddleware($app));

$app->addRoutingMiddleware();

$errorMiddleware = $app->addErrorMiddleware($_ENV['APP_DEBUG'] === 'true', true, true);

$errorMiddleware->setDefaultErrorHandler($container->get(GlobalErrorMiddleware::class));

$app->add(new CorsMiddleware());

$options = new Options(
  isSecure: $_ENV['APP_ENV'] === 'production'
);

$decoder = new FirebaseDecoder(new Secret($_ENV['JWT_SECRET_KEY'], 'HS256'));

$rules = [
  new RequestPathRule(
    paths: ['/'],
    ignore: [
      "/images", 
      "/auth/signin", 
      "/auth/signup", 
      "/auth/forgot", 
      "/auth/verify", 
      "/auth/reset", 
      "/auth/token" 
    ]
  )
];

$app->add(new JwtAuthentication($options, $decoder, $rules));

$app->get("/", function (Request $request, Response $response) {
    
  return Responder::success('PHP Slim Base API!');
  
});

require_once APP_ROOT . '/src/Routes/auth.php';
require_once APP_ROOT . '/src/Routes/user.php';
require_once APP_ROOT . '/src/Routes/image.php';

$app->run();