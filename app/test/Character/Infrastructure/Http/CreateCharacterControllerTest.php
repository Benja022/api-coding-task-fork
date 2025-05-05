<?php

namespace App\Test\Character\Infrastructure\Http;

use App\Character\Domain\CharacterRepository;
use App\Test\Shared\BaseTestCase;
use PDO;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\After;
use Slim\App;
use Slim\Psr7\Factory\StreamFactory;
use Slim\Psr7\Headers;
use Slim\Psr7\Request as SlimRequest;
use Slim\Psr7\Uri;

class CreateCharacterControllerTest extends BaseTestCase
{
    private App $app;
    private CharacterRepository $repository;
    private PDO $pdo;

    #[Before]
    protected function setUp(): void
    {
        // Load the app instance without cache
        $this->app = $this->getAppInstanceWithoutCache();

        // Get the CharacterRepository from the container
        $this->repository = $this->app->getContainer()->get(CharacterRepository::class);

        // Get the PDO connection from the container
        $this->pdo = $this->app->getContainer()->get(PDO::class);

        // Delete all characters, equipments and factions
        $this->pdo->exec('DELETE FROM characters');
        $this->pdo->exec('DELETE FROM equipments');
        $this->pdo->exec('DELETE FROM factions');

        // Insert equipments and factions to avoid foreign key constraints
        $this->pdo->exec('INSERT INTO equipments (id, name, type, made_by) VALUES (1, "Sword", "weapon", "Blacksmith")');
        $this->pdo->exec('INSERT INTO factions (id, faction_name, description) VALUES (1, "Alliance", "The Alliance faction")');
    }

    #[Test]
    #[Group('happy-path')]
    #[Group('acceptance')]
    #[Group('createCharacter')]
    public function givenAValidCharacterWhenCreateCharacterThenReturn201(): void
    {
        // Create a valid character
        $request = $this->createJsonRequest('POST', '/character', [
            'name' => 'John Doe',
            'birth_date' => '1990-01-01',
            'kingdom' => 'Kingdom of the North',
            'equipment_id' => 1,
            'faction_id' => 1
        ]);

        // Handle the request
        $response = $this->app->handle($request);

        // Assert that the response is a 201
        $this->assertEquals(201, $response->getStatusCode());

        // Get the character ID from the response
        $responseData = json_decode((string) $response->getBody(), true);
        $this->assertArrayHasKey('character', $responseData);
        $this->assertArrayHasKey('id', $responseData['character']); //Get all the data from the character

        $characterId = $responseData['character']['id'];

        // Assert that the character was created
        $character = $this->repository->find($characterId);
        $this->assertNotNull($character);
        $this->assertEquals('John Doe', $character->getName());
        $this->assertEquals('1990-01-01', $character->getBirthDate());
        $this->assertEquals('Kingdom of the North', $character->getKingdom());
        $this->assertEquals(1, $character->getEquipmentId());
        $this->assertEquals(1, $character->getFactionId());
    }


    #[Test]
    #[Group('unhappy-path')]
    #[Group('acceptance')]
    #[Group('createCharacter')]
    public function givenInvalidCharacterMissingNameWhenCreateThenReturn400(): void
    {
        // Create an invalid character
        $request = $this->createJsonRequest('POST', '/character', [
            'name' => '',
            'birth_date' => '1990-01-01',
            'kingdom' => 'Kingdom of the North',
            'equipment_id' => 1,
            'faction_id' => 1
        ]);

        // Handle the request
        $response = $this->app->handle($request);

        // Assert that the response is a 400
        $this->assertEquals(400, $response->getStatusCode());

        $responseData = json_decode((string) $response->getBody(), true);

        $this->assertArrayHasKey('error', $responseData);
        $this->assertStringContainsString('Missing required field', $responseData['error']);
    }

    #[Test]
    #[Group('unhappy-path')]
    #[Group('acceptance')]
    #[Group('createCharacter')]
    public function givenInvalidCharacterWithNonExistentEquipmentWhenCreateThenReturn500(): void
    {
        // Create an invalid character with a non-existent equipment_id
        $request = $this->createJsonRequest('POST', '/character', [
            'name' => 'John Doe',
            'birth_date' => '1990-01-01',
            'kingdom' => 'Kingdom of the North',
            'equipment_id' => 999, // Non-existent equipment ID
            'faction_id' => 1
        ]);

        // Handle the request
        $response = $this->app->handle($request);

        // Assert that the response is a 500
        $this->assertEquals(500, $response->getStatusCode());

        $responseData = json_decode((string) $response->getBody(), true);

        $this->assertArrayHasKey('error', $responseData);
        $this->assertEquals('Error creating character', $responseData['error']);
    }

    #[Test]
    #[Group('unhappy-path')]
    #[Group('integration')]
    #[Group('createCharacter')]
    public function givenInvalidJsonWhenCreateCharacterThenReturn400(): void
    {
        // Create a request with invalid JSON
        $uri = new Uri('', '', 80, '/character');
        $handle = fopen('php://temp', 'w+');
        $stream = (new StreamFactory())->createStreamFromResource($handle);
        $stream->write('{"name": "John Doe", "birth_date": "1990-01-01", "kingdom": "Kingdom of the North", "equipment_id": 1, "faction_id": 1,}'); // Invalid JSON with trailing comma

        $h = new Headers();
        $h->addHeader('Content-Type', 'application/json');

        $request = new SlimRequest('POST', $uri, $h, [], [], $stream);

        // Handle the request
        $response = $this->app->handle($request);

        // Assert that the response is a 400
        $this->assertEquals(400, $response->getStatusCode());

        $responseData = json_decode((string) $response->getBody(), true);

        $this->assertArrayHasKey('error', $responseData);
        $this->assertEquals('Invalid JSON', $responseData['error']);
        $this->assertArrayHasKey('message', $responseData);
    }

    #[After]
    protected function tearDown(): void
    {
        // Delete all characters, equipments and factions
        $this->pdo->exec('DELETE FROM characters');
        $this->pdo->exec('DELETE FROM equipments');
        $this->pdo->exec('DELETE FROM factions');
    }
}
