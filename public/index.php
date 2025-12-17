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
use App\Middleware\JwtAuthMiddleware;

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

$app->add(($container->get(JwtAuthMiddleware::class))());

$app->get("/", function (Request $request, Response $response) {
    
  return Responder::success('PHP Slim Base API!');
  
});

require_once APP_ROOT . '/src/Routes/auth.php';
require_once APP_ROOT . '/src/Routes/user.php';
require_once APP_ROOT . '/src/Routes/image.php';

$app->run();