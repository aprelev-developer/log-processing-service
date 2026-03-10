<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

use Random\RandomException;

final readonly class TraceId
{
    private function __construct(
        public string $value,
    ) {
    }

    public static function fromString(string $value): self
    {
        if (!preg_match('/^[a-f0-9\-]{8,64}$/i', $value)) {
            throw new \InvalidArgumentException(sprintf('Invalid trace ID format: "%s"', $value));
        }

        return new self($value);
    }

    /**
     * @throws RandomException
     */
    public static function generate(): self
    {
        return new self(bin2hex(random_bytes(16)));
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
