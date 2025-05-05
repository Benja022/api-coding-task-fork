<?php

namespace App\Faction\Domain\Exception;

/**
 * FactionValidationException is a class that extends the DomainException class.
 * It is used to validate the faction.
 *
 * @package App\Faction\Domain\Exception
 */

class FactionValidationException extends \DomainException
{
    private const FACTION_NAME_REQUIRED = 'Faction name is required';
    private const DESCRIPTION_REQUIRED = 'Faction description is required';
    private const ID_NON_POSITIVE = 'ID must be greater than 0';

    /***
     * Constructor
     */

    private function __construct(string $message)
    {
        parent::__construct($message);
    }

    /**
     * Funtions to validate the faction and throw an exception if the faction is not valid.
     */
    public static function factionNameRequired(): self
    {
        return new self(self::FACTION_NAME_REQUIRED);
    }

    public static function factionDescriptionRequired(): self
    {
        return new self(self::DESCRIPTION_REQUIRED);
    }

    public static function idNonPositive(): self
    {
        return new self(self::ID_NON_POSITIVE);
    }
}
