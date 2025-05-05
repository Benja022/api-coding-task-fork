<?php

namespace App\Character\Application;

use App\Character\Domain\CharacterRepository;

/**
 * DeleteCharacterUseCase is a use case that deletes a character.
 *
 * @package App\Character\Application
 */

class DeleteCharacterUseCase
{

    // Constructor
    public function __construct(
        private CharacterRepository $repository,
    ) {}

    /**
     * This method deletes a character.
     * It first finds the character by its id.
     * Then it deletes the character from the repository.
     */
    public function execute(int $id): void
    {
        $character = $this->repository->find($id);
        $this->repository->delete($character);
    }
}
