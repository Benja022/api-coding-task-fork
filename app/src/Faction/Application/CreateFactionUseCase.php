<?php

namespace App\Faction\Application;

use App\Faction\Domain\Faction;
use App\Faction\Domain\FactionFactory;
use App\Faction\Domain\FactionRepository;

/**
 * @api
 * @package App\Faction\Application
 * @author Your Name <your.email@example.com>
 * @since 1.0.0
 */
class CreateFactionUseCase
{
    /**
     * @param FactionRepository $repository The repository to use for faction operations
     */
    public function __construct(
        private FactionRepository $repository
    ) {}

    /**
     * Creates a new faction with the provided data
     *
     * @param CreateFactionUseCaseRequest $request The request containing faction data
     * @return Faction The created faction
     * @throws \Exception If the faction could not be created
     */
    public function execute(
        CreateFactionUseCaseRequest $request
    ): Faction {
        $faction = FactionFactory::build(
            $request->getFactionName(),
            $request->getDescription()
        );

        return $this->repository->save($faction);
    }
}
