<?php

namespace App\Test\Shared\Infrastructure\Pdo\Exception;

use App\Shared\Infrastructure\Pdo\Exception\RowInsertionFailedException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

class RowInsertionFailedExceptionTest extends TestCase
{
    #[Test]
    #[Group('shared')]
    #[Group('exception')]
    public function testBuildReturnsExceptionWithCorrectMessage(): void
    {
        $exception = RowInsertionFailedException::build();

        $this->assertInstanceOf(RowInsertionFailedException::class, $exception);
        $this->assertEquals('Row insertion failed', $exception->getMessage());
    }
}
