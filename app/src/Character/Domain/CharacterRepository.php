<?php

namespace App\Character\Domain;

/**
 * CharacterRepository is a repository that manages characters.
 *
 * @api
 * @package App\Character\Domain
 */
interface CharacterRepository
{
    public function findAll(): array;

    public function find(int $id): ?Character;

    public function save(Character $character): Character;

    public function delete(Character $character): bool;
}
