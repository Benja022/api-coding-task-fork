<?php

namespace App\Character\Domain;

use App\Character\Domain\Character;

/**
 * CharacterToArrayTransformer is a transformer that transforms a character to an array.
 *
 * @api
 * @package App\Character\Domain
 */
class CharacterToArrayTransformer
{

    public static function transform(Character $character): array
    {
        return [
            'id' => $character->getId(),
            'name' => $character->getName(),
            'birth-date' => $character->getBirthDate(),
            'kingdom' => $character->getKingdom(),
            'equipment-id' => $character->getEquipmentId(),
            'faction-id' => $character->getFactionId()
        ];
    }
}
