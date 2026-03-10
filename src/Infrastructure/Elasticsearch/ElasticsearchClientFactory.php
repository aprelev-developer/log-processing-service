<?php

declare(strict_types=1);

namespace App\Infrastructure\Elasticsearch;

use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\ClientBuilder;
use Elastic\Elasticsearch\Exception\AuthenticationException;

final class ElasticsearchClientFactory
{
    public function __construct(
        private readonly string $host,
    ) {
    }

    /**
     * @throws AuthenticationException
     */
    public function create(): Client
    {
        return ClientBuilder::create()
            ->setHosts([$this->host])
            ->build();
    }
}
