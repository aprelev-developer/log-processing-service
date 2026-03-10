<?php

declare(strict_types=1);

namespace App\Infrastructure\Elasticsearch;

use Elastic\Elasticsearch\Client;

final readonly class IndexTemplateManager
{
    private const TEMPLATE_NAME = 'logs-template';

    public function __construct(
        private Client $client,
    ) {
    }

    public function ensureTemplateExists(): void
    {
        $this->client->indices()->putIndexTemplate([
            'name' => self::TEMPLATE_NAME,
            'body' => [
                'index_patterns' => ['logs-*'],
                'priority' => 200,
                'template' => [
                    'settings' => [
                        'number_of_shards' => 1,
                        'number_of_replicas' => 1,
                        'refresh_interval' => '5s',
                    ],
                    'mappings' => LogIndexMappingFactory::getMapping(),
                ],
            ],
        ]);
    }
}
