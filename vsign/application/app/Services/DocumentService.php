<?php

namespace App\Services;

use App\Contracts\DocumentServiceInterface;
use App\Http\DTO\Signature\SignatureInitializeDTO;
use App\Models\Document;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class DocumentService implements DocumentServiceInterface
{
    public const string UNSIGNED_DOCUMENTS_PATH = 'documents/unsigned';

    public const string SIGNED_DOCUMENTS_PATH = 'documents/signed';

    public function saveDocument(SignatureInitializeDTO $initializeDTO): ?Document
    {
        try {
            $documentFile = $initializeDTO->document;
            $documentHash = hash('sha256', File::get($documentFile));

            $documentStorePath = sprintf('%s/%s/%s.pdf', self::UNSIGNED_DOCUMENTS_PATH, substr($documentHash, 0, 2), $documentHash);

            $storedDocument = Document::query()->where('hash', $documentHash)->first();
            if ($storedDocument) {
                return $storedDocument;
            }

            $this->storeDocument($initializeDTO, $documentStorePath);

            return Document::query()->create([
                'path' => $documentStorePath,
                'original_filename' => $documentFile->getClientOriginalName(),
                'mime_type' => $documentFile->getMimeType(),
                'size' => $documentFile->getSize(),
                'hash' => $documentHash,
            ]);
        } catch (Throwable $exception) {
            Log::error('Document initialization failed: '.$exception->getMessage());

            return null;
        }
    }

    /**
     * Get documents by their IDs.
     *
     * @param Collection<int, int> $documentIds
     * @return Collection<int, Document>
     */
    public function getDocumentsByIds(Collection $documentIds): Collection
    {
        return Document::query()
            ->whereIn('id', $documentIds)
            ->get();
    }

    public function storeSignedDocument(string $pdfContent): string
    {
        $signedDocumentHash = hash('sha256', $pdfContent);
        $signedDocumentPath = sprintf('%s/%s/%s.pdf', self::SIGNED_DOCUMENTS_PATH, substr($signedDocumentHash, 0, 2), $signedDocumentHash);

        if (! Storage::disk('s3')->put($signedDocumentPath, $pdfContent)) {
            throw new RuntimeException('Failed to save signed document to S3');
        }

        return $signedDocumentPath;
    }

    public function updateDocumentPath(Document $document, string $signedDocumentPath): void
    {
        $document->update(['signed_path' => $signedDocumentPath]);
    }

    private function storeDocument(SignatureInitializeDTO $initializeDTO, string $documentStorePath): void
    {
        $document = $initializeDTO->document;
        $stored = Storage::disk('s3')->put($documentStorePath, File::get($document));

        if (! $stored) {
            throw new RuntimeException('The document was not saved.');
        }
    }
}
