<?php

namespace App\Character\Infrastructure\Http;

use App\Character\Application\UpdateCharacterUseCase;
use App\Character\Domain\CharacterToArrayTransformer;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Character\Application\UpdateCharacterUseCaseRequest;

/**
 * UpdateCharacterController is a controller that updates a character.
 *
 * @api
 * @package App\Character\Infrastructure\Http
 */
class UpdateCharacterController
{
    public function __construct(
        private UpdateCharacterUseCase $useCase
    ) {}

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        // Parsear el cuerpo de la petición manualmente
        $rawBody = (string) $request->getBody();
        $data = json_decode($rawBody, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $response->getBody()->write(json_encode([
                'error' => 'Invalid JSON',
                'message' => json_last_error_msg()
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        // Validar campos requeridos
        $requiredFields = ['name', 'birth_date', 'kingdom', 'equipment_id', 'faction_id'];
        $missingFields = [];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                $missingFields[] = $field;
            }
        }

        if (!empty($missingFields)) {
            $response->getBody()->write(json_encode(['error' => 'Missing required fields']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        try {
            $useCaseResponse = $this->useCase->execute(
                new UpdateCharacterUseCaseRequest(
                    $data['name'],
                    $data['birth_date'],
                    $data['kingdom'],
                    $data['equipment_id'],
                    $data['faction_id'],
                    $args['id']
                )
            );

            // Return success response
            $response->getBody()->write(json_encode([
                'character' => CharacterToArrayTransformer::transform($useCaseResponse),
                'message' => 'Character updated correctly'
            ]));

            return $response->withHeader('content-Type', 'application/json')->withStatus(200);
        } catch (\Exception $e) {
            $response->getBody()->write(json_encode([
                'error' => 'Error updating character',
                'message' => 'Unexpected error'
            ]));

            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }
}
