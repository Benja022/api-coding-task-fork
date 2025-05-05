<?php

namespace App\Test\Equipment\Infrastructure\Http;

use App\Equipment\Application\ReadAllEquipmentsUseCase;
use App\Equipment\Domain\EquipmentRepository;
use App\Test\Shared\BaseTestCase;
use PDO;
use Slim\App;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
use App\Test\Equipment\Application\MotherObject\ReadAllEquipmentsUseCaseRequestMotherObject;
use App\Equipment\Infrastructure\Http\ReadAllEquipmentsController;

class ReadAllEquipmentsControllerTest extends BaseTestCase
{
    private App $app;
    private EquipmentRepository $repository;
    private PDO $pdo;

    protected function setUp(): void
    {
        $this->app = $this->getAppInstanceWithoutCache();
        $this->repository = $this->app->getContainer()->get(EquipmentRepository::class);
        $this->pdo = $this->app->getContainer()->get(PDO::class);

        $this->pdo->exec('DELETE FROM equipments');
    }

    #[Test]
    #[Group('happy-path')]
    #[Group('acceptance')]
    #[Group('readAllEquipments')]
    public function givenARepositoryWithMultipleEquipmentsWhenReadAllEquipmentsThenReturnEquipmentsAsJson(): void
    {

        // Arrange
        $equipments = ReadAllEquipmentsUseCaseRequestMotherObject::withMultipleEquipment();

        // Guardar los equipamentos en el repositorio
        foreach ($equipments as $equipment) {
            $this->repository->save($equipment);
        }

        // Act
        $request = $this->createRequest('GET', '/equipments');
        $response = $this->app->handle($request);

        // Assert
        $this->assertEquals(200, $response->getStatusCode());

        $payload = json_decode((string) $response->getBody(), true);

        $this->assertArrayHasKey('equipments', $payload);
    }

    #[Test]
    #[Group('unhappy-path')]
    #[Group('unit')]
    #[Group('readAllEquipments')]
    public function givenUseCaseThrowsExceptionWhenReadAllEquipmentsThenReturn500(): void
    {
        // Mock del use case que lanza una excepción
        $mockUseCase = $this->createMock(ReadAllEquipmentsUseCase::class);
        $mockUseCase->method('execute')->willThrowException(new \RuntimeException('DB down'));

        // Crear instancia del controller con el mock
        /** @var \App\Equipment\Application\ReadAllEquipmentsUseCase&\PHPUnit\Framework\MockObject\MockObject */
        $mockUseCase = $this->createMock(ReadAllEquipmentsUseCase::class);
        $mockUseCase->method('execute')->willThrowException(new \RuntimeException('DB down'));

        $controller = new ReadAllEquipmentsController($mockUseCase);


        $response = new \Slim\Psr7\Response();
        $request = $this->CreateRequest('GET', '/equipments');

        $response = $controller($request, $response, []);

        $this->assertEquals(500, $response->getStatusCode());

        $payload = json_decode((string) $response->getBody(), true);
        $this->assertArrayHasKey('error', $payload);
        $this->assertEquals('Failed to list equipments', $payload['error']);
        $this->assertStringContainsString('DB down', $payload['message']);
    }
}
