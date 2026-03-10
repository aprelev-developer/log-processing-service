<?php

declare(strict_types=1);

namespace App\Application\Exception;

use Throwable;

final class SearchServiceException extends \RuntimeException
{
    public static function fromPrevious(Throwable $previous): self
    {
        return new self('Search service request failed', $previous->getCode(), $previous);
    }
}