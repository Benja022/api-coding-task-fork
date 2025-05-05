<?php

namespace App\Equipment\Application;

use App\Equipment\Domain\EquipmentRepository;

/**
 * ReadAllEquipmentsUseCase is a use case that reads all equipments.
 *
 * @api
 * @package App\Equipment\Application
 */

class ReadAllEquipmentsUseCase
{
    public function __construct(
        private EquipmentRepository $repository
    ) {}

    public function execute(): array
    {
        return $this->repository->findAll();
    }
}
