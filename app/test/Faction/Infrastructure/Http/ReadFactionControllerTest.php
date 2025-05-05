<?php

namespace App\Test\Faction\Infrastructure\Http;

use App\Faction\Application\ReadFactionUseCase;
use App\Faction\Domain\FactionRepository;
use App\Faction\Infrastructure\Http\ReadFactionController;
use App\Test\Faction\Application\MotherObject\ReadFactionUseCaseRequestMotherObject;
use App\Test\Shared\BaseTestCase;
use PDO;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
use Slim\App;
use PHPUnit\Framework\MockObject\MockObject;
use App\Faction\Infrastructure\Persistence\Pdo\Exception\FactionNotFoundException;

class ReadFactionControllerTest extends BaseTestCase
{
    private App $app;
    private FactionRepository $repository;
    private PDO $pdo;
    private ReadFactionUseCase&MockObject $useCase;
    private ReadFactionController $controller;

    protected function setUp(): void
    {
        $this->useCase = $this->createMock(ReadFactionUseCase::class);
        $this->controller = new ReadFactionController($this->useCase);
    }

    #[Test]
    #[Group('happy-path')]
    #[Group('acceptance')]
    #[Group('readFaction')]
    public function givenARepositoryWithOneFactionIdWhenReadFactionThenReturnFactionAsJson(): void
    {
        // Arrange
        $faction = ReadFactionUseCaseRequestMotherObject::valid();
        $this->useCase->expects($this->once())
            ->method('execute')
            ->with('1')
            ->willReturn($faction);

        $request = $this->createRequest('GET', '/factions/1');
        $response = $this->controller->__invoke($request, new \Slim\Psr7\Response(), ['id' => '1']);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/json', $response->getHeaderLine('Content-Type'));

        $responseBody = json_decode((string) $response->getBody(), true);
        $this->assertArrayHasKey('faction', $responseBody);
    }

    #[Test]
    #[Group('unhappy-path')]
    #[Group('acceptance')]
    #[Group('readFaction')]
    public function givenARepositoryWithNonExistingFactionIdWhenReadFactionThenReturn404(): void
    {
        $this->useCase->expects($this->once())
            ->method('execute')
            ->with('999')
            ->willThrowException(new FactionNotFoundException());

        $request = $this->createRequest('GET', '/factions/999');
        $response = $this->controller->__invoke($request, new \Slim\Psr7\Response(), ['id' => '999']);

        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEquals('application/json', $response->getHeaderLine('Content-Type'));

        $responseBody = json_decode((string) $response->getBody(), true);
        $this->assertEquals('Not Found', $responseBody['message']);
    }

    #[Test]
    #[Group('unhappy-path')]
    #[Group('acceptance')]
    #[Group('readFaction')]
    public function givenUseCaseThrowsExceptionWhenReadFactionThenReturn500(): void
    {
        $this->useCase->expects($this->once())
            ->method('execute')
            ->with('1')
            ->willThrowException(new \Exception('Database error'));

        $request = $this->createRequest('GET', '/factions/1');
        $response = $this->controller->__invoke($request, new \Slim\Psr7\Response(), ['id' => '1']);

        $this->assertEquals(500, $response->getStatusCode());
        $this->assertEquals('application/json', $response->getHeaderLine('Content-Type'));

        $responseBody = json_decode((string) $response->getBody(), true);
        $this->assertEquals('Failed to read faction', $responseBody['error']);
        $this->assertEquals('Database error', $responseBody['message']);
    }
}
