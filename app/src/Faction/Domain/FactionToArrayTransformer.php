<?php

namespace App\Faction\Domain;

/**
 * This class transform the faction to an array.
 */

class FactionToArrayTransformer
{
    public static function transform(Faction $faction): array
    {
        return [
            'id' => $faction->getId(),
            'faction_name' => $faction->getFactionName(),
            'description' => $faction->getDescription()
        ];
    }
}
