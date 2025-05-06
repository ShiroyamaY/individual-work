<?php

namespace App\Providers;

use App\Services\Metrics\MetricsRegistry;
use App\Services\Metrics\MetricsService;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use OpenTelemetry\API\Signals;
use OpenTelemetry\Contrib\Grpc\GrpcTransportFactory;
use OpenTelemetry\Contrib\Otlp\MetricExporter;
use OpenTelemetry\Contrib\Otlp\OtlpUtil;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Common\Util\ShutdownHandler;
use OpenTelemetry\SDK\Metrics\MeterProvider;
use OpenTelemetry\SDK\Metrics\MetricReader\ExportingReader;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use OpenTelemetry\SDK\Resource\ResourceInfoFactory;
use OpenTelemetry\SemConv\ResourceAttributes;

class MetricsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(MeterProvider::class, function (Application $app) {
            $resource = ResourceInfoFactory::emptyResource()->merge(ResourceInfo::create(Attributes::create([
                ResourceAttributes::SERVICE_NAMESPACE => config('telemetry.service_namespace'),
                ResourceAttributes::SERVICE_NAME => config('telemetry.service_name'),
                ResourceAttributes::DEPLOYMENT_ENVIRONMENT_NAME => config('telemetry.deployment_environment'),
            ])));

            $grpcTransport = (new GrpcTransportFactory())->create(
                config('telemetry.otel_collector_url').OtlpUtil::method(Signals::METRICS)
            );
            $metricExporter = new MetricExporter($grpcTransport);
            $metricReader = new ExportingReader($metricExporter);

            return MeterProvider::builder()
                ->setResource($resource)
                ->addReader($metricReader)
                ->build();
        });

        $this->app->singleton(MetricsRegistry::class, function (Application $app) {
            return new MetricsRegistry($app->make(MeterProvider::class));
        });

        $this->app->singleton(MetricsService::class, function (Application $app) {
            return new MetricsService(
                $app->make(MetricsRegistry::class)
            );
        });
    }

    /**
     * @throws BindingResolutionException
     */
    public function boot(): void
    {
        ShutdownHandler::register([$this->app->make(MeterProvider::class), 'shutdown']);

        /** @var MetricsService $metricsService */
        $metricsService = $this->app->make(MetricsService::class);

        DB::listen(function ($query) use ($metricsService) {
            $metricsService->incrementCounter(MetricsRegistry::COUNTER_DB_QUERIES);
            $metricsService->recordHistogram(MetricsRegistry::HISTOGRAM_DB_QUERY_DURATION, $query->time);
        });
    }
}
