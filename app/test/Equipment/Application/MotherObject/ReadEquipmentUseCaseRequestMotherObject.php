<?php

namespace App\Test\Equipment\Application\MotherObject;

use App\Equipment\Domain\Equipment;
use App\Equipment\Domain\EquipmentFactory;

/**
 * This class is a mother object for the ReadEquipmentUseCase.
 * It is used to create different scenarios for the use case.
 * @method static Equipment valid()
 * @method static Equipment withInvalidId()
 */

class ReadEquipmentUseCaseRequestMotherObject
{
    public static function valid(): Equipment
    {
        return EquipmentFactory::build('Anduril', 'Weapon', 'Elfs');
    }

    public static function withInvalidId(): Equipment
    {
        return EquipmentFactory::build('Anduril', 3, 'Elfs', 999);
    }
}
