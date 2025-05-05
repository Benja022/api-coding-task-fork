<?php

namespace App\Character\Application;

use App\Character\Domain\CharacterFactory;
use App\Character\Domain\CharacterRepository;

/**
 * CreateCharacterUseCase is a use case that creates a character.
 *
 * @api
 * @package App\Character\Application
 */

class CreateCharacterUseCase
{
    /**
     * @api
     * @param CharacterRepository $repository
     */
    public function __construct(
        private CharacterRepository $repository,
    ) {}

    /**
     * @api
     * @param CreateCharacterUseCaseRequest $request
     * @return CreateCharacterUseCaseResponse
     */
    public function execute(
        CreateCharacterUseCaseRequest $request
    ): CreateCharacterUseCaseResponse {
        $character = CharacterFactory::build(
            $request->getName(),
            $request->getBirthDate(),
            $request->getKingdom(),
            $request->getEquipmentId(),
            $request->getFactionId()
        );

        // This is the repository pattern, we are saving the character in the database.
        $character = $this->repository->save($character);

        return new CreateCharacterUseCaseResponse($character);
    }
}
