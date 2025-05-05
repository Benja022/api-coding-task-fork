<?php

namespace App\Character\Application;

use App\Character\Domain\Character;

/**
 * CreateCharacterUseCaseResponse is a response that creates a character.
 *
 * @api
 * @package App\Character\Application
 */
class CreateCharacterUseCaseResponse
{
    public function __construct(
        private readonly Character $character
    ) {}

    public function getCharacter(): Character
    {
        return $this->character;
    }
}
