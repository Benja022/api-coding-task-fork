<?php

namespace App\Test\Faction\Infrastructure\Http;

use App\Faction\Application\CreateFactionUseCase;
use App\Faction\Domain\Exception\FactionValidationException;
use App\Faction\Domain\FactionRepository;
use App\Test\Faction\Application\MotherObject\CreateFactionUseCaseRequestMotherObject;
use App\Test\Shared\BaseTestCase;
use Slim\App;
use PHPUnit\Framework\Attributes\Before;
use PDO;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;

class CreateFactionControllerTest extends BaseTestCase
{
    private App $app;
    private FactionRepository $factionRepository;
    private PDO $pdo;

    #[Before]
    public function setUp(): void
    {
        $this->app = $this->getAppInstanceWithoutCache();
        $this->factionRepository = $this->app->getContainer()->get(FactionRepository::class);
        $this->pdo = $this->app->getContainer()->get(PDO::class);
    }

    #[Test]
    #[Group('happy-path'), Group('acceptance'), Group('createFaction')]
    //#[DataProvider('payloadProvider')]
    public function givenAValidFactionWhenCreateFactionThenReturn201(): void
    {
        $request = $this->createJsonRequest('POST', '/faction', CreateFactionUseCaseRequestMotherObject::validAsArray());
        $response = $this->app->handle($request);

        $this->assertEquals(201, $response->getStatusCode());

        $responseData = json_decode((string) $response->getBody(), true);
        $this->assertArrayHasKey('message', $responseData);
        $this->assertEquals('Faction created successfully', $responseData['message']);
    }

    #[Test]
    #[Group('unhappy-path'), Group('acceptance'), Group('createFaction')]
    //#[DataProvider('payloadProvider')]
    public function givenMissingNameWhenCreateThenReturn400(): void
    {
        $request = $this->createJsonRequest('POST', '/faction', CreateFactionUseCaseRequestMotherObject::missingNameAsArray());
        $response = $this->app->handle($request);
        $this->assertEquals(400, $response->getStatusCode());

        $responseData = json_decode((string) $response->getBody(), true);
        $this->assertArrayHasKey('error', $responseData);
        $this->assertEquals('Faction faction_name is required', $responseData['error']);
    }

    #[Test]
    #[Group('unhappy-path'), Group('acceptance'), Group('createFaction')]
    public function givenEmptyNameWhenCreateThenReturn400(): void
    {
        $request = $this->createJsonRequest('POST', '/faction', CreateFactionUseCaseRequestMotherObject::emptyNameAsArray());
        $response = $this->app->handle($request);
        $this->assertEquals(400, $response->getStatusCode());

        $responseData = json_decode((string) $response->getBody(), true);
        $this->assertArrayHasKey('error', $responseData);
        $this->assertEquals('Faction description is required', $responseData['error']);
    }

    #[Test]
    #[Group('unhappy-path'), Group('acceptance'), Group('createFaction')]
    public function givenInvalidJsonWhenCreateThenReturn400(): void
    {
        $request = $this->createJsonRequest('POST', '/faction', CreateFactionUseCaseRequestMotherObject::invalidJsonAsArray());
        $response = $this->app->handle($request);
        $this->assertEquals(400, $response->getStatusCode());

        $responseData = json_decode((string) $response->getBody(), true);
        $this->assertArrayHasKey('error', $responseData);
        $this->assertEquals('Faction faction_name is required', $responseData['error']);
    }

    #[Test]
    #[Group('unhappy-path'), Group('acceptance'), Group('createFaction')]
    public function givenUseCaseThrowsUnexpectedExceptionWhenCreateThenReturn500(): void
    {

        // Crear un mock de la excepción

        $useCase = $this->createMock(CreateFactionUseCase::class);

        //Configurar el mock para lanzar la excepción
        $useCase->expects($this->once())
            ->method('execute')
            ->willThrowException(new \Exception('Error al crear la facción'));

        // Reemplazar el caso de uso en el contenedor
        /** @var \DI\Container $container */
        $container = $this->app->getContainer();
        $container->set(CreateFactionUseCase::class, $useCase);

        // Ejecutar la prueba
        $request = $this->createJsonRequest('POST', '/faction', CreateFactionUseCaseRequestMotherObject::validAsArray());
        $response = $this->app->handle($request);

        // Verificar el código de estado
        $this->assertEquals(500, $response->getStatusCode());

        // Verificar el mensaje de error
        $responseData = json_decode((string) $response->getBody(), true);
        $this->assertArrayHasKey('error', $responseData);
        $this->assertEquals('Failed to create faction', $responseData['error']);
    }
    #[Test]
    #[Group('unhappy-path'), Group('acceptance'), Group('createFaction')]
    public function givenValidationExceptionWhenCreateThenReturn400WithError(): void
    {
        // Crear un mock del caso de uso
        $useCase = $this->createMock(CreateFactionUseCase::class);

        // Configurar el mock para que lance una excepción de validación
        $useCase->expects($this->once())
            ->method('execute')
            ->willThrowException(FactionValidationException::factionNameRequired());

        // Reemplazar el caso de uso en el contenedor
        /** @var \DI\Container $container */
        $container = $this->app->getContainer();
        $container->set(CreateFactionUseCase::class, $useCase);

        // Ejecutar la prueba
        $request = $this->createJsonRequest('POST', '/faction', CreateFactionUseCaseRequestMotherObject::validAsArray());
        $response = $this->app->handle($request);

        // Verificar el código de estado
        $this->assertEquals(400, $response->getStatusCode());

        // Verificar el Content-Type
        $this->assertEquals('application/json', $response->getHeaderLine('Content-Type'));

        // Verificar el mensaje de error
        $responseData = json_decode((string) $response->getBody(), true);
        $this->assertArrayHasKey('error', $responseData);
        $this->assertEquals('Faction name is required', $responseData['error']);
    }
}
