<?php

namespace App\Shared\Infrastructure\Pdo\Exception;

/**
 * RowInsertionFailedException is an exception that is thrown when a row insertion fails.
 *
 * @package App\Shared\Infrastructure\Pdo\Exception
 */

class RowInsertionFailedException extends \Exception
{

    private const MESSAGE = 'Row insertion failed';

    public static function build(): self
    {
        return new self(self::MESSAGE);
    }
}
