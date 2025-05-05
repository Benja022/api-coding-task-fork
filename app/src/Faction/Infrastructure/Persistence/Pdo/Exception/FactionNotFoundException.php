<?php

namespace App\Faction\Infrastructure\Persistence\Pdo\Exception;

/**
 * ExceptionFactionNotFoundException is a class that is used to handle the exception when a faction is not found.
 *
 * @package App\Faction\Infrastructure\Persistence\Pdo\Exception
 */

class FactionNotFoundException extends \Exception
{
    private const MESSAGE = 'Faction not found';

    public static function build(): self
    {
        return new self(sprintf(self::MESSAGE));
    }
}
