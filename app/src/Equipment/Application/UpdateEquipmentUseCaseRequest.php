<?php

namespace App\Equipment\Application;

/**
 * UpdateEquipmentUseCaseRequest is a request that updates an equipment.
 *
 * @api
 * @package App\Equipment\Application
 */

class UpdateEquipmentUseCaseRequest
{
    public function __construct(
        private readonly string $name,
        private readonly string $type,
        private readonly string $made_by,
        private readonly int $id
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
        return $this->made_by;
    }

    public function getId(): int
    {
        return $this->id;
    }
}
