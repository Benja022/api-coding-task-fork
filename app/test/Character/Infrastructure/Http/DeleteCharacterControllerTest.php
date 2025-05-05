<?php

namespace App\Test\Character\Infrastructure\Http;

use App\Character\Application\DeleteCharacterUseCase;
use App\Character\Domain\Character;
use App\Character\Domain\CharacterRepository;
use App\Character\Infrastructure\Http\DeleteCharacterController;
use App\Character\Infrastructure\Persistence\InMemory\ArrayCharacterRepository;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\Group;
use App\Test\Shared\BaseTestCase;

class DeleteCharacterControllerTest extends BaseTestCase
{
    private CharacterRepository $repository;
    private DeleteCharacterController $controller;

    protected function setUp(): void
    {
        $this->repository = new ArrayCharacterRepository();
        $useCase = new DeleteCharacterUseCase($this->repository);
        $this->controller = new DeleteCharacterController($useCase);
    }

    #[Test]
    #[TestDox('Given a character id when delete character then return 200')]
    #[Group('integration')]
    #[Group('deleteCharacter')]
    public function givenACharacterIdWhenDeleteCharacterThenReturn200()
    {
        $character = new Character(
            'John Doe',
            '1990-01-01',
            'Kingdom of Spain',
            1,
            1
        );

        $savedCharacter = $this->repository->save($character);

        $request = $this->createRequest('DELETE', '/characters/' . $savedCharacter->getId());
        $response = $this->controller->__invoke($request, new \Slim\Psr7\Response(), ['id' => $savedCharacter->getId()]);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/json', $response->getHeaderLine('Content-Type'));

        $responseBody = json_decode((string) $response->getBody(), true);
        $this->assertTrue($responseBody['success']);
    }

    #[Test]
    #[Group('deleteCharacter')]
    #[TestDox('Given a character id when delete character then return 404')]
    // TestDox es una anotación que permite dar un nombre al test
    public function givenANonExistentCharacterIdWhenDeleteCharacterThenReturn404()
    {
        $request = $this->createRequest('DELETE', '/characters/999');
        $response = $this->controller->__invoke($request, new \Slim\Psr7\Response(), ['id' => 999]);

        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEquals('application/json', $response->getHeaderLine('Content-Type'));

        $responseBody = json_decode((string) $response->getBody(), true);
        $this->assertFalse($responseBody['success']);
        $this->assertEquals('Character not found', $responseBody['message']);
    }
}
