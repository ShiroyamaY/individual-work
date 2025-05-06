<?php

namespace Tests\Unit;

use App\Enums\SignatureRequestStatus;
use App\Models\Document;
use App\Models\DocumentSignatureRequest;
use App\Services\DocumentSignatureRequestService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DocumentSignatureRequestServiceTest extends TestCase
{
    use RefreshDatabase;

    private DocumentSignatureRequestService $signatureRequestService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->signatureRequestService = new DocumentSignatureRequestService();
    }

    public function testInitializeSignatureRequestCreatesNewRequestWhenNoneExists(): void
    {
        $document = Document::factory()->create();

        $signatureRequest = $this->signatureRequestService->initializeSignatureRequest($document);

        $this->assertEquals($document->getId(), $signatureRequest->document_id);
        $this->assertEquals(SignatureRequestStatus::PENDING, $signatureRequest->status);
    }

    public function testInitializeSignatureRequestReturnsExistingRequestWhenOneExists(): void
    {
        $document = Document::factory()->create();
        $existingRequest = DocumentSignatureRequest::factory()->create(['document_id' => $document->getId()]);

        $signatureRequest = $this->signatureRequestService->initializeSignatureRequest($document);

        $this->assertEquals($existingRequest->id, $signatureRequest->id);
    }

    public function testGetActiveSignRequestsReturnsPendingRequests(): void
    {
        DocumentSignatureRequest::query()->delete();
        DocumentSignatureRequest::factory()->count(3)->create(['status' => SignatureRequestStatus::PENDING]);
        DocumentSignatureRequest::factory()->count(2)->create(['status' => SignatureRequestStatus::COMPLETED]);

        $activeRequests = $this->signatureRequestService->getActiveSignRequests();

        $this->assertEquals(3, $activeRequests->count());
    }

    public function testUpdateSignatureRequestStatusByDocumentIdUpdatesStatus(): void
    {
        $document = Document::factory()->create();
        DocumentSignatureRequest::factory()->create(['document_id' => $document->getId()]);

        $this->signatureRequestService->updateSignatureRequestStatusByDocumentId($document->getId());

        $updatedRequest = DocumentSignatureRequest::query()->where('document_id', $document->getId())->first();
        $this->assertEquals(SignatureRequestStatus::COMPLETED, $updatedRequest->status);
    }
}
