<?php

declare(strict_types=1);

namespace App\Application\Port;

use App\Domain\Entity\LogEntry;

interface LogIndexerInterface
{
    public function index(LogEntry $log): void;
}
