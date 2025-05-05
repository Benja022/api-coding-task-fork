<?php

namespace App\Test\Faction\Infrastructure\Http;

use App\Faction\Application\DeleteFactionUseCase;
use App\Faction\Domain\FactionRepository;
use App\Faction\Infrastructure\Http\DeleteFactionController;
use App\Test\Faction\Application\MotherObject\DeleteFactionUseCaseRequestMotherObject;
use App\Test\Shared\BaseTestCase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\MockObject;
use App\Faction\Infrastructure\Persistence\Pdo\Exception\FactionNotFoundException;

class DeleteFactionControllerTest extends BaseTestCase
{
    private DeleteFactionController $controller;
    private DeleteFactionUseCase&MockObject $useCase;

    protected function setUp(): void
    {
        $this->useCase = $this->createMock(DeleteFactionUseCase::class);
        $this->controller = new DeleteFactionController($this->useCase);
    }

    #[Test]
    #[Group('integration')]
    #[Group('deleteFaction')]
    public function givenAValidFactionIdWhenDeleteFactionThenReturn200(): void
    {
        $this->useCase->expects($this->once())
            ->method('execute')
            ->with(1);

        $request = $this->createRequest('DELETE', '/factions/1');
        $response = $this->controller->__invoke($request, new \Slim\Psr7\Response(), ['id' => 1]);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/json', $response->getHeaderLine('Content-Type'));

        $responseBody = json_decode((string) $response->getBody(), true);
        $this->assertEquals('Faction deleted successfully', $responseBody['message']);
    }

    #[Test]
    #[Group('integration')]
    #[Group('deleteFaction')]
    public function givenANonExistingFactionIdWhenDeleteFactionThenReturn404(): void
    {
        $this->useCase->expects($this->once())
            ->method('execute')
            ->with(999)
            ->willThrowException(new FactionNotFoundException());

        $request = $this->createRequest('DELETE', '/factions/999');
        $response = $this->controller->__invoke($request, new \Slim\Psr7\Response(), ['id' => 999]);

        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEquals('application/json', $response->getHeaderLine('Content-Type'));

        $responseBody = json_decode((string) $response->getBody(), true);
        $this->assertEquals('Faction not found', $responseBody['message']);
    }

    #[Test]
    #[Group('integration')]
    #[Group('deleteFaction')]
    public function givenAnErrorWhenDeleteFactionThenReturn500(): void
    {
        $this->useCase->expects($this->once())
            ->method('execute')
            ->with(1)
            ->willThrowException(new \Exception('Database error'));

        $request = $this->createRequest('DELETE', '/factions/1');
        $response = $this->controller->__invoke($request, new \Slim\Psr7\Response(), ['id' => 1]);

        $this->assertEquals(500, $response->getStatusCode());
        $this->assertEquals('application/json', $response->getHeaderLine('Content-Type'));

        $responseBody = json_decode((string) $response->getBody(), true);
        $this->assertEquals('Failed to delete faction', $responseBody['message']);
    }
}
