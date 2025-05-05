<?php

namespace App\Character\Infrastructure\Persistence\Pdo;

use App\Character\Domain\Character;
use App\Character\Domain\CharacterRepository;
use App\Character\Infrastructure\Persistence\Pdo\MySQLCharacterToArrayTransformer;
use App\Character\Infrastructure\Persistence\Pdo\Exception\CharacterNotFoundException;
use App\Shared\Infrastructure\Pdo\Exception\RowInsertionFailedException as ExceptionRowInsertionFailedException;
use PDO;

/**
 * MySQLCharacterRepository is a repository that manages characters in MySQL.
 *
 * @api
 * @package App\Character\Infrastructure\Persistence\Pdo
 */
class MySQLCharacterRepository implements CharacterRepository
{
    public function __construct(private PDO $pdo) {}


    public function find(int $id): ?Character
    {
        $stmt = $this->pdo->prepare('SELECT * FROM characters WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $data = $stmt->fetch();

        if (!$data) {
            throw CharacterNotFoundException::build();
        }

        return MySQLCharacterFactory::buildFromArray($data);
    }

    public function findAll(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM characters');
        $characters = [];

        while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $characters[] = MySQLCharacterFactory::buildFromArray($data);
        }

        return $characters;
    }

    public function save(Character $character): Character
    {
        if ($character->getId() !== null) {
            return $this->update($character);
        }

        return $this->insert($character);
    }

    private function insert(Character $character): Character
    {
        $sql = 'INSERT INTO characters (name, birth_date, kingdom, equipment_id, faction_id) VALUES (:name, :birth_date, :kingdom, :equipment_id, :faction_id)';

        $stmt = $this->pdo->prepare($sql);
        $result = $stmt->execute(MySQLCharacterToArrayTransformer::transform($character));

        if (!$result) {
            throw ExceptionRowInsertionFailedException::build();
        }

        return MySQLCharacterFactory::buildFromArray([
            'name' => $character->getName(),
            'birth_date' => $character->getBirthDate(),
            'kingdom' => $character->getKingdom(),
            'equipment_id' => $character->getEquipmentId(),
            'faction_id' => $character->getFactionId(),
            'id' => (int) $this->pdo->lastInsertId()
        ]);
    }

    public function update(Character $character): Character
    {
        $sql = 'UPDATE characters
            SET name = :name,
                birth_date = :birth_date,
                kingdom = :kingdom,
                equipment_id = :equipment_id,
                faction_id = :faction_id
            WHERE id = :id';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'id' => $character->getId(),
            'name' => $character->getName(),
            'birth_date' => $character->getBirthDate(),
            'kingdom' => $character->getKingdom(),
            'equipment_id' => $character->getEquipmentId(),
            'faction_id' => $character->getFactionId(),
        ]);

        return $character;
    }

    public function delete(Character $character): bool
    {
        if (null === $character->getId()) {
            return false;
        }

        $stmt = $this->pdo->prepare('DELETE FROM characters WHERE id = :id');
        return $stmt->execute(['id' => $character->getId()]);
    }
}
