<?php

namespace App\Shared\Infrastructure\Exception\Http;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Exception\HttpNotFoundException;
use Slim\Exception\HttpMethodNotAllowedException;

/**
 * ErrorHandlerMiddleware is a middleware that handles errors.
 *
 * @package App\Shared\Infrastructure\Exception\Http
 */

class ErrorHandlerMiddleware implements MiddlewareInterface
{
    public function process(Request $request, RequestHandler $handler): Response
    {
        try {
            return $handler->handle($request);
        } catch (HttpNotFoundException $e) {
            $response = new \Slim\Psr7\Response();
            $response->getBody()->write(json_encode([
                'error' => 'Not Found',
                'message' => 'La ruta solicitada no existe',
                'path' => $request->getUri()->getPath()
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        } catch (HttpMethodNotAllowedException $e) {
            $response = new \Slim\Psr7\Response();
            $response->getBody()->write(json_encode([
                'error' => 'Method Not Allowed',
                'message' => 'El método HTTP no está permitido para esta ruta',
                'method' => $request->getMethod(),
                'path' => $request->getUri()->getPath()
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(405);
        } catch (\Exception $e) {
            $response = new \Slim\Psr7\Response();
            $response->getBody()->write(json_encode([
                'error' => 'Internal Server Error',
                'message' => $e->getMessage()
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }
}
