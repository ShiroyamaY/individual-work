<?php

use App\Services\Metrics\MetricsRegistry;
use OpenTelemetry\API\Signals;
use OpenTelemetry\Contrib\Grpc\GrpcTransportFactory;
use OpenTelemetry\Contrib\Otlp\MetricExporter;
use OpenTelemetry\Contrib\Otlp\OtlpUtil;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Metrics\MeterProvider;
use OpenTelemetry\SDK\Metrics\MetricReader\ExportingReader;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use OpenTelemetry\SDK\Resource\ResourceInfoFactory;
use OpenTelemetry\SemConv\ResourceAttributes;

$serviceNamespace = $_ENV['OTEL_SERVICE_NAMESPACE'];
$serviceName = $_ENV['OTEL_SERVICE_NAME'];
$deploymentEnvironment = $_ENV['OTEL_DEPLOYMENT_ENVIRONMENT'];
$otelCollectorUrl = $_ENV['OTEL_EXPORTER_OTLP_ENDPOINT'];

if (
    $serviceName &&
    $serviceNamespace &&
    $deploymentEnvironment &&
    $otelCollectorUrl
) {
    $resource = ResourceInfoFactory::emptyResource()->merge(ResourceInfo::create(Attributes::create([
        ResourceAttributes::SERVICE_NAMESPACE => $serviceNamespace,
        ResourceAttributes::SERVICE_NAME => $serviceName,
        ResourceAttributes::DEPLOYMENT_ENVIRONMENT_NAME => $deploymentEnvironment,
    ])));

    $grpcTransport = (new GrpcTransportFactory())->create($otelCollectorUrl . OtlpUtil::method(Signals::METRICS));
    $metricExporter = new MetricExporter($grpcTransport);
    $metricReader = new ExportingReader($metricExporter);
    $meterProvider = MeterProvider::builder()
        ->setResource($resource)
        ->addReader($metricReader)
        ->build();

    $httpMeter = $meterProvider->getMeter(MetricsRegistry::METER_HTTP);
    $httpRequestCounter = $httpMeter->createCounter(
        MetricsRegistry::COUNTER_HTTP_REQUESTS,
        'requests'
    );

    $httpRequestCounter->add(1);

    $meterProvider->forceFlush();
}
