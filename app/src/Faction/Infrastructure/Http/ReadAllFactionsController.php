<?php

namespace App\Faction\Infrastructure\Http;

use App\Faction\Application\ReadAllFactionsUseCase;
use App\Faction\Domain\FactionToArrayTransformer;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * @api
 * @package App\Faction\Infrastructure\Http
 */
class ReadAllFactionsController
{
    /**
     * @api
     * @param ReadAllFactionsUseCase $useCase
     */
    public function __construct(
        private ReadAllFactionsUseCase $useCase
    ) {}

    /**
     * @api
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     */
    public function __invoke(Request $request, Response $response, array $args): Response
    {
        try {
            $factions = $this->useCase->execute();

            $response->getBody()->write(json_encode([
                'factions' => array_map(
                    fn($faction) => FactionToArrayTransformer::transform($faction),
                    $factions
                )
            ]));

            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        } catch (\Exception $e) {
            $response->getBody()->write(json_encode([
                'error' => 'Failed to list factions',
                'message' => $e->getMessage()
            ]));

            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }
}
