<?php

namespace App\Test\Character\Application\MotherObject;

use App\Character\Domain\CharacterFactory;

/**
 * This class is a mother object for the ReadAllCharactersUseCase.
 * It is used to create different scenarios for the use case.
 * @method static array withMultipleCharacters()
 * @method static array withEmptyRepository()
 * @method static array withOneCharacter()
 */

class ReadAllCharactersUseCaseRequestMotherObject
{
    public static function withMultipleCharacters(): array
    {
        return [
            CharacterFactory::build('John Doe', '1990-01-01', 'Kingdom of Doe', 1, 1, 1),
            CharacterFactory::build('Jane Doe', '1992-02-02', 'Kingdom of Jane', 2, 2, 2)
        ];
    }

    public static function withEmptyRepository(): array
    {
        return [];
    }

    public static function withOneCharacter(): array
    {
        return [
            CharacterFactory::build('John Doe', '1990-01-01', 'Kingdom of Doe', 1, 1, 1)
        ];
    }

    public static function withThreeCharacters(): array
    {
        return [
            CharacterFactory::build('John Doe', '1990-01-01', 'Kingdom of Doe', 1, 1, 1),
            CharacterFactory::build('Jane Doe', '1992-02-02', 'Kingdom of Jane', 2, 2, 2),
            CharacterFactory::build('Bob Smith', '1985-05-15', 'Kingdom of Smith', 3, 3, 3)
        ];
    }
}
