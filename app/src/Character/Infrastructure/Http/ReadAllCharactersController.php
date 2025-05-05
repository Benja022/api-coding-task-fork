<?php

namespace App\Character\Infrastructure\Http;

use App\Character\Application\ReadAllCharactersUseCase;
use App\Character\Domain\CharacterToArrayTransformer;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * ReadAllCharactersController is a controller that reads all characters.
 *
 * @api
 * @package App\Character\Infrastructure\Http
 */
class ReadAllCharactersController
{
    public function __construct(
        private ReadAllCharactersUseCase $useCase
    ) {}

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        try {
            $characters = $this->useCase->execute();

            // Return success response
            $response->getBody()->write(json_encode([
                'characters' => array_map(
                    fn($character) => CharacterToArrayTransformer::transform($character),
                    $characters
                )
            ]));

            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        } catch (\Exception $e) {
            $response->getBody()->write(json_encode([
                'error' => 'Failed to list characters',
                'message' => $e->getMessage()
            ]));

            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }
}
