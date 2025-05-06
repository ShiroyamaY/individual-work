<?php

declare(strict_types=1);

namespace Tests\Unit\Jobs;

use App\Jobs\SignDocumentJob;
use App\Models\Document;
use App\Models\DocumentSignature;
use App\Models\DocumentSignatureRequest;
use App\Services\DocumentSignatureService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\MockObject\Exception;
use Tests\TestCase;

class SignDocumentJobTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @throws Exception
     */
    public function testJobProcessesDocumentSigningSuccessfully(): void
    {
        Storage::fake('s3');

        $document = Document::factory()->create();
        $documentSignatureRequest = DocumentSignatureRequest::factory()->create(['document_id' => $document->getId()]);
        $documentSignatureService = $this->createMock(DocumentSignatureService::class);

        $documentSignatureService->expects($this->once())
            ->method('sign')
            ->with($documentSignatureRequest)
            ->willReturn(DocumentSignature::factory()->create(['request_id' => $documentSignatureRequest->id]));

        $job = new SignDocumentJob($documentSignatureRequest);
        $job->handle($documentSignatureService);

        $this->assertDatabaseHas('document_signatures', ['request_id' => $documentSignatureRequest->id]);
    }

    /**
     * @throws Exception
     */
    public function testJobHandlesSigningFailure(): void
    {
        Storage::fake('s3');
        $document = Document::factory()->create();
        $documentSignatureRequest = DocumentSignatureRequest::factory()->create(['document_id' => $document->getId()]);
        $documentSignatureService = $this->createMock(DocumentSignatureService::class);

        $documentSignatureService->expects($this->once())
            ->method('sign')
            ->with($documentSignatureRequest)
            ->willReturn(null);

        $job = new SignDocumentJob($documentSignatureRequest);
        $job->handle($documentSignatureService);

        $this->assertDatabaseMissing('document_signatures', ['request_id' => $documentSignatureRequest->id]);
    }
}
