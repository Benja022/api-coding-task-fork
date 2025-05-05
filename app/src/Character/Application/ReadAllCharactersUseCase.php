<?php

namespace App\Character\Application;

use App\Character\Domain\CharacterRepository;

/**
 * ReadAllCharactersUseCase is a use case that reads all characters.
 *
 * @api
 * @package App\Character\Application
 */
class ReadAllCharactersUseCase
{
    public function __construct(
        private CharacterRepository $characterRepository
    ) {}

    public function execute(): array
    {
        return $this->characterRepository->findAll();
    }
}
