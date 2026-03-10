<?php

declare(strict_types=1);

namespace App\Infrastructure\Messaging\Middleware;

use Psr\Log\LoggerInterface;
use App\Application\DTO\LogMessage;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\StackInterface;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;

final readonly class CorrelationMiddleware implements MiddlewareInterface
{
    public function __construct(
        private LoggerInterface $logger,
    ) {
    }

    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        $message = $envelope->getMessage();

        if ($message instanceof LogMessage && $message->traceId !== null) {
            $this->logger->info('Processing log message', [
                'trace_id' => $message->traceId,
                'service' => $message->service,
            ]);
        }

        return $stack->next()->handle($envelope, $stack);
    }
}
