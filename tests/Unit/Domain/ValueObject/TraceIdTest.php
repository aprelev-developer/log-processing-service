<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\ValueObject;

use Random\RandomException;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use App\Domain\ValueObject\TraceId;

final class TraceIdTest extends TestCase
{
    public function testFromValidString(): void
    {
        $traceId = TraceId::fromString('abc123def456');
        $this->assertSame('abc123def456', $traceId->value);
        $this->assertSame('abc123def456', (string) $traceId);
    }

    public function testFromValidUuid(): void
    {
        $traceId = TraceId::fromString('550e8400-e29b-41d4-a716-446655440000');
        $this->assertSame('550e8400-e29b-41d4-a716-446655440000', $traceId->value);
    }

    public function testFromInvalidStringThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        TraceId::fromString('invalid trace with spaces!');
    }

    public function testFromTooShortStringThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        TraceId::fromString('abc');
    }

    /**
     * @throws RandomException
     */
    public function testGenerate(): void
    {
        $traceId = TraceId::generate();
        $this->assertSame(32, strlen($traceId->value));
    }
}
