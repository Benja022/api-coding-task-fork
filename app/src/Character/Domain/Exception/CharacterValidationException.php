<?php

namespace App\Character\Domain\Exception;

/**
 * CharacterValidationException is a domain exception that validates a character.
 *
 * @api
 * @package App\Character\Domain\Exception
 */
class CharacterValidationException extends \DomainException
{
    private const NAME_REQUIRED = 'Name is required';
    private const BIRTH_DATE_REQUIRED = 'Birth date is required';
    private const BIRTH_DATE_INVALID_FORMAT = 'Birth date is invalid';
    private const KINGDOM_REQUIRED = 'Kingdom is required';
    private const EQUIPMENT_ID_REQUIRED = 'Equipment ID is required';
    private const EQUIPMENT_ID_NON_POSITIVE = 'Equipment ID must be greater than 0';
    private const FACTION_ID_REQUIRED = 'Faction ID is required';
    private const FACTION_ID_NON_POSITIVE = 'Faction ID must be greater than 0';
    private const ID_NON_POSITIVE = 'ID must be greater than 0';


    private function __construct(string $message)
    {
        parent::__construct($message);
    }

    public static function nameRequired(): self
    {
        return new self(self::NAME_REQUIRED);
    }

    public static function birthDateRequired(): self
    {
        return new self(self::BIRTH_DATE_REQUIRED);
    }

    public static function birthDateInvalidFormat(): self
    {
        return new self(self::BIRTH_DATE_INVALID_FORMAT);
    }

    public static function kingdomRequired(): self
    {
        return new self(self::KINGDOM_REQUIRED);
    }

    public static function equipmentIdRequired(): self
    {
        return new self(self::EQUIPMENT_ID_REQUIRED);
    }

    public static function equipmentIdNonPositive(): self
    {
        return new self(self::EQUIPMENT_ID_NON_POSITIVE);
    }

    public static function factionIdRequired(): self
    {
        return new self(self::FACTION_ID_REQUIRED);
    }

    public static function factionIdNonPositive(): self
    {
        return new self(self::FACTION_ID_NON_POSITIVE);
    }

    public static function idNonPositive(): self
    {
        return new self(self::ID_NON_POSITIVE);
    }
}
