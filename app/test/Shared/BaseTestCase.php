<?php

declare(strict_types=1);

namespace App\Test\Shared;

use DI\ContainerBuilder;
use Dotenv\Dotenv;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Slim\App;
use Slim\Factory\AppFactory;
use Slim\Psr7\Factory\ServerRequestFactory;
use Slim\Psr7\Factory\StreamFactory;
use Slim\Psr7\Headers;
use Slim\Psr7\Request as SlimRequest;
use Slim\Psr7\Uri;
use PHPUnit\Framework\MockObject\MockObject;
use PDO;
use Slim\Middleware\BodyParserMiddleware;

abstract class BaseTestCase extends TestCase
{
    protected function getAppInstanceWithCache(): App
    {
        return $this->buildAppInstance(true);
    }

    protected function getAppInstanceWithoutCache(): App
    {
        return $this->buildAppInstance(false);
    }

    private function buildAppInstance(bool $cacheEnabled): App
    {
        // Limpiar variables de entorno existentes
        unset($_ENV['APP_ENV'], $_SERVER['APP_ENV']);
        putenv('APP_ENV');

        // Establecer modo de prueba
        putenv('APP_ENV=test');
        $_ENV['APP_ENV'] = 'test';
        $_SERVER['APP_ENV'] = 'test';

        // Cargar .env
        $dotenv = Dotenv::createImmutable(dirname(__DIR__, 2));
        $dotenv->load();

        // Forzar variable de entorno
        putenv('CACHE_ENABLED=' . ($cacheEnabled ? '1' : '0'));
        $_ENV['CACHE_ENABLED'] = $cacheEnabled ? '1' : '0';
        $_SERVER['CACHE_ENABLED'] = $cacheEnabled ? '1' : '0';

        $containerBuilder = new ContainerBuilder();
        $definitions = require dirname(__DIR__, 2) . '/config/definitions.php';
        $definitions($containerBuilder);

        $container = $containerBuilder->build();

        AppFactory::setContainer($container);
        $app = AppFactory::create();

        // Agregar el middleware para pasear a JSON
        $app->addBodyParsingMiddleware();

        // Cargar rutas
        $routes = require dirname(__DIR__, 2) . '/config/routes.php';
        $routes($app);

        // ⚠️ Verificar que se usa la base de datos de test
        $this->assertUsingTestDatabase($app);

        return $app;
    }

    protected function createJsonRequest(
        string $method,
        string $path,
        array $body = [],
        array $headers = ['Content-Type' => 'application/json']
    ): ServerRequestInterface {
        $streamFactory = new StreamFactory();
        $stream = $streamFactory->createStream(json_encode($body));
        $stream->rewind();

        $serverRequestFactory = new ServerRequestFactory();
        $request = $serverRequestFactory->createServerRequest($method, $path);
        return $request->withBody($stream)->withHeader('Content-Type', 'application/json');
    }


    protected function createRequest(
        string $method,
        string $path,
        array $headers = ['HTTP_ACCEPT' => 'application/json'],
        array $cookies = [],
        array $serverParams = []
    ): ServerRequestInterface {
        $uri = new Uri('', '', 80, $path);
        $handle = fopen('php://temp', 'w+');
        $stream = (new StreamFactory())->createStreamFromResource($handle);

        $h = new Headers();
        foreach ($headers as $name => $value) {
            $h->addHeader($name, $value);
        }

        return new SlimRequest($method, $uri, $h, $cookies, $serverParams, $stream);
    }

    /**
     * Crea un mock de repositorio con `find()` que devuelve entidades por ID.
     *
     * @template T of object
     * @param class-string $repositoryInterface Ej: EquipmentRepository::class
     * @param T[] $entities Entidades que implementen getId()
     * @return T&MockObject
     */
    protected function mockRepositoryWithFind(string $repositoryInterface, array $entities): object
    {
        /** @var MockObject $repository */
        $repository = $this->createMock($repositoryInterface);

        $repository->method('find')
            ->willReturnCallback(function (int $id) use ($entities) {
                foreach ($entities as $entity) {
                    if ($entity->getId() === $id) {
                        return $entity;
                    }
                }
                return null;
            });

        return $repository;
    }

    protected function assertUsingTestDatabase(App $app): void
    {
        $pdo = $app->getContainer()->get(PDO::class);
        $result = $pdo->query("SELECT DATABASE()")->fetchColumn();
        $this->assertEquals('test', $result, 'La base de datos debe ser "test"');
    }
}
