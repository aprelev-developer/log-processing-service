<?php

declare(strict_types=1);

namespace App\Application\DTO;

use DateTimeImmutable;
use Symfony\Component\Serializer\Attribute\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class SearchQueryDTO
{
    public function __construct(
        public ?string $service = null,

        #[Assert\Choice(choices: ['emergency', 'alert', 'critical', 'error', 'warning', 'notice', 'info', 'debug'])]
        public ?string $level = null,

        public ?string $message = null,

        #[SerializedName('trace_id')]
        public ?string $traceId = null,

        public ?DateTimeImmutable $from = null,

        public ?DateTimeImmutable $to = null,

        #[Assert\Range(min: 1)]
        public int $page = 1,

        #[SerializedName('per_page')]
        #[Assert\Range(min: 1, max: 100)]
        public int $perPage = 20,
    ) {
    }
}
