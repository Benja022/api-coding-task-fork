<?php

namespace App\Test\Equipment\Application\MotherObject;

use App\Equipment\Application\UpdateEquipmentUseCaseRequest;

/**
 * This class is a mother object for the UpdateEquipmentUseCase.
 * It is used to create different scenarios for the use case.
 * @method static UpdateEquipmentUseCaseRequest valid()
 * @method static UpdateEquipmentUseCaseRequest validAsArray()
 */

class UpdateEquipmentUseCaseRequestMotherObject
{
    public static function valid(): UpdateEquipmentUseCaseRequest
    {
        return new UpdateEquipmentUseCaseRequest(
            name: 'Anduril',
            type: 'Weapon',
            made_by: 'Elfs',
            id: 1
        );
    }
    // return an array with the valid request, to simulate the request from the controller (JSON)
    public static function validAsArray(): array
    {
        return [
            'name' => 'Anduril',
            'type' => 'Weapon',
            'made_by' => 'Elfs',
            'id' => 1
        ];
    }

    public static function invalid(): UpdateEquipmentUseCaseRequest
    {
        return new UpdateEquipmentUseCaseRequest(
            name: 'Anduril',
            type: 'Weapon',
            made_by: 'Elfs',
            id: 999
        );
    }
}
