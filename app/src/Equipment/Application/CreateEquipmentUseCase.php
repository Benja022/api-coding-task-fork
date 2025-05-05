<?php

namespace App\Equipment\Application;

use App\Equipment\Domain\Equipment;
use App\Equipment\Domain\EquipmentFactory;
use App\Equipment\Domain\EquipmentRepository;

/**
 * CreateEquipmentUseCase is a use case that creates an equipment.
 *
 * @api
 * @package App\Equipment\Application
 */
class CreateEquipmentUseCase
{

    public function __construct(
        private EquipmentRepository $repository
    ) {}

    public function execute(
        CreateEquipmentUseCaseRequest $request
    ): Equipment {
        $equipment = EquipmentFactory::build(
            $request->getName(),
            $request->getType(),
            $request->getMadeBy()
        );

        return $this->repository->save($equipment);
    }
}
