<?php

namespace App\Character\Infrastructure\Http;

use App\Character\Application\DeleteCharacterUseCase;
use App\Character\Infrastructure\Persistence\Pdo\Exception\CharacterNotFoundException;
use Slim\Psr7\Request;
use Slim\Psr7\Response;

/**
 * DeleteCharacterController is a controller that deletes a character.
 *
 * @api
 * @package App\Character\Infrastructure\Http
 */
class DeleteCharacterController
{
    public function __construct(private DeleteCharacterUseCase $useCase) {}

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $id = $args['id'];

        try {
            $this->useCase->execute($id);

            $response->getBody()->write(json_encode([
                'success' => true,
                'message' => 'Character correctly deleted'
            ]));

            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        } catch (CharacterNotFoundException $e) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => 'Character not found'
            ]));

            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }
    }
}
