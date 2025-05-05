<?php

namespace App\Equipment\Infrastructure\Persistence\Pdo\Exception;

class EquipmentNotFoundException extends \Exception
{
    private const MESSAGE = 'Equipment not found';

    public static function build(string $id): self
    {
        return new self(sprintf(self::MESSAGE, $id));
    }
}
