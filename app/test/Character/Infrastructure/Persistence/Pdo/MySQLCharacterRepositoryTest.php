<?php

namespace App\Test\Character\Infrastructure\Persistence\Pdo;

use App\Character\Domain\Character;
use App\Character\Infrastructure\Persistence\Pdo\Exception\CharacterNotFoundException;
use App\Character\Infrastructure\Persistence\Pdo\MySQLCharacterRepository;
use PHPUnit\Framework\TestCase;
use PDO;

class MySQLCharacterRepositoryTest extends TestCase
{
    private $repository;

    /*
     * This method is called before each test, call de parent setUp method
     * create a new instace of the repository to ensure that the database is *clean before each test
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new MySQLCharacterRepository($this->createPdoConnection());
        $this->cleanDatabase();
    }

    /*
     * This method is called after each test, call de parent tearDown method
     * clean the database after each test.
     */
    protected function tearDown(): void
    {
        $this->cleanDatabase();
        parent::tearDown();
    }

    /*
     * This method is a helper method to clean the database,
     * delete all characters from the database, excute a DELETE FROM
     * characters without WHERE clause.
     */
    private function cleanDatabase(): void
    {
        $pdo = $this->createPdoConnection();
        $pdo->exec('DELETE FROM characters');  // First delete the characters
        $pdo->exec('DELETE FROM equipments');  // Then delete the equipments
        $pdo->exec('DELETE FROM factions');    // Finally delete the factions
    }

    /**
     * This test is an integration test because it connects to the database
     * and it is a good example of how to use the repository pattern
     * @test
     * @group integration
     */


    public function givenARepositoryWithOneCharacterIdWhenReadCharacterThenReturnTheCharacter()
    {
        // Arrange create a new repository with a PDO connection
        $repository = new MySQLCharacterRepository(
            $this->createPdoConnection()
        );

        // Create equipment and faction first
        $pdo = $this->createPdoConnection();
        $pdo->exec('INSERT INTO equipments (id, name, type, made_by) VALUES (1, "Sword", "weapon", "Blacksmith")');
        $pdo->exec('INSERT INTO factions (id, faction_name, description) VALUES (1, "Alliance", "The Alliance faction")');

        // Create a new character
        $character = new Character(
            'John Doe',
            '1990-01-01',
            'Kingdom of Doe',
            1,
            1
        );

        $character = $repository->save($character);
        $character = $repository->find($character->getId());

        $this->assertEquals('John Doe', $character->getName());
        $this->assertEquals('1990-01-01', $character->getBirthDate());
        $this->assertEquals('Kingdom of Doe', $character->getKingdom());
        $this->assertEquals(1, $character->getEquipmentId());
        $this->assertEquals(1, $character->getFactionId());
    }

