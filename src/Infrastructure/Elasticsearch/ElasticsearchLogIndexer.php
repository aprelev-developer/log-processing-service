<?php

declare(strict_types=1);

namespace App\Infrastructure\Elasticsearch;

use App\Application\Port\LogIndexerInterface;
use App\Domain\Entity\LogEntry;
use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\Exception\ClientResponseException;
use Elastic\Elasticsearch\Exception\MissingParameterException;
use Elastic\Elasticsearch\Exception\ServerResponseException;

final class ElasticsearchLogIndexer implements LogIndexerInterface
{
    private array $createdIndices = [];

    public function __construct(
        private readonly Client $client,
    ) {
    }

    /**
     * @throws ClientResponseException
     * @throws ServerResponseException
     * @throws MissingParameterException
     */
    public function index(LogEntry $log): void
    {
        $indexName = (string) $log->getIndexName();

        $this->ensureIndexExists($indexName);

        $this->client->index([
            'index' => $indexName,
            'id' => $log->id,
            'body' => $log->toIndexableArray(),
        ]);
    }

    private function ensureIndexExists(string $indexName): void
    {
        if (isset($this->createdIndices[$indexName])) {
            return;
        }

        try {
            $exists = $this->client->indices()->exists(['index' => $indexName]);
            if ($exists->getStatusCode() === 200) {
                $this->createdIndices[$indexName] = true;
                return;
            }
        } catch (ClientResponseException $e) {
            if ($e->getCode() !== 404) {
                throw $e;
            }
        }

        $this->client->indices()->create([
            'index' => $indexName,
            'body' => [
                'mappings' => LogIndexMappingFactory::getMapping(),
            ],
        ]);

        $this->createdIndices[$indexName] = true;
    }
}
