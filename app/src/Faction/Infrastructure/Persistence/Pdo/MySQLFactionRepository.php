<?php

namespace App\Faction\Infrastructure\Persistence\Pdo;

use App\Faction\Domain\Faction;
use App\Faction\Domain\FactionRepository;
use App\Faction\Infrastructure\Persistence\Pdo\Exception\FactionNotFoundException;
use App\Shared\Infrastructure\Pdo\Exception\RowInsertionFailedException;
use PDO;

/**
 * MySQLFactionRepository is a class that is used to store factions in a MySQL database.
 *
 * @package App\Faction\Infrastructure\Persistence\Pdo
 */

class MySQLFactionRepository implements FactionRepository
{
    public function __construct(private PDO $pdo) {}

    public function find(int $id): ?Faction
    {
        $stmt = $this->pdo->prepare('SELECT * FROM factions WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $data = $stmt->fetch();

        if (!$data) {
            throw FactionNotFoundException::build();
        }

        return MySQLFactionFactory::buildFromArray($data);
    }

    public function findAll(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM factions');
        $factions = [];

        while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $factions[] = MySQLFactionFactory::buildFromArray($data);
        }

        return $factions;
    }

    public function save(Faction $faction): Faction
    {
        if ($faction->getId() !== null) {
            return $this->update($faction);
        }

        return $this->insert($faction);
    }

    private function insert(Faction $faction): Faction
    {
        $sql = 'INSERT INTO factions (faction_name, description) VALUES (:faction_name, :description)';

        $stmt = $this->pdo->prepare($sql);
        $result = $stmt->execute(MySQLFactionToArrayTransformer::transform($faction));

        if (!$stmt->rowCount()) {
            throw RowInsertionFailedException::build();
        }

        return MySQLFactionFactory::buildFromArray([
            'faction_name' => $faction->getFactionName(),
            'description' => $faction->getDescription(),
            'id' => $this->pdo->lastInsertId()
        ]);
    }

    public function update(Faction $faction): Faction
    {
        $sql = 'UPDATE factions
            SET faction_name = :faction_name,
                description = :description
            WHERE id = :id';

        $stmt = $this->pdo->prepare($sql);
        $result = $stmt->execute([
            'id' => $faction->getId(),
            'faction_name' => $faction->getFactionName(),
            'description' => $faction->getDescription()
        ]);


        return $faction;
    }

    public function delete(Faction $faction): bool
    {
        if (null === $faction->getId()) {
            return false;
        }

        $stmt = $this->pdo->prepare('DELETE FROM factions WHERE id = :id');
        return $stmt->execute(['id' => $faction->getId()]);
    }
}