    /**
     * @test
     * @group integration
     * Test that the repository can find all characters
     */
    public function givenARepositoryWithMultipleCharactersWhenFindAllThenReturnAllCharacters()
    {
        // Arrange
        $pdo = $this->createPdoConnection();

        // First create the equipments
        $pdo->exec('INSERT INTO equipments (id, name, type, made_by) VALUES
            (1, "Sword", "weapon", "Blacksmith"),
            (2, "Shield", "armor", "Armorer")');

        // Then create the factions (note: using faction_name instead of name)
        $pdo->exec('INSERT INTO factions (id, faction_name, description) VALUES
            (1, "Alliance", "The Alliance faction"),
            (2, "Horde", "The Horde faction")');

        // Finally create the characters
        $character1 = new Character('John Doe', '1990-01-01', 'Kingdom of Doe', 1, 1);
        $character2 = new Character('Jane Doe', '1992-02-02', 'Kingdom of Jane', 2, 2);

        $character1 = $this->repository->save($character1);
        $character2 = $this->repository->save($character2);

        // Act
        $characters = $this->repository->findAll();

        // Assert
        $this->assertCount(2, $characters);

        // Verify first character
        $this->assertEquals($character1->getId(), $characters[0]->getId());
        $this->assertEquals('John Doe', $characters[0]->getName());
        $this->assertEquals('1990-01-01', $characters[0]->getBirthDate());
        $this->assertEquals('Kingdom of Doe', $characters[0]->getKingdom());
        $this->assertEquals(1, $characters[0]->getEquipmentId());
        $this->assertEquals(1, $characters[0]->getFactionId());

        // Verify second character
        $this->assertEquals($character2->getId(), $characters[1]->getId());
        $this->assertEquals('Jane Doe', $characters[1]->getName());
        $this->assertEquals('1992-02-02', $characters[1]->getBirthDate());
        $this->assertEquals('Kingdom of Jane', $characters[1]->getKingdom());
        $this->assertEquals(2, $characters[1]->getEquipmentId());
        $this->assertEquals(2, $characters[1]->getFactionId());
    }

    /**
     * @test
     * @group integration
     * Test that the repository can delete a character
     */
    public function givenARepositoryWithCharacterWhenDeleteThenCharacterIsDeleted()
    {
        // Arrange
        $pdo = $this->createPdoConnection();

        // Create equipment and faction first
        $pdo->exec('INSERT INTO equipments (id, name, type, made_by) VALUES (1, "Sword", "weapon", "Blacksmith")');
        $pdo->exec('INSERT INTO factions (id, faction_name, description) VALUES (1, "Alliance", "The Alliance faction")');

        $character = new Character('John Doe', '1990-01-01', 'Kingdom of Doe', 1, 1);
        $character = $this->repository->save($character);

        // Verify character exists before deletion
        $this->assertNotNull($this->repository->find($character->getId()));

        // Act
        $result = $this->repository->delete($character);

        // Assert
        $this->assertTrue($result);
        $this->expectException(CharacterNotFoundException::class);
        $this->repository->find($character->getId());
    }

    /**
     * @test
     * @group integration
     * Test that the repository throws an exception when trying to find a non-existent character
     */
    public function givenARepositoryWhenFindNonExistentCharacterThenThrowException()
    {
        $this->expectException(CharacterNotFoundException::class);
        $this->repository->find(999999);
    }

    /**
     * @test
     * @group integration
     * Test that the repository can update a character
     */
    public function givenARepositoryWithCharacterWhenUpdateThenCharacterIsUpdated()
    {
        // Arrange
        $pdo = $this->createPdoConnection();

        // Create equipment and faction first
        $pdo->exec('INSERT INTO equipments (id, name, type, made_by) VALUES (1, "Sword", "weapon", "Blacksmith")');
        $pdo->exec('INSERT INTO factions (id, faction_name, description) VALUES (1, "Alliance", "The Alliance faction")');

        $character = new Character('John Doe', '1990-01-01', 'Kingdom of Doe', 1, 1);
        $character = $this->repository->save($character);

        // Act
        $updatedCharacter = new Character(
            'John Updated',
            '1995-05-05',
            'New Kingdom',
            1,
            1,
            $character->getId()
        );
        $updatedCharacter = $this->repository->save($updatedCharacter);

        // Assert
        $foundCharacter = $this->repository->find($character->getId());
        $this->assertEquals($character->getId(), $foundCharacter->getId());
        $this->assertEquals('John Updated', $foundCharacter->getName());
        $this->assertEquals('1995-05-05', $foundCharacter->getBirthDate());
        $this->assertEquals('New Kingdom', $foundCharacter->getKingdom());
        $this->assertEquals(1, $foundCharacter->getEquipmentId());
        $this->assertEquals(1, $foundCharacter->getFactionId());
    }

    /**
     * @test
     * @group integration
     * Test that verifies the MySQLCharacterToArrayTransformer behavior through the repository
     */
    public function givenACharacterWhenSaveThenTransformToArrayCorrectly(): void
    {
        // Arrange
        $pdo = $this->createPdoConnection();

        // Create equipment and faction first
        $pdo->exec('INSERT INTO equipments (id, name, type, made_by) VALUES (1, "Sword", "weapon", "Blacksmith")');
        $pdo->exec('INSERT INTO factions (id, faction_name, description) VALUES (1, "Alliance", "The Alliance faction")');

        // Create a character without ID
        $character = new Character(
            'John Doe',
            '1990-01-01',
            'Kingdom of Doe',
            1,
            1
        );

        // Act
        $savedCharacter = $this->repository->save($character);

        // Assert
        // Verify that the character was saved with all fields
        $stmt = $pdo->prepare('SELECT * FROM characters WHERE id = :id');
        $stmt->execute(['id' => $savedCharacter->getId()]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assertEquals('John Doe', $data['name']);
        $this->assertEquals('1990-01-01', $data['birth_date']);
        $this->assertEquals('Kingdom of Doe', $data['kingdom']);
        $this->assertEquals(1, $data['equipment_id']);
        $this->assertEquals(1, $data['faction_id']);
        $this->assertNotNull($data['id']);

        // Now test with a character that has an ID
        $characterWithId = new Character(
            'Jane Doe',
            '1992-02-02',
            'Kingdom of Jane',
            1,
            1,
            $savedCharacter->getId() // Use the same ID to test update
        );

        // Act
        $updatedCharacter = $this->repository->save($characterWithId);

        // Assert
        $stmt->execute(['id' => $updatedCharacter->getId()]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assertEquals('Jane Doe', $data['name']);
        $this->assertEquals('1992-02-02', $data['birth_date']);
        $this->assertEquals('Kingdom of Jane', $data['kingdom']);
        $this->assertEquals(1, $data['equipment_id']);
        $this->assertEquals(1, $data['faction_id']);
        $this->assertEquals($savedCharacter->getId(), $data['id']);
    }

    // This is a helper method to create a PDO connection
    private function createPdoConnection(): PDO
    {
        return new PDO('mysql:host=db;dbname=test', 'root', 'root');
    }
}
