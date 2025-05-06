<?php

namespace App\Contracts;

use App\Models\Document;
use App\Models\DocumentSignatureRequest;
use Illuminate\Support\Collection;

interface SignatureRequestServiceInterface
{
    public function initializeSignatureRequest(Document $document): DocumentSignatureRequest;

    /**
     * Get all active signature requests.
     *
     * @return Collection<int, DocumentSignatureRequest>
     */
    public function getActiveSignRequests(): Collection;

    public function updateSignatureRequestStatusByDocumentId(int $documentId): void;
}
