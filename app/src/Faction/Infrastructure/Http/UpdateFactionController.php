<?php

namespace App\Faction\Infrastructure\Http;

use App\Faction\Application\UpdateFactionUseCase;
use App\Faction\Application\UpdateFactionUseCaseRequest;
use App\Faction\Domain\FactionToArrayTransformer;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * UpdateFactionController is a class that is used to update a faction.
 *
 * @package App\Faction\Infrastructure\Http
 */

class UpdateFactionController
{
    public function __construct(
        private UpdateFactionUseCase $useCase
    ) {}

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        // Parsear el cuerpo de la petición manualmente
        $rawBody = (string) $request->getBody();
        $data = json_decode($rawBody, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $response->getBody()->write(json_encode([
                'error' => 'Missing required field: faction_name',
                'message' => json_last_error_msg()
            ]));

            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        // Validar campos requeridos
        $requiredFields = ['faction_name', 'description'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                $response->getBody()->write(json_encode(['error' => "Missing required field: {$field}"]));

                return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
            }
        }

        try {
            $useCaseResponse = $this->useCase->execute(
                new UpdateFactionUseCaseRequest(
                    $data['faction_name'],
                    $data['description'],
                    $args['id']
                )
            );

            // Devolver respuesta de éxito
            $response->getBody()->write(json_encode([
                'faction' => FactionToArrayTransformer::transform($useCaseResponse),
                'message' => 'Faction updated successfully'
            ]));

            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        } catch (\Exception $e) {
            $response->getBody()->write(json_encode([
                'error' => 'Error updating faction',
                'message' => $e->getMessage()
            ]));

            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }
}
