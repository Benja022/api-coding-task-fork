<?php

namespace App\Equipment\Infrastructure\Http;

use App\Equipment\Application\ReadAllEquipmentsUseCase;
use App\Equipment\Domain\EquipmentToArrayTransformer;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ReadAllEquipmentsController
{
    public function __construct(
        private ReadAllEquipmentsUseCase $useCase
    ) {}

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        try {
            $equipments = $this->useCase->execute();

            $response->getBody()->write(json_encode([
                'equipments' => array_map(
                    fn($equipment) => EquipmentToArrayTransformer::transform($equipment),
                    $equipments
                )
            ]));

            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        } catch (\Exception $e) {
            $response->getBody()->write(json_encode([
                'error' => 'Failed to list equipments',
                'message' => $e->getMessage()
            ]));

            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }
}
