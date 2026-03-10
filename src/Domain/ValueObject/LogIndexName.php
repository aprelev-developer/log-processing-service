<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

final readonly class LogIndexName
{
    private function __construct(
        public string $value,
    ) {
    }

    public static function create(string $serviceName, \DateTimeImmutable $date): self
    {
        $sanitized = preg_replace('/[^a-z0-9\-]/', '-', strtolower($serviceName));

        return new self(sprintf('logs-%s-%s', $sanitized, $date->format('Y.m.d')));
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
