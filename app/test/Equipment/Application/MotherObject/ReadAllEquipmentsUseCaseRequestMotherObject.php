<?php

namespace App\Test\Equipment\Application\MotherObject;

use App\Equipment\Domain\EquipmentFactory;

/**
 * This class is a mother object for the ReadAllEquipmentsUseCase.
 * It is used to create different scenarios for the use case.
 * @method static array withMultipleEquipment()
 * @method static array withEmptyRepository()
 */
class ReadAllEquipmentsUseCaseRequestMotherObject
{

    public static function valid(): array
    {
        return [
            EquipmentFactory::build('Anduril', 'Weapon', 'Elfs'),
        ];
    }

    public static function withMultipleEquipment(): array
    {
        return [
            EquipmentFactory::build('Anduril', 'Weapon', 'Elfs'),
            EquipmentFactory::build('Glamdring', 'Weapon', 'Elfs'),
            EquipmentFactory::build('Narsil', 'Weapon', 'Elfs'),
        ];
    }

    public static function withEmptyRepository(): array
    {
        return [];
    }
}
