<?php

namespace App\Equipment\Domain\Exception;

/**
 * EquipmentValidationException is a class that extends the DomainException class.
 * It is used to validate the equipment.
 *
 * @package App\Equipment\Domain\Exception
 */

class EquipmentValidationException extends \DomainException
{
    private const NAME_REQUIRED = 'Name is required';
    private const TYPE_REQUIRED = 'Type is required';
    private const MADE_BY_REQUIRED = 'Made by is required';
    private const ID_NON_POSITIVE = 'ID must be greater than 0';

    /***
     * Constructor
     */

    private function __construct(string $message)
    {
        parent::__construct($message);
    }

    /**
     * Funtions to validate the equipment and throw an exception if the equipment is not valid.
     */
    public static function nameRequired(): self
    {
        return new self(self::NAME_REQUIRED);
    }

    public static function typeRequired(): self
    {
        return new self(self::TYPE_REQUIRED);
    }

    public static function madeByRequired(): self
    {
        return new self(self::MADE_BY_REQUIRED);
    }

    public static function idNonPositive(): self
    {
        return new self(self::ID_NON_POSITIVE);
    }
}
