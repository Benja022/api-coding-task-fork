<?php

namespace App\Faction\Application;

use App\Faction\Domain\FactionRepository;

/**
 * ReadAllFactionsUseCase is a class that is used to read all factions.
 *
 * @package App\Faction\Application
 */

class ReadAllFactionsUseCase
{
    public function __construct(
        private FactionRepository $repository
    ) {}

    public function execute(): array
    {
        return $this->repository->findAll();
    }
}
