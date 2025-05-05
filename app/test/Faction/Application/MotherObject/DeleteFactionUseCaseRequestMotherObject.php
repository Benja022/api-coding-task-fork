<?php

namespace App\Test\Faction\Application\MotherObject;

use App\Faction\Domain\Faction;
use App\Faction\Domain\FactionFactory;

class DeleteFactionUseCaseRequestMotherObject
{
    public static function valid(): Faction
    {
        return FactionFactory::build('Elfs', 'Elfs are a race of people who live in the forest and are known for their agility and speed.');
    }

    public static function invalidId(): Faction
    {
        return FactionFactory::build('Elfs', 'Elfs are a race of people who live in the forest and are known for their agility and speed.', 999999);
    }
}
