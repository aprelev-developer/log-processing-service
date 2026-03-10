<?php

declare(strict_types=1);

namespace App\Domain\Entity;

use App\Domain\ValueObject\LogIndexName;
use App\Domain\ValueObject\LogLevel;
use App\Domain\ValueObject\TraceId;
use DateTimeImmutable;
use Random\RandomException;

final readonly class LogEntry
{
    private function __construct(
        public string $id,
        public string $service,
        public LogLevel $level,
        public string $message,
        public DateTimeImmutable $timestamp,
        public ?TraceId $traceId,
        public array $context,
    ) {
    }

    /**
     * @throws RandomException
     */
    public static function create(
        string $service,
        LogLevel $level,
        string $message,
        DateTimeImmutable $timestamp,
        ?TraceId $traceId = null,
        array $context = [],
    ): self {
        return new self(
            id: bin2hex(random_bytes(16)),
            service: $service,
            level: $level,
            message: $message,
            timestamp: $timestamp,
            traceId: $traceId,
            context: $context,
        );
    }

    public function getIndexName(): LogIndexName
    {
        return LogIndexName::create($this->service, $this->timestamp);
    }

    public function toIndexableArray(): array
    {
        return [
            'service' => $this->service,
            'level' => $this->level->value,
            'message' => $this->message,
            'timestamp' => $this->timestamp->format('c'),
            'trace_id' => $this->traceId?->value,
            'context' => $this->context,
        ];
    }
}
