<?php

return [
    'document_signed' => [
        'enabled' => env('DOCUMENT_SIGNED_WEBHOOK_ENABLED', false),
        'url' => env('DOCUMENT_SIGNED_WEBHOOK_URL'),
        'timeout' => env('DOCUMENT_SIGNED_WEBHOOK_TIMEOUT', 30),
        'retry_attempts' => env('DOCUMENT_SIGNED_WEBHOOK_RETRY_ATTEMPTS', 3),
        'retry_delay' => env('DOCUMENT_SIGNED_WEBHOOK_RETRY_DELAY', 60),
    ],
];
