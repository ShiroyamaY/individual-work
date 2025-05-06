<?php

namespace App\Services;

use App\Contracts\DocumentServiceInterface;
use App\Contracts\DocumentSignatureServiceInterface;
use App\Contracts\SignatureRequestServiceInterface;
use App\Contracts\SignatureServiceInterface;
use App\Http\DTO\Signature\SignatureInitializeDTO;
use App\Models\Document;
use App\Models\DocumentSignature;
use App\Models\DocumentSignatureRequest;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

readonly class DocumentSignatureService implements DocumentSignatureServiceInterface
{
    public function __construct(
        private DocumentServiceInterface $documentService,
        private SignatureRequestServiceInterface $signatureRequestService,
        private SignatureServiceInterface $signatureService,
    ) {
    }

    public function initializeSignatureRequest(SignatureInitializeDTO $initializeDTO): ?DocumentSignatureRequest
    {
        try {
            DB::beginTransaction();

            $document = $this->documentService->saveDocument($initializeDTO);
            if (! $document) {
                DB::rollBack();

                return null;
            }

            $signatureRequest = $this->signatureRequestService->initializeSignatureRequest($document);

            DB::commit();

            return $signatureRequest;
        } catch (Throwable $exception) {
            DB::rollBack();
            Log::error('Signature initialization failed: '.$exception->getMessage());

            return null;
        }
    }

    /**
     * Get documents that need to be signed.
     *
     * @return Collection<int, Document>
     */
    public function getDocumentsToSign(string $filter): Collection
    {
        $documentIds = $this->signatureRequestService
            ->getActiveSignRequests()
            ->pluck('document_id');

        return Document::query()
            ->whereIn('id', $documentIds)
            ->where('original_filename', 'LIKE', "%{$filter}%")
            ->get();
    }

    public function sign(DocumentSignatureRequest $documentSignatureRequest): ?DocumentSignature
    {
        try {
            $document = $documentSignatureRequest->document;
            $signedDocumentContent = $this->signatureService->signDocument($document);

            $signedDocumentPath = $this->documentService->storeSignedDocument($signedDocumentContent);
            $this->documentService->updateDocumentPath($document, $signedDocumentPath);

            $this->signatureRequestService->updateSignatureRequestStatusByDocumentId($document->getId());

            return $this->createDocumentSignature($documentSignatureRequest, $signedDocumentPath);
        } catch (Exception $exception) {
            if (! empty($signedDocumentPath)) {
                Storage::disk('s3')->delete($signedDocumentPath);
            }

            Log::error('Error during document signature: '.$exception->getMessage());

            return null;
        }
    }

    private function createDocumentSignature(DocumentSignatureRequest $documentSignatureRequest, string $signedDocumentPath): DocumentSignature
    {
        $documentSignature = new DocumentSignature([
            'request_id' => $documentSignatureRequest->id,
            'user_id' => Auth::id(),
            'signed_pdf_path' => $signedDocumentPath,
            'signed_at' => now(),
        ]);

        $documentSignature->save();

        return $documentSignature;
    }
}
