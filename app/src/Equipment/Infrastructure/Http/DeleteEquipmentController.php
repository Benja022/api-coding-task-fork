<?php

namespace App\Equipment\Infrastructure\Http;

use App\Equipment\Application\DeleteEquipmentUseCase;
use App\Equipment\Infrastructure\persistence\Pdo\Exception\EquipmentNotFoundException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class DeleteEquipmentController
{
    public function __construct(
        private DeleteEquipmentUseCase $useCase
    ) {}

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        try {
            $id = (int) $args['id'];
            $this->useCase->execute($id);

            $response->getBody()->write(json_encode([
                'success' => true,
                'message' => 'Equipment deleted successfully'
            ]));

            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        } catch (EquipmentNotFoundException $e) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => 'Equipment not found'
            ]));

            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        } catch (\PDOException $e) {
            if ($e->getCode() == '23000') { // Código de error para violación de clave foránea
                $response->getBody()->write(json_encode([
                    'success' => false,
                    'message' => 'Cannot delete equipment because it is being used by one or more characters'
                ]));
            } else {
                $response->getBody()->write(json_encode([
                    'success' => false,
                    'message' => 'Failed to delete equipment'
                ]));
            }

            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        } catch (\Exception $e) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => 'Failed to delete equipment'
            ]));

            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }
}
