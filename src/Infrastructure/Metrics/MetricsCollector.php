<?php

declare(strict_types=1);

namespace App\Infrastructure\Metrics;

use Prometheus\CollectorRegistry;
use Prometheus\Counter;
use Prometheus\Exception\MetricsRegistrationException;
use Prometheus\Histogram;
use Prometheus\Storage\InMemory;

final class MetricsCollector
{
    private CollectorRegistry $registry;
    private Counter $logsProcessedCounter;
    private Counter $logsFailedCounter;
    private Histogram $processingDuration;

    /**
     * @throws MetricsRegistrationException
     */
    public function __construct()
    {
        $this->registry = new CollectorRegistry(new InMemory());

        $this->logsProcessedCounter = $this->registry->registerCounter(
            'app',
            'logs_processed_total',
            'Total number of logs processed',
            ['service', 'level'],
        );

        $this->logsFailedCounter = $this->registry->registerCounter(
            'app',
            'logs_failed_total',
            'Total number of failed log processing attempts',
            ['service'],
        );

        $this->processingDuration = $this->registry->registerHistogram(
            'app',
            'log_processing_duration_seconds',
            'Log processing duration in seconds',
            ['service'],
            [0.001, 0.005, 0.01, 0.05, 0.1, 0.5, 1.0],
        );
    }

    public function incrementProcessed(string $service, string $level): void
    {
        $this->logsProcessedCounter->incBy(1, [$service, $level]);
    }

    public function incrementFailed(string $service): void
    {
        $this->logsFailedCounter->incBy(1, [$service]);
    }

    public function observeProcessingDuration(string $service, float $duration): void
    {
        $this->processingDuration->observe($duration, [$service]);
    }

    public function getRegistry(): CollectorRegistry
    {
        return $this->registry;
    }
}
