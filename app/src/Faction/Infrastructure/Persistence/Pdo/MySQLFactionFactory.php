<?php

namespace App\Faction\Infrastructure\Persistence\Pdo;

use App\Faction\Domain\Faction;
use App\Faction\Domain\FactionFactory;

/**
 * MySQLFactionFactory is a class that is used to create a new faction.
 *
 * @package App\Faction\Infrastructure\Persistence\Pdo
 */

class MySQLFactionFactory
{
    public static function buildFromArray(array $data): Faction
    {
        return FactionFactory::build(
            $data['faction_name'],
            $data['description'],
            $data['id'] ?? null // valor por defecto nulo.
        );
    }
}
