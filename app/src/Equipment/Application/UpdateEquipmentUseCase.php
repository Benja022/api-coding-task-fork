<?php

namespace App\Equipment\Application;

use App\Equipment\Domain\Equipment;
use App\Equipment\Domain\EquipmentRepository;
use App\Equipment\Infrastructure\Persistence\Pdo\Exception\EquipmentNotFoundException;

/**
 * This use case is used to update an equipment
 *
 */

class UpdateEquipmentUseCase
{
    public function __construct(
        private EquipmentRepository $repository
    ) {}

    public function execute(
        UpdateEquipmentUseCaseRequest $request
    ): Equipment {
        $oldEquipment = $this->repository->find($request->getId());

        if (!$oldEquipment) {
            throw new EquipmentNotFoundException('Equipment not found');
        }

        $updatedEquipment = new Equipment(
            name: $request->getName(),
            type: $request->getType(),
            made_by: $request->getMadeBy(),
            id: $oldEquipment->getId()
        );

        return $this->repository->save($updatedEquipment);
    }
}
