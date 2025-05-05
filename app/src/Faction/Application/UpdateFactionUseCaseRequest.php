<?php

namespace App\Faction\Application;

/**
 * UpdateFactionUseCaseRequest is a class that is used to update a faction.
 *
 * @package App\Faction\Application
 */

class UpdateFactionUseCaseRequest
{
    public function __construct(
        private readonly string $faction_name,
        private readonly string $description,
        private readonly int $id
    ) {}

    public function getFactionName(): string
    {
        return $this->faction_name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getId(): int
    {
        return $this->id;
    }
}
