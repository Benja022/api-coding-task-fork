<?php

namespace App\Test\Shared\Infrastructure\Exception\Http;

use App\Shared\Infrastructure\Exception\Http\ErrorHandlerMiddleware;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Exception\HttpNotFoundException;
use Slim\Exception\HttpMethodNotAllowedException;
use Slim\Psr7\Factory\ServerRequestFactory;
use Slim\Psr7\Factory\ResponseFactory;
use PHPUnit\Framework\MockObject\MockObject;

class ErrorHandlerMiddlewareTest extends TestCase
{
    private ErrorHandlerMiddleware $middleware;
    private ServerRequestInterface $request;
    private RequestHandlerInterface&MockObject $handler;

    protected function setUp(): void
    {
        $this->middleware = new ErrorHandlerMiddleware();
        $this->request = (new ServerRequestFactory())->createServerRequest('GET', '/test');
        $this->handler = $this->createMock(RequestHandlerInterface::class);
    }

    #[Test]
    #[Group('shared')]
    #[Group('middleware')]
    public function testProcessHandlesHttpNotFoundException(): void
    {
        $this->handler->expects($this->once())
            ->method('handle')
            ->willThrowException(new HttpNotFoundException($this->request));

        $response = $this->middleware->process($this->request, $this->handler);

        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEquals('application/json', $response->getHeaderLine('Content-Type'));

        $body = json_decode((string) $response->getBody(), true);
        $this->assertEquals('Not Found', $body['error']);
        $this->assertEquals('La ruta solicitada no existe', $body['message']);
        $this->assertEquals('/test', $body['path']);
    }

    #[Test]
    #[Group('shared')]
    #[Group('middleware')]
    public function testProcessHandlesHttpMethodNotAllowedException(): void
    {
        $this->handler->expects($this->once())
            ->method('handle')
            ->willThrowException(new HttpMethodNotAllowedException($this->request));

        $response = $this->middleware->process($this->request, $this->handler);

        $this->assertEquals(405, $response->getStatusCode());
        $this->assertEquals('application/json', $response->getHeaderLine('Content-Type'));

        $body = json_decode((string) $response->getBody(), true);
        $this->assertEquals('Method Not Allowed', $body['error']);
        $this->assertEquals('El método HTTP no está permitido para esta ruta', $body['message']);
        $this->assertEquals('GET', $body['method']);
        $this->assertEquals('/test', $body['path']);
    }

    #[Test]
    #[Group('shared')]
    #[Group('middleware')]
    public function testProcessHandlesGenericException(): void
    {
        $this->handler->expects($this->once())
            ->method('handle')
            ->willThrowException(new \Exception('Test error'));

        $response = $this->middleware->process($this->request, $this->handler);

        $this->assertEquals(500, $response->getStatusCode());
        $this->assertEquals('application/json', $response->getHeaderLine('Content-Type'));

        $body = json_decode((string) $response->getBody(), true);
        $this->assertEquals('Internal Server Error', $body['error']);
        $this->assertEquals('Test error', $body['message']);
    }
}
