<?php

declare(strict_types=1);

namespace App\Infrastructure\Messaging\Handler;

use Exception;
use Psr\Log\LoggerInterface;
use App\Application\DTO\LogMessage;
use App\Application\UseCase\ProcessLogUseCase;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[AsMessageHandler]
final readonly class LogMessageHandler
{
    public function __construct(
        private ValidatorInterface $validator,
        private ProcessLogUseCase $processLogUseCase,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * @throws Exception
     */
    public function __invoke(LogMessage $message): void
    {
        $violations = $this->validator->validate($message);

        if ($violations->count() > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $errors[] = sprintf('%s: %s', $violation->getPropertyPath(), $violation->getMessage());
            }

            $this->logger->error('Invalid log message', ['errors' => $errors]);

            throw new \InvalidArgumentException(sprintf('Invalid log message: %s', implode(', ', $errors)));
        }

        $this->processLogUseCase->execute($message);
    }
}
