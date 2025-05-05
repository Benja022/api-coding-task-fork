<?php

namespace App\Equipment\Infrastructure\Http;

use App\Equipment\Application\UpdateEquipmentUseCase;
use App\Equipment\Domain\EquipmentRepository;
use App\Equipment\Infrastructure\Persistence\InMemory\ArrayEquipmentRepository;
use App\Test\Equipment\Application\MotherObject\UpdateEquipmentUseCaseRequestMotherObject;
use App\Test\Shared\BaseTestCase;
use Slim\Psr7\Response;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;

class UpdateEquipmentControllerTest extends BaseTestCase
{
    private EquipmentRepository $repository;
    private UpdateEquipmentController $controller;

    protected function setUp(): void
    {
        $this->repository = new ArrayEquipmentRepository();
        $useCase = new UpdateEquipmentUseCase($this->repository);
        $this->controller = new UpdateEquipmentController($useCase);
    }

    #[Test]
    #[Group('unit')]
    #[Group('updateEquipment')]
    public function testSuccesfullUpdateReturns200(): void
    {
        // Crear un equipo existente para actualizar
        $existingEquipment = \App\Equipment\Domain\EquipmentFactory::build(
            'Old Name',
            'Old Type',
            'Old Made By',
            1
        );
        $this->repository->save($existingEquipment);

        // Crear la solicitud de actualización
        $updateData = UpdateEquipmentUseCaseRequestMotherObject::validAsArray();

        $request = $this->createJsonRequest(
            'PUT',
            '/equipment/1',
        );
        $request->getBody()->write(json_encode($updateData));
        $request->getBody()->rewind();

        $response = new Response();

        $result = $this->controller->__invoke($request, $response, ['id' => 1]);

        $this->assertEquals(200, $result->getStatusCode());

        $body = json_decode((string) $result->getBody(), true);
        $this->assertArrayHasKey('equipment', $body);
        $this->assertEquals('Anduril', $body['equipment']['name']);
        $this->assertEquals('Weapon', $body['equipment']['type']);
        $this->assertEquals('Elfs', $body['equipment']['made_by']);
    }

    #[Test]
    #[Group('unit')]
    #[Group('updateEquipment')]
    public function testInvalidJsonReturns400(): void
    {
        $request = $this->createJsonRequest(
            'PUT',
            '/equipment/1',
        );
        // Enviar JSON inválido
        $request->getBody()->write('{"name": "Anduril", "type": "Weapon", "made_by": "Elfs"');
        $request->getBody()->rewind();

        $response = new Response();

        $result = $this->controller->__invoke($request, $response, ['id' => 1]);

        $this->assertEquals(400, $result->getStatusCode());

        $body = json_decode((string) $result->getBody(), true);
        $this->assertArrayHasKey('error', $body);
        $this->assertEquals('Invalid JSON', $body['error']);
    }

    #[Test]
    #[Group('unit')]
    #[Group('updateEquipment')]
    public function testMissingRequiredFieldReturns400(): void
    {
        $request = $this->createJsonRequest(
            'PUT',
            '/equipment/1',
        );
        // Enviar datos sin el campo 'type' que es requerido
        $request->getBody()->write(json_encode([
            'name' => 'Anduril',
            'made_by' => 'Elfs'
        ]));
        $request->getBody()->rewind();

        $response = new Response();

        $result = $this->controller->__invoke($request, $response, ['id' => 1]);

        $this->assertEquals(400, $result->getStatusCode());

        $body = json_decode((string) $result->getBody(), true);
        $this->assertArrayHasKey('error', $body);
        $this->assertEquals('Missing required field: type', $body['error']);
    }

    #[Test]
    #[Group('unit')]
    #[Group('updateEquipment')]
    public function testInternalErrorReturns500(): void
    {
        // Crear un mock del caso de uso que lance una excepción
        /** @var UpdateEquipmentUseCase&\PHPUnit\Framework\MockObject\MockObject $mockUseCase */
        $mockUseCase = $this->createMock(UpdateEquipmentUseCase::class);
        $mockUseCase->method('execute')
            ->willThrowException(new \RuntimeException('Error interno'));

        $controller = new UpdateEquipmentController($mockUseCase);

        $request = $this->createJsonRequest(
            'PUT',
            '/equipment/1',
        );
        $request->getBody()->write(json_encode(UpdateEquipmentUseCaseRequestMotherObject::validAsArray()));
        $request->getBody()->rewind();

        $response = new Response();

        $result = $controller->__invoke($request, $response, ['id' => 1]);

        $this->assertEquals(500, $result->getStatusCode());

        $body = json_decode((string) $result->getBody(), true);
        $this->assertArrayHasKey('error', $body);
        $this->assertEquals('Error updating equipment', $body['error']);
        $this->assertEquals('Error interno', $body['message']);
    }
}
