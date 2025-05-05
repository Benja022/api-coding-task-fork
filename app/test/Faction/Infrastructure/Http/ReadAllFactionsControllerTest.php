<?php

namespace App\Test\Faction\Infrastructure\Http;

use App\Faction\Application\ReadAllFactionsUseCase;
use App\Faction\Domain\FactionRepository;
use App\Faction\Infrastructure\Http\ReadAllFactionsController;
use App\Test\Equipment\Application\MotherObject\ReadAllFactionsUseCaseRequestMotherObject;
use App\Test\Shared\BaseTestCase;
use PDO;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
use Slim\App;

class ReadAllFactionsControllerTest extends BaseTestCase
{
    private App $app;
    private FactionRepository $repository;
    private PDO $pdo;

    protected function setUp(): void
    {
        $this->app = $this->getAppInstanceWithoutCache();
        $this->repository = $this->app->getContainer()->get(FactionRepository::class);
        $this->pdo = $this->app->getContainer()->get(PDO::class);

        $this->pdo->exec('DELETE FROM factions');
    }

    #[Test]
    #[Group('happy-path')]
    #[Group('acceptance')]
    #[Group('readAllFactions')]
    public function givenARepositoryWithMultipleFactionsWhenReadAllFactionsThenReturnFactionsAsJson(): void
    {
        // Arrange
        $factions = ReadAllFactionsUseCaseRequestMotherObject::withMultipleFactions();

        // Guardar las facciones en el repositorio
        foreach ($factions as $faction) {
            $this->repository->save($faction);
        }

        // Act
        $request = $this->createRequest('GET', '/factions');
        $response = $this->app->handle($request);

        // Assert
        $this->assertEquals(200, $response->getStatusCode());

        $payload = json_decode((string) $response->getBody(), true);

        $this->assertArrayHasKey('factions', $payload);
    }

    #[Test]
    #[Group('unhappy-path')]
    #[Group('unit')]
    #[Group('readAllFactions')]
    public function givenUseCaseThrowsExceptionWhenReadAllFactionsThenReturn500(): void
    {
        // Mock del use case que lanza una excepción
        /** @var \App\Faction\Application\ReadAllFactionsUseCase&\PHPUnit\Framework\MockObject\MockObject */
        $mockUseCase = $this->createMock(ReadAllFactionsUseCase::class);
        $mockUseCase->method('execute')->willThrowException(new \RuntimeException('DB down'));

        $controller = new ReadAllFactionsController($mockUseCase);

        $request = $this->createRequest('GET', '/factions');
        $response = $controller($request, new \Slim\Psr7\Response(), []);

        $this->assertEquals(500, $response->getStatusCode());

        $payload = json_decode((string) $response->getBody(), true);
        $this->assertArrayHasKey('error', $payload);
        $this->assertEquals('Failed to list factions', $payload['error']);
        $this->assertStringContainsString('DB down', $payload['message']);
    }
}
