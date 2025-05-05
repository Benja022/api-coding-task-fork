<?php

namespace App\Equipment\Domain;

/**
 * This class transform the equipment to an array.
 */

class EquipmentToArrayTransformer
{
    public static function transform(Equipment $equipment): array
    {
        return [
            'id' => $equipment->getId(),
            'name' => $equipment->getName(),
            'type' => $equipment->getType(),
            'made_by' => $equipment->getMadeBy()
        ];
    }
}
