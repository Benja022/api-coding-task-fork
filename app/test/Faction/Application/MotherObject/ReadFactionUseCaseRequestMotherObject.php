<?php

namespace App\Test\Faction\Application\MotherObject;

use App\Faction\Domain\Faction;
use App\Faction\Domain\FactionFactory;

class ReadFactionUseCaseRequestMotherObject
{
    public static function valid(): Faction
    {
        return FactionFactory::build('Rohirrim', 'Los rohirrim se caracterizaban por ser altos, fornidos y de tez pálida y cabellos rubios, con ojos azules o verdes en su mayoría.', 1);
    }

    public static function withInvalidId(): Faction
    {
        return FactionFactory::build('Rohirrim', 'Los rohirrim se caracterizaban por ser altos, fornidos y de tez pálida y cabellos rubios, con ojos azules o verdes en su mayoría.', 999);
    }
}
