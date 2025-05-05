<?php

namespace App\Faction\Domain;

/**
 * FactionFactory is a factory class for creating Faction objects.
 * It is used to create Faction objects with the correct parameters.
 *
 * @package App\Faction\Domain
 */

class FactionFactory
{
    public static function build(
        string $faction_name,
        string $description,
        ?int $id = null
    ): Faction {
        return new Faction(
            $faction_name,
            $description,
            $id
        );
    }
}
