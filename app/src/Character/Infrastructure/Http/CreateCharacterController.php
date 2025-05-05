<?php

namespace App\Character\Infrastructure\Http;

use App\Character\Application\CreateCharacterUseCase;
use App\Character\Domain\CharacterToArrayTransformer;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Character\Application\CreateCharacterUseCaseRequest;

/**
 * CreateCharacterController is a controller that creates a character.
 *
 * @api
 * @package App\Character\Infrastructure\Http
 */
class CreateCharacterController
{
    public function __construct(private CreateCharacterUseCase $useCase) {}

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
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) { // Invalid or empty field
                $response->getBody()->write(json_encode(['error' => "Missing required field: {$field}"]));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
            }
        }

        try {
            $useCaseResponse = $this->useCase->execute(
                new CreateCharacterUseCaseRequest(
                    $data['name'],
                    $data['birth_date'],
                    $data['kingdom'],
                    $data['equipment_id'],
                    $data['faction_id']
                )
            );

            // Return success response
            $response->getBody()->write(json_encode([
                'character' => CharacterToArrayTransformer::transform($useCaseResponse->getCharacter()),
                'message' => 'Character created successfully'
            ]));

            return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
        } catch (\Exception $e) {
            $response->getBody()->write(json_encode([
                'error' => 'Error creating character',
                'message' => $e->getMessage()
            ]));

            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }
}
