<?php

namespace App\Equipment\Application;

use App\Equipment\Domain\Equipment;
use App\Equipment\Domain\EquipmentRepository;
use App\Equipment\Infrastructure\Persistence\Pdo\Exception\EquipmentNotFoundException;

/**
 * ReadEquipmentUseCase is a class that is used to read an equipment.
 */

class ReadEquipmentUseCase
{

    public function __construct(
        private EquipmentRepository $repository
    ) {}

    public function execute(int $id): Equipment
    {
        $equipment = $this->repository->find($id);

        if (!$equipment) {
            throw EquipmentNotFoundException::build($id);
        }

        return $equipment;
    }
}
