<?php

declare(strict_types=1);

namespace App\Http\Controller;

use App\Application\DTO\SearchQueryDTO;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use App\Infrastructure\Elasticsearch\ElasticsearchSearchRepository;

#[AsController]
final readonly class LogSearchController
{
    public function __construct(
        private ElasticsearchSearchRepository $searchRepository,
    ) {
    }

    #[Route('/api/logs/search', name: 'api_logs_search', methods: ['GET'])]
    public function search(#[MapQueryString] SearchQueryDTO $query = new SearchQueryDTO()): JsonResponse
    {
        $result = $this->searchRepository->search($query);

        return new JsonResponse($result->toArray());
    }
}