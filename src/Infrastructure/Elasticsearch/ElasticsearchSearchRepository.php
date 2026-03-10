<?php

declare(strict_types=1);

namespace App\Infrastructure\Elasticsearch;

use Throwable;
use Elastic\Elasticsearch\Client;
use App\Application\DTO\SearchQueryDTO;
use App\Application\DTO\SearchResultDTO;
use App\Application\Exception\SearchServiceException;
use Elastic\Elasticsearch\Exception\ClientResponseException;

class ElasticsearchSearchRepository
{
    public function __construct(
        private readonly Client $client,
    ) {
    }

    public function search(SearchQueryDTO $query): SearchResultDTO
    {
        $must = [];
        $filter = [];

        if ($query->service !== null) {
            $filter[] = ['term' => ['service' => $query->service]];
        }

        if ($query->level !== null) {
            $filter[] = ['term' => ['level' => $query->level]];
        }

        if ($query->message !== null) {
            $must[] = ['match' => ['message' => $query->message]];
        }

        if ($query->traceId !== null) {
            $filter[] = ['term' => ['trace_id' => $query->traceId]];
        }

        $range = [];
        if ($query->from !== null) {
            $range['gte'] = $query->from->format('c');
        }
        if ($query->to !== null) {
            $range['lte'] = $query->to->format('c');
        }
        if ($range !== []) {
            $filter[] = ['range' => ['timestamp' => $range]];
        }

        $bool = ['filter' => $filter];
        if ($must !== []) {
            $bool['must'] = $must;
        }

        $body = [
            'query' => ['bool' => $bool],
            'sort' => [['timestamp' => 'desc']],
            'from' => ($query->page - 1) * $query->perPage,
            'size' => $query->perPage,
        ];

        try {
            $response = $this->client->search([
                'index' => 'logs-*',
                'body' => $body,
            ]);

            $data = $response->asArray();
            $hits = $data['hits']['hits'] ?? [];
            $total = $data['hits']['total']['value'] ?? 0;

            $logs = array_map(
                fn(array $hit) => array_merge(['_id' => $hit['_id'], '_index' => $hit['_index']], $hit['_source']),
                $hits,
            );

            return new SearchResultDTO(
                total: $total,
                logs: $logs,
                page: $query->page,
                perPage: $query->perPage,
            );
        } catch (ClientResponseException $e) {
            if ($e->getCode() === 404) {
                return new SearchResultDTO(total: 0, logs: [], page: $query->page, perPage: $query->perPage);
            }
            throw SearchServiceException::fromPrevious($e);
        } catch (Throwable $e) {
            throw SearchServiceException::fromPrevious($e);
        }
    }
}
