<?php

declare(strict_types=1);

namespace Guiziweb\BookStackClient\Tests\Exception;

use Guiziweb\BookStackClient\Exception\ValidationException;
use PHPUnit\Framework\TestCase;

/**
 * Test for ValidationException.
 *
 * @author Camille Islasse
 */
class ValidationExceptionTest extends TestCase
{
    public function testExceptionMessage(): void
    {
        $exception = new ValidationException('Test validation error');

        $this->assertSame('Test validation error', $exception->getMessage());
    }

    public function testExceptionInheritance(): void
    {
        $exception = new ValidationException('Test error');

        $this->assertInstanceOf(\Guiziweb\BookStackClient\Exception\BookStackException::class, $exception);
    }
}
