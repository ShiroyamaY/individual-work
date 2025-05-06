<?php

namespace App\Contracts;

use App\Http\DTO\Signature\SignatureInitializeDTO;
use App\Models\Document;

interface DocumentServiceInterface
{
    public function saveDocument(SignatureInitializeDTO $initializeDTO): ?Document;

    public function storeSignedDocument(string $pdfContent): string;

    public function updateDocumentPath(Document $document, string $signedDocumentPath): void;
}
