<?php

namespace App\Test\Character\Infrastructure\Http;

use App\Character\Application\ReadAllCharactersUseCase;
use App\Character\Domain\Character;
use App\Character\Domain\CharacterRepository;
use App\Character\Infrastructure\Http\ReadAllCharactersController;
use App\Test\Shared\BaseTestCase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
use PDO;
use Slim\App;
use Slim\Psr7\Factory\StreamFactory;
use Slim\Psr7\Headers;
use Slim\Psr7\Request as SlimRequest;
use Slim\Psr7\Uri;

class ReadAllCharactersControllerTest extends BaseTestCase
{
    private App $app;
    private CharacterRepository $repository;
    private PDO $pdo;

    protected function setUp(): void
    {
        $this->app = $this->getAppInstanceWithoutCache();
        $this->repository = $this->app->getContainer()->get(CharacterRepository::class);
        $this->pdo = $this->app->getContainer()->get(PDO::class);

        // Limpiar datos antes de cada test
        $this->pdo->exec('DELETE FROM characters');
        $this->pdo->exec('DELETE FROM equipments');
        $this->pdo->exec('DELETE FROM factions');

        // Insertar datos necesarios
        $this->pdo->exec('INSERT INTO equipments (id, name, type, made_by) VALUES (1, "Sword", "weapon", "Blacksmith")');
        $this->pdo->exec('INSERT INTO factions (id, faction_name, description) VALUES (1, "Alliance", "The Alliance faction")');
    }

    #[Test]
    #[Group('happy-path')]
    #[Group('acceptance')]
    #[Group('readAllCharacters')]
    public function givenARepositoryWithMultipleCharactersWhenReadAllCharactersThenReturnCharactersAsJson(): void
    {
        // Insertar personajes de prueba
        $character1 = new Character('John Doe', '1990-01-01', 'Kingdom A', 1, 1);
        $character2 = new Character('Jane Smith', '1995-05-05', 'Kingdom B', 1, 1);

        $character1 = $this->repository->save($character1);
        $character2 = $this->repository->save($character2);

        $request = $this->createRequest('GET', '/characters');
        $response = $this->app->handle($request);

        $this->assertEquals(200, $response->getStatusCode());

        $payload = json_decode((string) $response->getBody(), true);

        $this->assertArrayHasKey('characters', $payload);
        $this->assertCount(2, $payload['characters']);
    }

    #[Test]
    #[Group('unhappy-path')]
    #[Group('unit')]
    #[Group('readAllCharacters')]
    public function givenUseCaseThrowsExceptionWhenReadAllCharactersThenReturn500(): void
    {
        // Mock del use case que lanza una excepción
        $mockUseCase = $this->createMock(ReadAllCharactersUseCase::class);
        $mockUseCase->method('execute')->willThrowException(new \RuntimeException('DB down'));

        // Crear instancia del controller con el mock
        /** @var \App\Character\Application\ReadAllCharactersUseCase&\PHPUnit\Framework\MockObject\MockObject */
        $mockUseCase = $this->createMock(ReadAllCharactersUseCase::class);
        $mockUseCase->method('execute')->willThrowException(new \RuntimeException('DB down'));

        $controller = new ReadAllCharactersController($mockUseCase);

        // Crear petición y respuesta simuladas
        $request = new SlimRequest(
            'GET',
            new Uri('', '', 80, '/characters'),
            new Headers(),
            [],
            [],
            (new StreamFactory())->createStream()
        );

        $response = new \Slim\Psr7\Response();

        $response = $controller($request, $response, []);

        $this->assertEquals(500, $response->getStatusCode());

        $payload = json_decode((string) $response->getBody(), true);
        $this->assertArrayHasKey('error', $payload);
        $this->assertEquals('Failed to list characters', $payload['error']);
        $this->assertStringContainsString('DB down', $payload['message']);
    }
}
