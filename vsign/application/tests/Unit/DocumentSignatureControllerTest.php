<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Contracts\DocumentSignatureServiceInterface;
use App\Http\Controllers\Api\V1\DocumentSignatureController;
use App\Http\Requests\Api\V1\CreateSignatureRequest;
use App\Http\Resources\Api\V1\SignatureRequestResource;
use App\Models\Document;
use App\Models\DocumentSignatureRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\UploadedFile;
use PHPUnit\Framework\MockObject\Exception;
use Tests\TestCase;

class DocumentSignatureControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @throws Exception
     */
    public function testInitializeReturnsSignatureRequestResourceOnSuccess(): void
    {
        $signatureService = $this->createMock(DocumentSignatureServiceInterface::class);
        $signatureRequest = new DocumentSignatureRequest();
        $signatureService->method('initializeSignatureRequest')->willReturn($signatureRequest);

        $controller = new DocumentSignatureController($signatureService);

        $request = new CreateSignatureRequest();
        $request->files->set('document', UploadedFile::fake()->create('document.pdf', 100));

        $response = $controller->initialize($request);

        $this->assertInstanceOf(SignatureRequestResource::class, $response);
        $this->assertEquals($signatureRequest, $response->resource);
    }

    /**
     * @throws Exception
     */
    public function testInitializeReturnsJsonResponseOnFailure(): void
    {
        $documentSignatureService = $this->createMock(DocumentSignatureServiceInterface::class);
        $documentSignatureService->method('initializeSignatureRequest')->willReturn(null);

        $controller = new DocumentSignatureController($documentSignatureService);

        $request = new CreateSignatureRequest();
        $request->files->set('document', UploadedFile::fake()->create('document.pdf', 100));

        $response = $controller->initialize($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(500, $response->getStatusCode());
        $this->assertEquals(['message' => 'Failed to initialize signature request.'], $response->getData(true));
    }

    /**
     * @throws Exception
     */
    public function testDocumentsToSignReturnsDocumentCollection(): void
    {
        $documentCollection = Document::factory()->count(3)->create();
        $documentSignatureService = $this->createMock(DocumentSignatureServiceInterface::class);
        $documentSignatureService->method('getDocumentsToSign')->willReturn($documentCollection);

        $controller = new DocumentSignatureController($documentSignatureService);

        $response = $controller->documentsToSign();

        $this->assertEquals($documentCollection->pluck('id'), $response->collection->pluck('id'));
    }
}
