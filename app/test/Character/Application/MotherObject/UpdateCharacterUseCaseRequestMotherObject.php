<?php

namespace App\Test\Character\Application\MotherObject;

use App\Character\Application\UpdateCharacterUseCaseRequest;

/**
 * This class is a mother object for the UpdateCharacterUseCase.
 * It is used to create different scenarios for the use case.
 * @method static UpdateCharacterUseCaseRequest valid()
 * @method static UpdateCharacterUseCaseRequest validAsArray()
 */
class UpdateCharacterUseCaseRequestMotherObject
{
    public static function valid(): UpdateCharacterUseCaseRequest
    {
        return new UpdateCharacterUseCaseRequest(
            name: 'John Doe',
            birthDate: '1990-01-01',
            kingdom: 'Kingdom of Spain',
            equipmentId: 1,
            factionId: 1,
            id: 1
        );
    }

    // Genera un array con los datos de la request para que pueda ser usado en el test, controller need data in HTTP(JSON -> array)
    public static function validAsArray(): array
    {
        return [
            'name' => 'John Doe',
            'birthDate' => '1990-01-01',
            'kingdom' => 'Kingdom of Spain',
            'equipmentId' => 1,
            'factionId' => 1,
            'id' => 1
        ];
    }
}
