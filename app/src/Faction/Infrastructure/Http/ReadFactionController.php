<?php

namespace App\Faction\Infrastructure\Http;

use App\Faction\Application\ReadFactionUseCase;
use App\Faction\Domain\FactionToArrayTransformer;
use App\Faction\Infrastructure\Persistence\Pdo\Exception\FactionNotFoundException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * @api
 * @package App\Faction\Infrastructure\Http
 */

class ReadFactionController
{
    /**
     * @api
     * @param ReadFactionUseCase $useCase
     */
    public function __construct(
        private ReadFactionUseCase $useCase
    ) {}

    /**
     * @api
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     * @throws FactionNotFoundException
     */
    public function __invoke(Request $request, Response $response, array $args): Response
    {
        try {
            $faction = $this->useCase->execute($args['id']);

            $response->getBody()->write(json_encode([
                'faction' => FactionToArrayTransformer::transform($faction)
            ]));

            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        } catch (FactionNotFoundException $e) {
            $response->getBody()->write(json_encode([
                'message' => 'Not Found'
            ]));

            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        } catch (\Exception $e) {
            $response->getBody()->write(json_encode([
                'error' => 'Failed to read faction',
                'message' => $e->getMessage()
            ]));

            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }
}
