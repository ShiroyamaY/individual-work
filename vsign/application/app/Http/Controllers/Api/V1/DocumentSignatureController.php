<?php

namespace App\Http\Controllers\Api\V1;

use App\Contracts\DocumentSignatureServiceInterface;
use App\Http\DTO\Signature\SignatureInitializeDTO;
use App\Http\Requests\Api\V1\CreateSignatureRequest;
use App\Http\Resources\Api\V1\DocumentCollection;
use App\Http\Resources\Api\V1\SignatureRequestResource;
use App\Jobs\SignDocumentJob;
use App\Models\Document;
use App\Models\DocumentSignatureRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

readonly class DocumentSignatureController
{
    public function __construct(
       private DocumentSignatureServiceInterface $documentSignatureService
    ) {
    }

    public function initialize(CreateSignatureRequest $request): SignatureRequestResource | JsonResponse
    {
        $signatureRequest = $this->documentSignatureService->initializeSignatureRequest(
            SignatureInitializeDTO::fromRequest($request)
        );

        return $signatureRequest
            ? SignatureRequestResource::make($signatureRequest)
            : Response::json(['message' => 'Failed to initialize signature request.'], 500);
    }

    /**
     * Get documents that need to be signed.
     *
     * @return DocumentCollection
     */
    public function documentsToSign(Request $request): DocumentCollection
    {
        $validated = $request->validate([
            'filter' => 'nullable|string',
        ]);

        $filter = $validated['filter'] ?? '';

        $documents = $this->documentSignatureService->getDocumentsToSign($filter);

        return DocumentCollection::make($documents);
    }

    public function sign(Request $request, Document $document): JsonResponse
    {
        SignDocumentJob::dispatch($document->signatureRequest);

        return Response::json([
            'message' => 'The document is signed, you will be notified by mail.',
        ], 202);
    }

    public function signedDocumentsCount(): int
    {
        return DocumentSignatureRequest::query()->where('status', 'completed')->count();
    }
}
