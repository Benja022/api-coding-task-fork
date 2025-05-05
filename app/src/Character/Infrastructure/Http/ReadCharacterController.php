<?php

namespace App\Character\Infrastructure\Http;

use App\Character\Application\ReadCharacterUseCase;
use App\Character\Domain\CharacterToArrayTransformer;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * ReadCharacterController is a controller that reads a character.
 *
 * @api
 * @package App\Character\Infrastructure\Http
 */
class ReadCharacterController
{
    public function __construct(
        private ReadCharacterUseCase $useCase
    ) {}

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        try {
            $character = $this->useCase->execute(
                $args['id']
            );

            // Return success response
            $response->getBody()->write(json_encode([
                'character' => CharacterToArrayTransformer::transform($character)
            ]));

            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        } catch (\Exception $e) {
            $response->getBody()->write(json_encode([
                'error' => 'Failed to read character',
                'message' => $e->getMessage()
            ]));

            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }
}
