<?php

namespace App\Jobs;

use App\Http\DTO\DocumentSignedDTO;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class NotifyWebhookDocumentSignedJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public mixed $tries;

    public mixed $backoff;

    public function __construct(
        public DocumentSignedDTO $documentSignedDTO
    ) {
        $this->tries = Config::get('webhooks.document_signed.retry_attempts', 3);
        $this->backoff = Config::get('webhooks.document_signed.retry_delay', 60);
    }

    /**
     * @throws ConnectionException
     */
    public function handle(): void
    {
        if (! Config::get('webhooks.document_signed.enabled', false)) {
            Log::info('Document signed webhook is disabled', [
                'request_id' => $this->documentSignedDTO->requestId,
            ]);

            return;
        }

        $webhookUrl = Config::get('webhooks.document_signed.url');

        if (! $webhookUrl) {
            Log::warning('No webhook URL configured for document signed events', [
                'request_id' => $this->documentSignedDTO->requestId,
            ]);

            return;
        }

        try {
            $timeout = Config::get('webhooks.document_signed.timeout', 30);
            $response = Http::withOptions(['verify' => false])->timeout($timeout)->post($webhookUrl, [
                'event' => 'document.signed',
                'document' => $this->documentSignedDTO->toArray(),
            ]);

            if (! $response->successful()) {
                throw new RuntimeException('Webhook failed with status: '.$response->status());
            }

            Log::info('Webhook sent successfully', [
                'request_id' => $this->documentSignedDTO->requestId,
                'status' => $response->status(),
            ]);
        } catch (Exception $exception) {
            Log::error('Webhook failed', [
                'request_id' => $this->documentSignedDTO->requestId,
                'error' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }
}
