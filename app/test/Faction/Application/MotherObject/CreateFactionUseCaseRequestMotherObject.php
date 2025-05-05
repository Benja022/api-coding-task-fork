<?php

namespace App\Test\Faction\Application\MotherObject;

use App\Faction\Application\CreateFactionUseCaseRequest;

class CreateFactionUseCaseRequestMotherObject
{
    public static function valid(): CreateFactionUseCaseRequest
    {
        return new CreateFactionUseCaseRequest('Rohirrim', 'Los rohirrim se caracterizaban por ser altos, fornidos y de tez pálida y cabellos rubios, con ojos azules o verdes en su mayoría.');
    }

    public static function invalid(): CreateFactionUseCaseRequest
    {
        return new CreateFactionUseCaseRequest('', '');
    }

    public static function withInvalidName(): CreateFactionUseCaseRequest
    {
        return new CreateFactionUseCaseRequest('', '');
    }

    public static function withInvalidDescription(): CreateFactionUseCaseRequest
    {
        return new CreateFactionUseCaseRequest('Rohirrim', '');
    }

    public static function validAsArray(): array
    {
        return [
            'faction_name' => 'Rohirrim',
            'description' => 'Los rohirrim se caracterizaban por ser altos, fornidos y de tez pálida y cabellos rubios, con ojos azules o verdes en su mayoría.'
        ];
    }

    public static function missingNameAsArray(): array
    {
        return [
            'description' => 'Los rohirrim se caracterizaban por ser altos, fornidos y de tez pálida y cabellos rubios, con ojos azules o verdes en su mayoría.'
        ];
    }

    public static function emptyNameAsArray(): array
    {
        return [
            'faction_name' => '',
        ];
    }

    public static function invalidJsonAsArray(): array
    {
        return [
            'name' => 'Rohirrim',
            'description' => 'Los rohirrim se caracterizaban por ser altos, fornidos y de tez pálida y cabellos rubios, con ojos azules o verdes en su mayoría.'
        ];
    }
}
