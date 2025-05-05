<?php

namespace App\Test\Faction\Infrastructure\Http;

use App\Faction\Application\UpdateFactionUseCase;
use App\Faction\Domain\FactionFactory;
use App\Faction\Domain\FactionRepository;
use App\Test\Shared\BaseTestCase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
use App\Faction\Infrastructure\Http\UpdateFactionController;
use App\Faction\Infrastructure\Persistence\InMemory\ArrayFactionRepository;
use App\Test\Faction\Application\MotherObject\UpdateFactionUseCaseRequestMotherObject;
use Slim\Psr7\Response;

class UpdateFactionControllerTest extends BaseTestCase
{
    private FactionRepository $repository;
    private UpdateFactionUseCase $useCase;
    private UpdateFactionController $controller;

    protected function setUp(): void
    {
        $this->repository = new ArrayFactionRepository();
        $this->useCase = new UpdateFactionUseCase($this->repository);
        $this->controller = new UpdateFactionController($this->useCase);
    }

    #[Test]
    #[Group('unit')]
    #[Group('updateFaction')]
    public function testSuccesfullUpdateReturns200(): void
    {
        // Arrange
        $faction = FactionFactory::build(
            'Test Faction',
            'Test Description',
            1
        );
        $this->repository->save($faction);

        $updateData = [
            'faction_name' => 'Updated Faction',
            'description' => 'Updated Description'
        ];

        $request = $this->createJsonRequest('PUT', '/factions/1');
        $request->getBody()->write(json_encode($updateData));
        $request->getBody()->rewind();

        $response = new Response();
        $result = $this->controller->__invoke($request, $response, ['id' => 1]);

        $this->assertEquals(200, $result->getStatusCode());

        $body = json_decode((string) $result->getBody(), true);
        $this->assertArrayHasKey('faction', $body);
        $this->assertEquals('Updated Faction', $body['faction']['faction_name']);
    }

    #[Test]
    #[Group('unit')]
    #[Group('updateFaction')]
    public function testInvalidJsonReturns400(): void
    {
        $request = $this->createJsonRequest(
            'PUT',
            '/factions/1',
        );
        $request->getBody()->write('{"name": "Test Faction", "description": "Test Description"');
        $request->getBody()->rewind();

        $response = new Response();

        $result = $this->controller->__invoke($request, $response, ['id' => 1]);

        $this->assertEquals(400, $result->getStatusCode());

        $body = json_decode((string) $result->getBody(), true);
        $this->assertArrayHasKey('error', $body);
        $this->assertEquals('Missing required field: faction_name', $body['error']);
    }

    #[Test]
    #[Group('unit')]
    #[Group('updateFaction')]
    public function testInternalErrorReturns500(): void
    {
        // Arrange
        $faction = FactionFactory::build(
            'Test Faction',
            'Test Description',
            1
        );
        $this->repository->save($faction);
        /** @var UpdateFactionUseCase&\PHPUnit\Framework\MockObject\MockObject $mockUseCase */
        $mockUseCase = $this->createMock(UpdateFactionUseCase::class);
        $mockUseCase->method('execute')
            ->willThrowException(new \RuntimeException('Error interno'));

        $controller = new UpdateFactionController($mockUseCase);

        $request = $this->createJsonRequest(
            'PUT',
            '/factions/1',
        );
        $request->getBody()->write(json_encode(UpdateFactionUseCaseRequestMotherObject::validAsArray()));
        $request->getBody()->rewind();

        $response = new Response();

        $result = $controller->__invoke($request, $response, ['id' => 1]);

        $this->assertEquals(500, $result->getStatusCode());
        $this->assertEquals('application/json', $result->getHeaderLine('Content-Type'));

        $body = json_decode((string) $result->getBody(), true);
        $this->assertEquals('Error updating faction', $body['error']);
        $this->assertEquals('Error interno', $body['message']);
    }

    #[Test]
    #[Group('unit')]
    #[Group('updateFaction')]
    public function givenMissingRequiredFieldWhenUpdateFactionThenReturn400(): void
    {
        // Arrange
        $updateData = [
            'description' => 'Updated Description'  // Faltando faction_name
        ];

        $request = $this->createJsonRequest('PUT', '/factions/1');
        $request->getBody()->write(json_encode($updateData));
        $request->getBody()->rewind();

        $response = new Response();
        $result = $this->controller->__invoke($request, $response, ['id' => 1]);

        // Assert
        $this->assertEquals(400, $result->getStatusCode());
        $this->assertEquals('application/json', $result->getHeaderLine('Content-Type'));

        $body = json_decode((string) $result->getBody(), true);
        $this->assertEquals('Missing required field: faction_name', $body['error']);
    }
}
