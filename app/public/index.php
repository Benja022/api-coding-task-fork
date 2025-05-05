<?php

use Slim\Factory\AppFactory;
use Slim\Middleware\BodyParsingMiddleware;

/* Alternativa a BodyParsingMiddleware sería usar: $data->json_decode(file_get_contents('php://input'), true);
habria que ponerlo en el controlador*/

require __DIR__ . '/../vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// Create Container
$containerBuilder = new DI\ContainerBuilder();

$definitions = require __DIR__ . '/../config/definitions.php';
$definitions($containerBuilder);

$container = $containerBuilder->build();

$app = AppFactory::create(container: $container);

$routes = require __DIR__ . '/../config/routes.php';
$routes($app);

// Add middleware to parse the body of the request as JSON
$app->add(new BodyParsingMiddleware());

// Run the application
$app->run();
