<?php

declare(strict_types=1);

namespace App\Application\UseCase;

use Exception;
use DateTimeImmutable;
use Psr\Log\LoggerInterface;
use App\Domain\Entity\LogEntry;
use App\Domain\ValueObject\TraceId;
use App\Application\DTO\LogMessage;
use App\Domain\ValueObject\LogLevel;
use App\Application\Port\LogIndexerInterface;

final readonly class ProcessLogUseCase
{
    public function __construct(
        private LogIndexerInterface $logIndexer,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * @throws Exception
     */
    public function execute(LogMessage $message): void
    {
        $logEntry = LogEntry::create(
            service: $message->service,
            level: LogLevel::from($message->level),
            message: $message->message,
            timestamp: new DateTimeImmutable($message->timestamp),
            traceId: $message->traceId !== null ? TraceId::fromString($message->traceId) : null,
            context: $message->context,
        );

        $this->logIndexer->index($logEntry);

        $this->logger->info('Log entry indexed', [
            'service' => $logEntry->service,
            'level' => $logEntry->level->value,
            'index' => (string) $logEntry->getIndexName(),
        ]);
    }
}
