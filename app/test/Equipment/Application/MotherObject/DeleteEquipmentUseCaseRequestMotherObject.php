<?php

namespace App\Test\Equipment\Application\MotherObject;

use App\Equipment\Domain\Equipment;
use App\Equipment\Domain\EquipmentFactory;

/**
 * This class is a mother object for the DeleteEquipmentUseCase.
 * It is used to create different scenarios for the use case.
 * @method static Equipment valid()
 * @method static Equipment withInvalidId()
 */
class DeleteEquipmentUseCaseRequestMotherObject
{
    public static function valid(): Equipment
    {
        return EquipmentFactory::build('Test Equipment', 'Weapon', 'Elfs', 1);
    }

    public static function invalidId(): Equipment
    {
        return EquipmentFactory::build('Test Equipment', 'Weapon', 'Elfs', 999999);
    }
}
