<?php

namespace App\Character\Infrastructure\Persistence\Pdo;

use App\Character\Domain\Character;
use App\Character\Domain\CharacterFactory;

/**
 * MySQLCharacterFactory is a factory that creates a character from an array.
 *
 * @api
 * @package App\Character\Infrastructure\Persistence\Pdo
 */
class MySQLCharacterFactory
{
    public static function buildFromArray(array $data): Character
    {
        return CharacterFactory::build(
            $data['name'],
            $data['birth_date'],
            $data['kingdom'],
            (int) $data['equipment_id'],
            (int) $data['faction_id'],
            isset($data['id']) ? (int) $data['id'] : null
        );
    }
}
