<?php

declare(strict_types=1);

namespace App\Application\DTO;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class LogMessage
{
    public function __construct(
        #[Assert\NotBlank]
        public string $service,

        #[Assert\NotBlank]
        #[Assert\Choice(choices: ['emergency', 'alert', 'critical', 'error', 'warning', 'notice', 'info', 'debug'])]
        public string $level,

        #[Assert\NotBlank]
        public string $message,

        #[Assert\NotBlank]
        public string $timestamp,

        public ?string $traceId = null,

        public array $context = [],
    ) {
    }
}
