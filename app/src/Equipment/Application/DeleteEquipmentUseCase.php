<?php

namespace App\Equipment\Application;

use App\Equipment\Domain\EquipmentRepository;
use App\Equipment\Infrastructure\persistence\Pdo\Exception\EquipmentNotFoundException as ExceptionEquipmentNotFoundException;

/**
 * DeleteEquipmentUseCase is a use case that deletes an equipment.
 *
 * @api
 * @package App\Equipment\Application
 */

class DeleteEquipmentUseCase
{
    public function __construct(
        private EquipmentRepository $repository
    ) {}

    public function execute(int $id): void
    {
        $equipment = $this->repository->find($id);

        if (!$equipment) {
            throw ExceptionEquipmentNotFoundException::build($id);
        }

        $this->repository->delete($equipment);
    }
}
