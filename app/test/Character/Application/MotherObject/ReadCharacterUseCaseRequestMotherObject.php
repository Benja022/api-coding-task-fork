<?php

/**
 * This class is a mother object for the ReadCharacterUseCase.
 * It is used to create different scenarios for the use case.
 * @method static Character valid()
 * @method static Character withInvalidId()
 */

namespace App\Test\Character\Application\MotherObject;

use App\Character\Domain\Character;
use App\Character\Domain\CharacterFactory;

class ReadCharacterUseCaseRequestMotherObject
{
    public static function valid(): Character
    {
        return CharacterFactory::build(
            'John Doe',
            '1990-01-01',
            'Kingdom of Doe',
            1,
            1,
            1
        );
    }

    public static function withInvalidId(): Character
    {
        return CharacterFactory::build(
            'John Doe',
            '1990-01-01',
            'Kingdom of Doe',
            1,
            1,
            999999
        );
    }
}
