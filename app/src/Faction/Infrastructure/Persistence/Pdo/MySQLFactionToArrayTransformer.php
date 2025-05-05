<?php

namespace App\Faction\Infrastructure\Persistence\Pdo;

use App\Faction\Domain\Faction;

/**
 * MySQLFactionToArrayTransformer is a class that is used to transform a Faction object into an array.
 *
 * @package App\Faction\Infrastructure\Persistence\Pdo
 */

class MySQLFactionToArrayTransformer
{

    public static function transform(Faction $faction): array
    {
        $data = [
            'faction_name' => $faction->getFactionName(),
            'description' => $faction->getDescription()
        ];

        if (null !== $faction->getId()) {
            $data['id'] = $faction->getId();
        }

        return $data;
    }
}
