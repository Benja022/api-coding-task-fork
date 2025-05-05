<?php

namespace App\Faction\Infrastructure\Persistence\InMemory;

use App\Faction\Domain\Faction;
use App\Faction\Domain\FactionFactory;
use App\Faction\Domain\FactionRepository;
use App\Faction\Infrastructure\Persistence\Pdo\Exception\FactionNotFoundException;


/**
 * ArrayFactionRepository is a class that is used to store factions in an array.
 *
 * @package App\Faction\Infrastructure\Persistence\InMemory
 */

class ArrayFactionRepository implements FactionRepository
{
    public function __construct(
        private array $factions = []
    ) {}

    public function find(int $id): ?Faction
    {
        if (!isset($this->factions[$id])) {
            throw FactionNotFoundException::build();
        }
        return $this->factions[$id];
    }

    public function save(Faction $faction): Faction
    {
        if (null !== $faction->getId()) {
            $this->factions[$faction->getId()] = $faction;
            return $faction;
        }

        $newId = count($this->factions) + 1;
        $faction = FactionFactory::build(
            $faction->getFactionName(),
            $faction->getDescription(),
            $newId
        );

        $this->factions[$newId] = $faction;
        return $faction;
    }

    public function findAll(): array
    {
        return $this->factions;
    }

    public function delete(Faction $faction): bool
    {
        unset($this->factions[$faction->getId()]);
        return true;
    }
}
