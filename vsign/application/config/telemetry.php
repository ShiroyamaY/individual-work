<?php

return [
    'otel_collector_url' => env('OTEL_COLLECTOR_URL', 'http://otel-collector:4317'),
    'service_namespace' => env('OTEL_SERVICE_NAMESPACE', 'demo'),
    'service_name' => env('OTEL_SERVICE_NAME', 'store-app'),
    'deployment_environment' => env('OTEL_ENVIRONMENT', 'development'),
];
