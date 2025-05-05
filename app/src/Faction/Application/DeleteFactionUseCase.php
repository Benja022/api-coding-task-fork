<?php

namespace App\Faction\Application;

use App\Faction\Domain\FactionRepository;
use App\Faction\Infrastructure\Persistence\Pdo\Exception\FactionNotFoundException;

/**
 * DeleteFactionUseCase is a class that is used to delete a faction.
 *
 * @package App\Faction\Application
 */

class DeleteFactionUseCase
{
    public function __construct(
        private FactionRepository $respository
    ) {}

    public function execute(string $id): void
    {
        $faction = $this->respository->find($id);

        if (!$faction) {
            throw FactionNotFoundException::build($id);
        }

        $this->respository->delete($faction);
    }
}
//