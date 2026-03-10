<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\ValueObject;

use App\Domain\ValueObject\LogIndexName;
use PHPUnit\Framework\TestCase;

final class LogIndexNameTest extends TestCase
{
    public function testCreateGeneratesCorrectFormat(): void
    {
        $date = new \DateTimeImmutable('2024-03-15');
        $indexName = LogIndexName::create('my-service', $date);

        $this->assertSame('logs-my-service-2024.03.15', $indexName->value);
        $this->assertSame('logs-my-service-2024.03.15', (string) $indexName);
    }

    public function testCreateSanitizesServiceName(): void
    {
        $date = new \DateTimeImmutable('2024-01-01');
        $indexName = LogIndexName::create('My Service_Name!', $date);

        $this->assertSame('logs-my-service-name--2024.01.01', $indexName->value);
    }
}
