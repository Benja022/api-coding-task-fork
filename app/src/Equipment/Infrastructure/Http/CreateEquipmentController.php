<?php

namespace App\Equipment\Infrastructure\Http;

use App\Equipment\Application\CreateEquipmentUseCase;
use App\Equipment\Application\CreateEquipmentUseCaseRequest;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class CreateEquipmentController
{
    public function __construct(private CreateEquipmentUseCase $useCase) {}

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        try {
            // ⚠️ Leer el body y parsear JSON manualmente (NO usar getParsedBody)
            $rawBody = (string) $request->getBody();
            $data = json_decode($rawBody, true);

            // ⚠️ JSON mal formado
            if (json_last_error() !== JSON_ERROR_NONE) {
                $response->getBody()->write(json_encode([
                    'error' => 'Invalid JSON',
                    'message' => json_last_error_msg()
                ]));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
            }

            // ⚠️ Validar campos obligatorios y no vacíos
            $requiredFields = ['name', 'type', 'made_by'];
            foreach ($requiredFields as $field) {
                if (!isset($data[$field]) || trim($data[$field]) === '') {
                    $response->getBody()->write(json_encode([
                        'error' => ucfirst("{$field} is required")
                    ]));
                    return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
                }
            }

            $request = new CreateEquipmentUseCaseRequest(
                $data['name'],
                $data['type'],
                $data['made_by']
            );

            $equipment = $this->useCase->execute($request);

            $response->getBody()->write(json_encode([
                'equipment' => [
                    'id' => $equipment->getId(),
                    'name' => $equipment->getName(),
                    'type' => $equipment->getType(),
                    'made_by' => $equipment->getMadeBy()
                ]
            ]));

            return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
        } catch (\Exception $e) {
            $response->getBody()->write(json_encode([
                'error' => 'Unexpected error',
                'message' => $e->getMessage()
            ]));

            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }
}
