<?php

namespace App\Test\Equipment\Application\MotherObject;

use App\Equipment\Application\CreateEquipmentUseCaseRequest;

class CreateEquipmentUseCaseRequestMotherObject
{
    public static function valid(): CreateEquipmentUseCaseRequest
    {
        return new CreateEquipmentUseCaseRequest(
            'Anduril',
            'Weapon',
            'Elfs',
        );
    }

    public static function invalid(): CreateEquipmentUseCaseRequest
    {
        return new CreateEquipmentUseCaseRequest(
            '',
            '',
            '',
        );
    }

    public static function withInvalidName(): CreateEquipmentUseCaseRequest
    {
        return new CreateEquipmentUseCaseRequest(
            '',
            'Weapon',
            'Elfs',
        );
    }

    public static function withInvalidNameAsArray(): array
    {
        return [
            'name' => '',
            'type' => 'Weapon',
            'made_by' => 'Elfs'
        ];
    }

    public static function withInvalidType(): CreateEquipmentUseCaseRequest
    {
        return new CreateEquipmentUseCaseRequest(
            'Anduril',
            '',
            'Elfs',
        );
    }

    public static function withInvalidMadeBy(): CreateEquipmentUseCaseRequest
    {
        return new CreateEquipmentUseCaseRequest(
            'Anduril',
            'Weapon',
            '',
        );
    }

    public static function validAsArray(): array
    {
        return [
            'name' => 'Anduril',
            'type' => 'Weapon',
            'made_by' => 'Elfs',
        ];
    }
    public static function missingNameAsArray(): array
    {
        return [
            'type' => 'Weapon',
            'made_by' => 'Elfs',
        ];
    }
}
