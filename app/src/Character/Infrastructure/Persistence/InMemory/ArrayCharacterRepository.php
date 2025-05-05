<?php

namespace App\Character\Infrastructure\Persistence\InMemory;

use App\Character\Domain\Character;
use App\Character\Domain\CharacterFactory;
use App\Character\Domain\CharacterRepository;
use App\Character\Infrastructure\Persistence\Pdo\Exception\CharacterNotFoundException;

/**
 * ArrayCharacterRepository is a repository that manages characters in memory.
 *
 * @api
 * @package App\Character\Infrastructure\Persistence\InMemory
 */
class ArrayCharacterRepository implements CharacterRepository
{
    public function __construct(
        private array $characters = []
    ) {}

    public function find(int $id): ?Character
    {
        if (!isset($this->characters[$id])) {
            throw CharacterNotFoundException::build($id);
        }
        return $this->characters[$id];
    }

    public function save(Character $character): Character
    {
        if (null !== $character->getId()) {
            $this->characters[$character->getId()] = $character;
            return $character;
        }

        $newId = count($this->characters) + 1;
        $character = CharacterFactory::build(
            $character->getName(),
            $character->getBirthDate(),
            $character->getKingdom(),
            $character->getEquipmentId(),
            $character->getFactionId(),
            $newId
        );

        $this->characters[$newId] = $character;
        return $character;
    }

    public function findAll(): array
    {
        return $this->characters;
    }

    public function delete(Character $character): bool
    {
        unset($this->characters[$character->getId()]);
        return true;
    }
}
