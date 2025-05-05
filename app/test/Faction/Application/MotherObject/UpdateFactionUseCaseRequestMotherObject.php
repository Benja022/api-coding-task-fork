<?php

namespace App\Test\Faction\Application\MotherObject;

use App\Faction\Application\UpdateFactionUseCaseRequest;

class UpdateFactionUseCaseRequestMotherObject
{
    public static function valid(): UpdateFactionUseCaseRequest
    {
        return new UpdateFactionUseCaseRequest(
            faction_name: 'Elfs',
            description: 'Elfs are a race of people who live in the forest and are known for their agility and speed.',
            id: 1
        );
    }

    public static function validAsArray(): array
    {
        return [
            'faction_name' => 'Elfs',
            'description' => 'Elfs are a race of people who live in the forest and are known for their agility and speed.',
            'id' => 1
        ];
    }

    public static function invalid(): UpdateFactionUseCaseRequest
    {
        return new UpdateFactionUseCaseRequest(
            faction_name: 'Elfs',
            description: 'Elfs are a race of people who live in the forest and are known for their agility and speed.',
            id: 999
        );
    }
}
