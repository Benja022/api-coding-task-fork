<?php

namespace App\Character\Application;

use App\Character\Domain\Character;
use App\Character\Domain\CharacterRepository;

/**
 * ReadCharacterUseCase is a use case that reads a character.
 *
 * @api
 * @package App\Character\Application
 */

class ReadCharacterUseCase
{


    public function __construct(
        private CharacterRepository $characterRepository
    ) {}

    public function execute(int $id): Character
    {
        return  $this->characterRepository->find($id);
    }
}
