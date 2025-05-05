<?php

namespace App\Test\Equipment\Infrastructure\Http;

use App\Equipment\Domain\EquipmentRepository;
use App\Test\Shared\BaseTestCase;
use PDO;
use Slim\App;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\After;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\DataProvider;
use App\Test\Equipment\Application\MotherObject\CreateEquipmentUseCaseRequestMotherObject;
use Slim\Psr7\Factory\StreamFactory;
use Slim\Psr7\Headers;
use Slim\Psr7\Uri;
use Slim\Psr7\Request;

class CreateEquipmentControllerTest extends BaseTestCase
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
    }

    #[Test]
    #[Group('happy-path'), Group('acceptance'), Group('createEquipment')]
    #[DataProvider('payloadProvider')]
    public function givenAValidEquipmentWhenCreateEquipmentThenReturn201(): void
    {
        $request = $this->createJsonRequest('POST', '/equipment', CreateEquipmentUseCaseRequestMotherObject::validAsArray());
        $response = $this->app->handle($request);

        $this->assertEquals(201, $response->getStatusCode());

        $responseData = json_decode((string) $response->getBody(), true);
        $this->assertArrayHasKey('equipment', $responseData);
        $this->assertArrayHasKey('id', $responseData['equipment']);

        $equipmentId = $responseData['equipment']['id'];
        $equipment = $this->equipmentRepository->find($equipmentId);

        $this->assertNotNull($equipment);
        $this->assertEquals('Anduril', $equipment->getName());
        $this->assertEquals('Weapon', $equipment->getType());
        $this->assertEquals('Elfs', $equipment->getMadeBy());
    }

    #[Test]
    #[Group('unhappy-path'), Group('acceptance'), Group('createEquipment')]
    #[DataProvider('payloadProvider')]
    public function givenMissingNameWhenCreateThenReturn400(): void
    {
        $request = $this->createJsonRequest('POST', '/equipment', CreateEquipmentUseCaseRequestMotherObject::missingNameAsArray());
        $response = $this->app->handle($request);
        $this->assertEquals(400, $response->getStatusCode());

        $responseData = json_decode((string) $response->getBody(), true);
        $this->assertArrayHasKey('error', $responseData);
        $this->assertStringContainsString('Name is required', $responseData['error']);
    }

    #[Test]
    #[Group('unhappy-path'), Group('acceptance'), Group('createEquipment')]
    #[DataProvider('payloadProvider')]
    public function givenEmptyNameWhenCreateThenReturn400(): void
    {
        $request = $this->createJsonRequest('POST', '/equipment', CreateEquipmentUseCaseRequestMotherObject::withInvalidNameAsArray());
        $response = $this->app->handle($request);
        $this->assertEquals(400, $response->getStatusCode());

        $responseData = json_decode((string) $response->getBody(), true);
        $this->assertArrayHasKey('error', $responseData);
        $this->assertEquals('Name is required', $responseData['error']);
    }

    #[Test]
    #[Group('unhappy-path'), Group('integration'), Group('createEquipment')]
    #[DataProvider('payloadProvider')]
    public function givenInvalidJsonWhenCreateThenReturn400(): void
    {
        $uri = new Uri('', '', 80, '/equipment');
        $handle = fopen('php://temp', 'w+');
        $stream = (new StreamFactory())->createStreamFromResource($handle);
        $stream->write('{"name": "Anduril", "type": "Weapon", "made_by": "Elfs",}'); // JSON malformado
        $stream->rewind();

        $headers = new Headers();
        $headers->addHeader('Content-Type', 'application/json');

        $request = new Request('POST', $uri, $headers, [], [], $stream);
        $response = $this->app->handle($request);

        $this->assertEquals(400, $response->getStatusCode());

        $responseData = json_decode((string) $response->getBody(), true);
        $this->assertEquals('Invalid JSON', $responseData['error']);
        $this->assertArrayHasKey('message', $responseData);
    }

    #[Test]
    #[Group('unhappy-path'), Group('integration'), Group('createEquipment')]
    public function givenUseCaseThrowsUnexpectedExceptionWhenCreateThenReturn500(): void
    {
        // Creamos un mock del use case que lanza una excepción
        $mockUseCase = $this->createMock(\App\Equipment\Application\CreateEquipmentUseCase::class);
        $mockUseCase->method('execute')
            ->willThrowException(new \Exception('Boom! Something went wrong'));

        // Reemplazamos el controlador en la app manualmente
        /** @var \DI\Container $container */
        $container = $this->app->getContainer();
        $container->set(
            \App\Equipment\Application\CreateEquipmentUseCase::class,
            fn() => $mockUseCase
        );

        // Petición válida, pero el use case fallará
        $request = $this->createJsonRequest('POST', '/equipment', CreateEquipmentUseCaseRequestMotherObject::validAsArray());
        $response = $this->app->handle($request);

        $this->assertEquals(500, $response->getStatusCode());

        $data = json_decode((string) $response->getBody(), true);
        $this->assertEquals('Unexpected error', $data['error']);
        $this->assertEquals('Boom! Something went wrong', $data['message']);
    }


    public static function payloadProvider(): array
    {
        return [
            'Valid equipment' => [CreateEquipmentUseCaseRequestMotherObject::validAsArray()],
            'Invalid name' => [CreateEquipmentUseCaseRequestMotherObject::withInvalidNameAsArray()],
            'Invalid type' => [CreateEquipmentUseCaseRequestMotherObject::withInvalidType()],
            'Invalid made by' => [CreateEquipmentUseCaseRequestMotherObject::withInvalidMadeBy()],
            'Missing name' => [CreateEquipmentUseCaseRequestMotherObject::missingNameAsArray()],
        ];
    }

    #[After]
    protected function tearDown(): void
    {
        $this->pdo->exec('DELETE FROM equipments');
    }
}
