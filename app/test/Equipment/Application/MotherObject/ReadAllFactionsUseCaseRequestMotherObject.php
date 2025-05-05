<?php

namespace App\Test\Equipment\Application\MotherObject;

use App\Faction\Domain\FactionFactory;

class ReadAllFactionsUseCaseRequestMotherObject
{
    public static function valid(): array
    {
        return [
            FactionFactory::build('Elfs', 'Elfs are a race of people who live in the forest and are known for their agility and speed.'),
        ];
    }

    public static function withMultipleFactions(): array
    {
        return [
            FactionFactory::build('Elfs', 'Elfs are a race of people who live in the forest and are known for their agility and speed.'),
            FactionFactory::build('Humans', 'Humans are a race of people who live in the city and are known for their strength and courage.'),
        ];
    }

    public static function withEmptyRepository(): array
    {
        return [];
    }
}
