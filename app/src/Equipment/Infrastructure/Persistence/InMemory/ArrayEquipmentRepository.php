<?php

namespace App\Equipment\Infrastructure\Persistence\InMemory;

use App\Equipment\Domain\Equipment;
use App\Equipment\Domain\EquipmentFactory;
use App\Equipment\Domain\EquipmentRepository;
use App\Equipment\Infrastructure\Persistence\Pdo\Exception\EquipmentNotFoundException;

/**
 * ArrayEquipmentRepository is a class that is used to store equipment in an array.
 *
 * @package App\Equipment\Infrastructure\Persistence\InMemory
 */

class ArrayEquipmentRepository implements EquipmentRepository
{

    public function __construct(
        private array $equipment = []
    ) {}

    public function find(int $id): ?Equipment
    {
        if (!isset($this->equipment[$id])) {
            throw EquipmentNotFoundException::build($id);
        }

        return $this->equipment[$id];
    }

    public function save(Equipment $equipment): Equipment
    {
        if (null !== $equipment->getId()) {
            $this->equipment[$equipment->getId()] = $equipment;
            return $equipment;
        }

        $newId = count($this->equipment) + 1;
        $equipment = EquipmentFactory::build(
            $equipment->getName(),
            $equipment->getType(),
            $equipment->getMadeBy(),
            $newId
        );

        $this->equipment[$newId] = $equipment;
        return $equipment;
    }

    public function findAll(): array
    {
        return $this->equipment;
    }

    public function delete(Equipment $equipment): bool
    {
        if (null === $equipment->getId()) {
            return false;
        }

        unset($this->equipment[$equipment->getId()]);
        return true;
    }
}
