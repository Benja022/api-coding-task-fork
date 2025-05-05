<?php

namespace App\Equipment\Infrastructure\Http;

use App\Equipment\Application\UpdateEquipmentUseCase;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Equipment\Application\UpdateEquipmentUseCaseRequest;
use App\Equipment\Domain\EquipmentToArrayTransformer;

class UpdateEquipmentController
{
    public function __construct(
        private UpdateEquipmentUseCase $useCase
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
        $requiredFields = ['name', 'type', 'made_by'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                $response->getBody()->write(json_encode(['error' => "Missing required field: {$field}"]));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
            }
        }

        try {
            $useCaseResponse = $this->useCase->execute(
                new UpdateEquipmentUseCaseRequest(
                    $data['name'],
                    $data['type'],
                    $data['made_by'],
                    $args['id']
                )
            );

            // Devolver respuesta de éxito
            $response->getBody()->write(json_encode([
                'equipment' => EquipmentToArrayTransformer::transform($useCaseResponse),
                'message' => 'Equipment updated successfully'
            ]));

            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        } catch (\Exception $e) {
            $response->getBody()->write(json_encode([
                'error' => 'Error updating equipment',
                'message' => $e->getMessage()
            ]));

            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }
}
