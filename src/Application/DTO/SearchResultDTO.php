<?php

declare(strict_types=1);

namespace App\Application\DTO;

final readonly class SearchResultDTO
{
    public function __construct(
        public int $total,
        /** @var list<array<string, mixed>> */
        public array $logs,
        public int $page,
        public int $perPage,
    ) {
    }

    public function toArray(): array
    {
        return [
            'total' => $this->total,
            'page' => $this->page,
            'per_page' => $this->perPage,
            'logs' => $this->logs,
        ];
    }
}
