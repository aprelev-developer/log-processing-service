<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\ValueObject;

use ValueError;
use PHPUnit\Framework\TestCase;
use App\Domain\ValueObject\LogLevel;

final class LogLevelTest extends TestCase
{
    public function testAllLevelsExist(): void
    {
        $expected = ['emergency', 'alert', 'critical', 'error', 'warning', 'notice', 'info', 'debug'];

        $actual = array_map(fn(LogLevel $level) => $level->value, LogLevel::cases());

        $this->assertSame($expected, $actual);
    }

    public function testFromValidString(): void
    {
        $level = LogLevel::from('error');
        $this->assertSame(LogLevel::Error, $level);
    }

    public function testFromInvalidStringThrows(): void
    {
        $this->expectException(ValueError::class);
    }
}
