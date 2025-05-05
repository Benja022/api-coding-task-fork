<?php

namespace App\Equipment\Infrastructure\Http;

use App\Equipment\Application\ReadEquipmentUseCase;
use App\Equipment\Domain\EquipmentToArrayTransformer;
use App\Equipment\Infrastructure\Persistence\Pdo\Exception\EquipmentNotFoundException;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class ReadEquipmentController
{
    public function __construct(
        private ReadEquipmentUseCase $useCase
    ) {}

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        try {
            $equipment = $this->useCase->execute($args['id']);

            $response->getBody()->write(json_encode([
                'equipment' => EquipmentToArrayTransformer::transform($equipment)
            ]));

            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        } catch (EquipmentNotFoundException $e) {
            $response->getBody()->write(json_encode([
                'error' => 'Equipment not found'
            ]));

            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        } catch (\Exception $e) {
            $response->getBody()->write(json_encode([
                'error' => 'Failed to read equipment',
                'message' => $e->getMessage()
            ]));

            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }
}
