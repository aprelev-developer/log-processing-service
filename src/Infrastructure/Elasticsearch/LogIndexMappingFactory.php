<?php

declare(strict_types=1);

namespace App\Infrastructure\Elasticsearch;

final class LogIndexMappingFactory
{
    public static function getMapping(): array
    {
        return [
            'properties' => [
                'service' => ['type' => 'keyword'],
                'level' => ['type' => 'keyword'],
                'message' => ['type' => 'text'],
                'timestamp' => ['type' => 'date'],
                'trace_id' => ['type' => 'keyword'],
                'context' => ['type' => 'object', 'enabled' => true, 'dynamic' => true],
            ],
        ];
    }
}
