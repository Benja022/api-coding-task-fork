<?php

namespace App\Test\Character\Application\MotherObject;

use App\Character\Application\CreateCharacterUseCaseRequest;

class CreateCharacterUseCaseRequestMotherObject
{
    public static function valid(): CreateCharacterUseCaseRequest
    {
        return new CreateCharacterUseCaseRequest(
            'John Doe',
            '1990-01-01',
            'Kingdom of Spain',
            1,
            1
        );
    }

    public static function invalid(): CreateCharacterUseCaseRequest
    {
        return new CreateCharacterUseCaseRequest(
            '',
            '',
            '',
            0,
            0
        );
    }

    public static function withInvalidName(): CreateCharacterUseCaseRequest
    {
        return new CreateCharacterUseCaseRequest(
            '',
            '1990-01-01',
            'Spain',
            1,
            1
        );
    }

    public static function withInvalidBirthDate(): CreateCharacterUseCaseRequest
    {
        return new CreateCharacterUseCaseRequest(
            'Pepe',
            '25-01-01',
            'Spain',
            1,
            1
        );
    }

    public static function withInvalidKingdom(): CreateCharacterUseCaseRequest
    {
        return new CreateCharacterUseCaseRequest(
            'Pepe',
            '1990-01-01',
            '',
            1,
            1
        );
    }

    public static function withInvalidEquipmentId(): CreateCharacterUseCaseRequest
    {
        return new CreateCharacterUseCaseRequest(
            'Pepe',
            '1990-01-01',
            'Spain',
            0,
            1
        );
    }

    public static function withInvalidFactionId(): CreateCharacterUseCaseRequest
    {
        return new CreateCharacterUseCaseRequest(
            'Pepe',
            '1990-01-01',
            'Spain',
            1,
            0
        );
    }

    public static function withoutBirthDate(): CreateCharacterUseCaseRequest
    {
        return new CreateCharacterUseCaseRequest(
            'Pepe',
            '',
            'Spain',
            1,
            1
        );
    }
}
