<?php

namespace App\Faction\Infrastructure\Http;

use App\Faction\Application\CreateFactionUseCase;
use App\Faction\Application\CreateFactionUseCaseRequest;
use App\Faction\Domain\Exception\FactionValidationException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * @api
 * @package App\Faction\Infrastructure\Http
 */
class CreateFactionController
{
    /**
     * @api
     * @param CreateFactionUseCase $useCase
     */
    public function __construct(
        private CreateFactionUseCase $useCase
    ) {}

    /**
     * @api
     * @param Request $request
     * @param Response $response
     * @return Response
     * @throws FactionValidationException
     */
    public function __invoke(Request $request, Response $response): Response
    {
        try {
            $data = json_decode($request->getBody()->getContents(), true);
            $request = new CreateFactionUseCaseRequest(
                $data['faction_name'],
                $data['description']
            );

            $faction = $this->useCase->execute($request);

            $response->getBody()->write(json_encode([
                'message' => 'Faction created successfully'
            ]));

            return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
        } catch (FactionValidationException $e) {
            $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        } catch (\Exception $e) {
            $response->getBody()->write(json_encode(['error' => 'Failed to create faction']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }
}
