<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Contracts\DocumentServiceInterface;
use App\Contracts\SignatureRequestServiceInterface;
use App\Contracts\SignatureServiceInterface;
use App\Http\DTO\Signature\SignatureInitializeDTO;
use App\Models\Document;
use App\Models\DocumentSignatureRequest;
use App\Services\DocumentSignatureService;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\MockObject\Exception as MockException;
use Tests\TestCase;

final class DocumentSignatureServiceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @throws MockException
     */
    public function testInitializeReturnsNullWhenDocumentSaveFails(): void
    {
        $initializeDTO = $this->createMock(SignatureInitializeDTO::class);

        $documentService = $this->createDocumentServiceMock();

        $signatureRequestService = $this->createMock(SignatureRequestServiceInterface::class);

        $signatureService = $this->createMock(SignatureServiceInterface::class);

        $documentSignatureService = new DocumentSignatureService($documentService, $signatureRequestService, $signatureService);
        $this->assertNull($documentSignatureService->initializeSignatureRequest($initializeDTO));
    }

    /**
     * @throws MockException
     */
    public function testInitializeReturnsSignatureRequestWhenDocumentIsSuccessfullySaved(): void
    {
        $document = Document::factory()->create();
        $signatureRequest = DocumentSignatureRequest::factory()->create(['document_id' => $document->getId()]);
        $documentService = $this->createDocumentServiceMock($document);
        $signatureRequestService = $this->createSignatureRequestServiceMock($signatureRequest);
        $signatureService = $this->createMock(SignatureServiceInterface::class);

        $documentSignatureService = new DocumentSignatureService($documentService, $signatureRequestService, $signatureService);

        $initializeDTO = $this->createMock(SignatureInitializeDTO::class);
        $result = $documentSignatureService->initializeSignatureRequest($initializeDTO);

        $this->assertNotNull($result);
        $this->assertEquals($signatureRequest->id, $result->id);
    }

    /**
     * @throws MockException
     */
    private function createDocumentServiceMock(?Document $document = null): DocumentServiceInterface
    {
        $documentService = $this->createMock(DocumentServiceInterface::class);
        $documentService->method('saveDocument')->willReturn($document);

        return $documentService;
    }

    /**
     * @throws MockException
     */
    private function createSignatureRequestServiceMock(DocumentSignatureRequest $signatureRequest): SignatureRequestServiceInterface
    {
        $signatureRequestService = $this->createMock(SignatureRequestServiceInterface::class);
        $signatureRequestService->method('initializeSignatureRequest')->willReturn($signatureRequest);

        return $signatureRequestService;
    }

    /**
     * @throws MockException
     */
    public function testGetDocumentsToSignReturnsCollection(): void
    {
        $document = Document::factory()->create();
        $signatureRequestService = $this->createMock(SignatureRequestServiceInterface::class);
        $signatureRequestService->method('getActiveSignRequests')->willReturn(collect([new DocumentSignatureRequest(['document_id' => $document->id])]));

        $documentSignatureService = new DocumentSignatureService(
            $this->createMock(DocumentServiceInterface::class),
            $signatureRequestService,
            $this->createMock(SignatureServiceInterface::class)
        );

        $result = $documentSignatureService->getDocumentsToSign();

        $this->assertEquals($document->id, $result->first()->id);
    }

    /**
     * @throws MockException
     */
    public function testSignReturnsNullOnFailure(): void
    {
        $document = Document::factory()->create();
        $documentSignatureRequest = DocumentSignatureRequest::factory()->create(['document_id' => $document->getId()]);
        $signatureService = $this->createMock(SignatureServiceInterface::class);
        $signatureService->method('signDocument')->willThrowException(new Exception('Signing failed'));

        $documentService = $this->createMock(DocumentServiceInterface::class);
        $documentService->method('storeSignedDocument')->willReturn('path/to/signed/document.pdf');

        $signatureRequestService = $this->createMock(SignatureRequestServiceInterface::class);

        $documentSignatureService = new DocumentSignatureService(
            $documentService,
            $signatureRequestService,
            $signatureService
        );

        $result = $documentSignatureService->sign($documentSignatureRequest);
        $this->assertNull($result);
    }
}
