<?php

namespace App\Equipment\Infrastructure\Persistence\Pdo;

use App\Equipment\Domain\Equipment;
use App\Equipment\Domain\EquipmentRepository;
use PDO;
use App\Shared\Infrastructure\Pdo\Exception\RowInsertionFailedException as ExceptionRowInsertionFailedException;
use App\Equipment\Infrastructure\Persistence\Pdo\MySQLEquipmentFactory;

class MySQLEquipmentRepository implements EquipmentRepository
{
    public function __construct(private PDO $pdo) {}

    public function find(int $id): ?Equipment
    {
        $stmt = $this->pdo->prepare('SELECT * FROM equipments WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$data) {
            return null;
        }

        return self::fromArray($data, $this->pdo);
    }

    private function fromArray(array $data): Equipment
    {
        return new Equipment(
            $data['name'],
            $data['type'],
            $data['made_by'],
            $data['id'] ?? null
        );
    }

    public function findAll(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM equipments');
        $equipments = [];

        while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $equipments[] = self::fromArray($data);
        }

        return $equipments;
    }

    public function save(Equipment $equipment): Equipment
    {
        if (!empty($equipment->getId())) {
            return $this->update($equipment);
        }

        return $this->insert($equipment);
    }

    private function insert(Equipment $equipment): Equipment
    {
        $sql = 'INSERT INTO equipments (name, type, made_by) VALUES (:name, :type, :made_by)';

        $stmt = $this->pdo->prepare($sql);
        $result = $stmt->execute(MySQLEquipmentToArrayTransformer::transform($equipment));

        if (!$result) {
            throw ExceptionRowInsertionFailedException::build();
        }

        return MySQLEquipmentFactory::buildFromArray([
            'name' => $equipment->getName(),
            'type' => $equipment->getType(),
            'made_by' => $equipment->getMadeBy(),
            'id' => $this->pdo->lastInsertId()
        ]);
    }


    private function update(Equipment $equipment): Equipment
    {
        $sql = 'UPDATE equipments
            SET name = :name,
            type = :type,
            made_by = :made_by
        WHERE id = :id';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'id' => $equipment->getId(),
            'name' => $equipment->getName(),
            'type' => $equipment->getType(),
            'made_by' => $equipment->getMadeBy(),
        ]);

        return $equipment;
    }

    public function delete(Equipment $equipment): bool
    {
        if (null === $equipment->getId()) {
            return false;
        }

        $stmt = $this->pdo->prepare('DELETE FROM equipments WHERE id = :id');
        return $stmt->execute(['id' => $equipment->getId()]);
    }
}
