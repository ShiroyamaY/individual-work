<?php

namespace App\Contracts;

use App\Http\DTO\Signature\SignatureInitializeDTO;
use App\Models\Document;
use App\Models\DocumentSignature;
use App\Models\DocumentSignatureRequest;
use Illuminate\Support\Collection;

interface DocumentSignatureServiceInterface
{
    public function initializeSignatureRequest(SignatureInitializeDTO $initializeDTO): ?DocumentSignatureRequest;

    /**
     * Get documents that need to be signed.
     *
     * @return Collection<int, Document>
     */
    public function getDocumentsToSign(string $filter): Collection;

    public function sign(DocumentSignatureRequest $documentSignatureRequest): ?DocumentSignature;
}
