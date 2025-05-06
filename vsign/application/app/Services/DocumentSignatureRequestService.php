<?php

namespace App\Services;

use App\Contracts\SignatureRequestServiceInterface;
use App\Enums\SignatureRequestStatus;
use App\Models\Document;
use App\Models\DocumentSignatureRequest;
use Illuminate\Support\Collection;

class DocumentSignatureRequestService implements SignatureRequestServiceInterface
{
    public function initializeSignatureRequest(Document $document): DocumentSignatureRequest
    {
        $signatureRequest = DocumentSignatureRequest::query()
            ->where('document_id', $document->getId())
            ->first();

        if ($signatureRequest) {
            return $signatureRequest;
        }

        return DocumentSignatureRequest::query()->create([
            'document_id' => $document->getId(),
            'status' => SignatureRequestStatus::PENDING,
        ]);
    }

    /**
     * Get all active signature requests.
     *
     * @return Collection<int, DocumentSignatureRequest>
     */
    public function getActiveSignRequests(): Collection
    {
        return DocumentSignatureRequest::query()
            ->where('status', SignatureRequestStatus::PENDING)
            ->get();
    }

    public function updateSignatureRequestStatusByDocumentId(int $documentId): void
    {
        DocumentSignatureRequest::query()->where('document_id', $documentId)->update([
            'status' => SignatureRequestStatus::COMPLETED,
        ]);
    }
}
