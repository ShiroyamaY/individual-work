<?php

namespace App\Jobs;

use App\Contracts\DocumentSignatureServiceInterface;
use App\Http\DTO\DocumentSignedDTO;
use App\Models\DocumentSignatureRequest;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class SignDocumentJob
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private DocumentSignatureRequest $documentSignatureRequest;

    public function __construct(DocumentSignatureRequest $documentSignatureRequest)
    {
        $this->documentSignatureRequest = $documentSignatureRequest;
    }

    public function handle(
        DocumentSignatureServiceInterface $documentSignatureService
    ): void {
        try {
            $documentSignature = $documentSignatureService->sign($this->documentSignatureRequest);

            if (! $documentSignature) {
                throw new RuntimeException("Failed to sign document ID: {$this->documentSignatureRequest->document->id}");
            }

            $documentSignedDTO = DocumentSignedDTO::fromDocumentSignature($documentSignature);
            $signerEmail = $documentSignature->user->email ?? "{$documentSignature->user->name}@github.com";

            NotifyWebhookDocumentSignedJob::dispatch($documentSignedDTO);
            SendDocumentSignedEmailJob::dispatch($signerEmail, $documentSignedDTO);
        } catch (Exception $e) {
            Log::error('Error in SignDocumentJob: '.$e->getMessage());
        }
    }
}
