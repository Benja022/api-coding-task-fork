<?php

namespace App\Character\Infrastructure\Persistence\Pdo\Exception;

/**
 * CharacterNotFoundException is an exception that is thrown when a character is not found.
 *
 * @api
 * @package App\Character\Infrastructure\Persistence\Pdo\Exception
 */
class CharacterNotFoundException extends \Exception
{
    private const MESSAGE = 'Character not found';

    public static function build(): self
    {
        return new self(self::MESSAGE);
    }
}
