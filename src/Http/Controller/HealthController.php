<?php

declare(strict_types=1);

namespace App\Http\Controller;

use Elastic\Elasticsearch\Client;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
final readonly class HealthController
{
    public function __construct(
        private Client $esClient,
    ) {
    }

    #[Route('/liveness', name: 'liveness', methods: ['GET'])]
    public function liveness(): JsonResponse
    {
        return new JsonResponse(['status' => 'alive']);
    }

    #[Route('/readiness', name: 'readiness', methods: ['GET'])]
    public function readiness(): JsonResponse
    {
        try {
            $response = $this->esClient->ping();
            $esReady = $response->getStatusCode() === 200;
        } catch (\Throwable) {
            $esReady = false;
        }

        if (!$esReady) {
            return new JsonResponse(['status' => 'not ready', 'elasticsearch' => false], 503);
        }

        return new JsonResponse(['status' => 'ready', 'elasticsearch' => true]);
    }
}
