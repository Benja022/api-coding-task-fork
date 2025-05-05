<?php

namespace App\Character\Infrastructure\Persistence\Pdo;

use App\Character\Domain\Character;

/**
 * MySQLCharacterToArrayTransformer is a transformer that transforms a character to an array.
 *
 * @api
 * @package App\Character\Infrastructure\Persistence\Pdo
 */
class MySQLCharacterToArrayTransformer
{
    public static function transform(Character $character): array
    {
        $data = [
            'name' => $character->getName(),
            'birth_date' => $character->getBirthDate(),
            'kingdom' => $character->getKingdom(),
            'equipment_id' => $character->getEquipmentId(),
            'faction_id' => $character->getFactionId()
        ];

        if (null !== $character->getId()) {
            $data['id'] = $character->getId();
        }

        return $data;
    }
}
