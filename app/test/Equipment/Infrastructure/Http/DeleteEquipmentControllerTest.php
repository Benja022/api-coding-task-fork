<?php

namespace App\Test\Equipment\Infrastructure\Http;

use App\Equipment\Application\DeleteEquipmentUseCase;
use App\Equipment\Domain\EquipmentRepository;
use App\Equipment\Infrastructure\Http\DeleteEquipmentController;
use App\Equipment\Infrastructure\Persistence\InMemory\ArrayEquipmentRepository;
use App\Test\Equipment\Application\MotherObject\DeleteEquipmentUseCaseRequestMotherObject;
use App\Test\Shared\BaseTestCase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;

class DeleteEquipmentControllerTest extends BaseTestCase
{
    private EquipmentRepository $repository;
    private DeleteEquipmentController $controller;

    protected function setUp(): void
    {
        $this->repository = new ArrayEquipmentRepository();
        $useCase = new DeleteEquipmentUseCase($this->repository);
        $this->controller = new DeleteEquipmentController($useCase);
    }



    #[Test]
    #[Group('integration')]
    #[Group('deleteEquipment')]
    public function givenAValidEquipmentIdWhenDeleteEquipmentThenReturn200()
    {
        $equipment = DeleteEquipmentUseCaseRequestMotherObject::valid();
        $this->repository->save($equipment);

        $id = (string) $equipment->getId();

        $request = $this->createRequest('DELETE', '/equipments/' . $id);
        //var_dump($equipment->getId(), gettype($equipment->getId()));
        //die(); // detiene la ejecución aquí para ver el valor exacto

        $response = $this->controller->__invoke($request, new \Slim\Psr7\Response(), ['id' => $id]);

        // Assert
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/json', $response->getHeaderLine('Content-Type'));

        $responseBody = json_decode((string) $response->getBody(), true);
        $this->assertTrue($responseBody['success']);
    }

    #[Test]
    #[Group('integration')]
    #[Group('deleteEquipment')]
    public function givenANonExistingEquipmentIdWhenDeleteEquipmentThenReturn404()
    {
        $id = '999999';
        $request = $this->createRequest('DELETE', '/equipments/' . $id);
        $response = $this->controller->__invoke($request, new \Slim\Psr7\Response(), ['id' => $id]);

        // Assert
        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEquals('application/json', $response->getHeaderLine('Content-Type'));

        $responseBody = json_decode((string) $response->getBody(), true);
        $this->assertFalse($responseBody['success']);
        $this->assertEquals('Equipment not found', $responseBody['message']);
    }

    #[Test]
    #[Group('integration')]
    #[Group('deleteEquipment')]
    public function givenAnErrorWhenDeleteEquipmentThenReturn500()
    {
        // Crear un mock del caso de uso que lance una excepción general
        /** @var DeleteEquipmentUseCase&\PHPUnit\Framework\MockObject\MockObject $mockUseCase */
        $mockUseCase = $this->createMock(DeleteEquipmentUseCase::class);
        $mockUseCase->method('execute')
            ->willThrowException(new \RuntimeException('Error general'));

        $controller = new DeleteEquipmentController($mockUseCase);

        $id = '1';
        $request = $this->createRequest('DELETE', '/equipments/' . $id);
        $response = $controller->__invoke($request, new \Slim\Psr7\Response(), ['id' => $id]);

        // Assert
        $this->assertEquals(500, $response->getStatusCode());
        $this->assertEquals('application/json', $response->getHeaderLine('Content-Type'));

        $responseBody = json_decode((string) $response->getBody(), true);
        $this->assertFalse($responseBody['success']);
        $this->assertEquals('Failed to delete equipment', $responseBody['message']);
    }
}
