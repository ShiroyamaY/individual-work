<?php

namespace App\Services\Metrics;

use Illuminate\Support\Collection;
use OpenTelemetry\API\Metrics\CounterInterface;
use OpenTelemetry\API\Metrics\HistogramInterface;
use OpenTelemetry\SDK\Metrics\MeterProvider;

class MetricsRegistry
{
    private MeterProvider $meterProvider;

    /** @var Collection<string, CounterInterface> */
    private Collection $counters;

    /** @var Collection<string, HistogramInterface> */
    private Collection $histograms;

    public const string METER_HTTP = 'http';

    public const string METER_DB = 'database';

    public const string COUNTER_HTTP_REQUESTS = 'http_requests_total';

    public const string COUNTER_DB_QUERIES = 'db_queries_total';

    public const string HISTOGRAM_DB_QUERY_DURATION = 'db_query_duration';

    public function __construct(MeterProvider $meterProvider)
    {
        $this->meterProvider = $meterProvider;
        $this->counters = new Collection();
        $this->histograms = new Collection();

        $this->initializeMetrics();
    }

    private function initializeMetrics(): void
    {
        $meterHttp = $this->meterProvider->getMeter(self::METER_HTTP);
        $meterDb = $this->meterProvider->getMeter(self::METER_DB);

        $this->counters->put(
            self::COUNTER_HTTP_REQUESTS,
            $meterHttp->createCounter(self::COUNTER_HTTP_REQUESTS, 'Total number of HTTP requests')
        );

        $this->counters->put(
            self::COUNTER_DB_QUERIES,
            $meterDb->createCounter(self::COUNTER_DB_QUERIES, 'Total number of database queries')
        );

        $this->histograms->put(
            self::HISTOGRAM_DB_QUERY_DURATION,
            $meterDb->createHistogram(self::HISTOGRAM_DB_QUERY_DURATION, 'Database query duration in milliseconds')
        );
    }

    public function getCounter(string $counterName): ?CounterInterface
    {
        return $this->counters->get($counterName) ?? null;
    }

    public function getHistogram(string $histogramName): ?HistogramInterface
    {
        return $this->histograms->get($histogramName) ?? null;
    }
}
