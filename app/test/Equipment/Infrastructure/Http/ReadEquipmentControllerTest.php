<?php

namespace App\Test\Equipment\Infrastructure\Http;

// use PHPUnit\Framework\Attributes\After;

use App\Equipment\Application\ReadEquipmentUseCase;
use App\Equipment\Domain\EquipmentRepository;
use App\Equipment\Domain\EquipmentToArrayTransformer;
use App\Equipment\Infrastructure\Http\ReadEquipmentController;
use App\Test\Equipment\Application\MotherObject\ReadEquipmentUseCaseRequestMotherObject;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
// use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestWith;
use App\Test\Shared\BaseTestCase;
use PDO;
use Slim\App;
use Slim\Psr7\Response;

class ReadEquipmentControllerTest extends BaseTestCase
{
    private App $app;
    private EquipmentRepository $equipmentRepository;
    private PDO $pdo;

    #[Before]
    public function setUp(): void
    {
        $this->app = $this->getAppInstanceWithoutCache();
        $this->equipmentRepository = $this->app->getContainer()->get(EquipmentRepository::class);
        $this->pdo = $this->app->getContainer()->get(PDO::class);

        // Limpiar datos antes de cada test
        $this->pdo->exec('DELETE FROM equipments');
    }

    #[Test]
    #[Group('happy-path'), Group('acceptance'), Group('readEquipment')]
    //#[DataProvider('provideEquipment', ['valid'])]
    public function givenARepositoryWithOneEquipmentIdWhenReadEquipmentThenReturnEquipmentAsJson(): void
    {
        // Arrange
        $expectedEquipment = ReadEquipmentUseCaseRequestMotherObject::valid();
        $expectedEquipment = $this->equipmentRepository->save($expectedEquipment);

        // Act
        $request = $this->createRequest('GET', '/equipment/' . $expectedEquipment->getId());
        $response = $this->app->handle($request);

        // Assert
        $payload = (string) $response->getBody();
        $serializedPayload = json_encode([
            'equipment' => EquipmentToArrayTransformer::transform($expectedEquipment),
        ]);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals($serializedPayload, $payload);
    }

    #[Test]
    #[Group('unhappy-path'), Group('acceptance'), Group('readEquipment')]
    //#[DataProvider('provideEquipment', ['invalid'])]
    #[TestWith(['invalid'])]
    public function givenARepositoryWithNonExistingEquipmentIdWhenReadEquipmentThenReturn404(): void
    {
        // Act
        $request = $this->createRequest('GET', '/equipment/999999');
        $response = $this->app->handle($request);

        // Assert
        $this->assertEquals(404, $response->getStatusCode());

        $payload = json_decode((string) $response->getBody(), true);
        $this->assertArrayHasKey('error', $payload);
        $this->assertEquals('Equipment not found', $payload['error']);
    }

    #[Test]
    #[Group('unhappy-path'), Group('acceptance'), Group('readEquipment')]
    //#[DataProvider('provideEquipment', ['exception'])]
    public function givenUseCaseThrowsUnexpectedExceptionWhenReadEquipmentThenReturn500(): void
    {
        // Arrange: mock del caso de uso que lanza excepción
        /** @var ReadEquipmentUseCase&\PHPUnit\Framework\MockObject\MockObject $mockUseCase */
        $mockUseCase = $this->createMock(ReadEquipmentUseCase::class);
        $mockUseCase->method('execute')->willThrowException(new \RuntimeException('Unexpected failure'));

        // Instanciar el controlador con el mock
        $controller = new ReadEquipmentController($mockUseCase);

        // Crear request y response
        $request = $this->createRequest('GET', '/equipment/999');
        $response = new Response();

        // Act: ejecutar el controlador con un id ficticio
        $result = $controller($request, $response, ['id' => 999]);

        // Assert: comprobar que devuelve 500 y el mensaje esperado
        $this->assertEquals(500, $result->getStatusCode());

        $payload = json_decode((string) $result->getBody(), true);
        $this->assertArrayHasKey('error', $payload);
        $this->assertEquals('Failed to read equipment', $payload['error']);
        $this->assertArrayHasKey('message', $payload);
        $this->assertStringContainsString('Unexpected failure', $payload['message']);
    }

    // TODO: implement separate providers for each test. Atually, every test run with all cases of the provider.
    // public static function provideEquipment(): array
    // {
    //     return [
    //         'valid' => [ReadEquipmentUseCaseRequestMotherObject::valid()],
    //         'invalid' => [ReadEquipmentUseCaseRequestMotherObject::withInvalidId()],
    //         'exception' => [ReadEquipmentUseCaseRequestMotherObject::withInvalidId()],
    //     ];
    // }
}
