<?php

namespace App\Equipment\Application;

/**
 * This class generate a request with the equipment data.
 */

class CreateEquipmentUseCaseRequest
{
    public function __construct(
        private readonly string $name,
        private readonly string $type,
        private readonly string $madeBy,
    ) {}

    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getMadeBy(): string
    {
        return $this->madeBy;
    }
}
