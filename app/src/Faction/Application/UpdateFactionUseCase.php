<?php

namespace App\Faction\Application;

use App\Faction\Domain\Faction;
use App\Faction\Domain\FactionRepository;
use App\Faction\Infrastructure\Persistence\Pdo\Exception\FactionNotFoundException;

/**
 * UpdateFactionUseCase is a class that is used to update a faction.
 *
 * @package App\Faction\Application
 */

class UpdateFactionUseCase
{
    public function __construct(
        private FactionRepository $repository
    ) {}

    public function execute(
        UpdateFactionUseCaseRequest $request
    ): Faction {
        $oldFaction = $this->repository->find($request->getId());

        if (!$oldFaction) {
            throw FactionNotFoundException::build();
        }

        $updatedFaction = new Faction(
            faction_name: $request->getFactionName(),
            description: $request->getDescription(),
            id: $oldFaction->getId()
        );

        return $this->repository->save($updatedFaction);
    }
}
