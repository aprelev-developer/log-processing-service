<?php

declare(strict_types=1);

namespace App\Http\Controller;

use Throwable;
use Prometheus\RenderTextFormat;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Infrastructure\Metrics\MetricsCollector;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
final readonly class MetricsController
{
    public function __construct(
        private MetricsCollector $metricsCollector,
    ) {
    }

    #[Route('/metrics', name: 'metrics', methods: ['GET'])]
    public function metrics(): Response
    {
        try {
            $renderer = new RenderTextFormat();
            $result = $renderer->render($this->metricsCollector->getRegistry()->getMetricFamilySamples());

            return new Response($result, 200, [
                'Content-Type' => RenderTextFormat::MIME_TYPE,
            ]);
        } catch (Throwable) {
            return new Response("# ERROR: Metrics unavailable\n", 503, [
                'Content-Type' => 'text/plain',
            ]);
        }
    }
}
