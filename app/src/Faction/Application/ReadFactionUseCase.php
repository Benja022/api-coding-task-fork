<?php

namespace App\Faction\Application;

use App\Faction\Domain\Faction;
use App\Faction\Domain\FactionRepository;

/**
 * ReadFactionUseCase is a class that is used to read a faction.
 *
 * @package App\Faction\Application
 */

class ReadFactionUseCase
{
    public function __construct(
        private FactionRepository $repository
    ) {}

    public function execute(string $id): Faction
    {
        return $this->repository->find($id);
    }
}
