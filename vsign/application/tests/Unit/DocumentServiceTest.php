<?php

namespace Tests\Unit;

use App\Http\DTO\Signature\SignatureInitializeDTO;
use App\Http\Requests\Api\V1\CreateSignatureRequest;
use App\Models\Document;
use App\Services\DocumentService;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class DocumentServiceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<\Illuminate\Http\Testing\File>>
     */
    public static function documentDataProvider(): array
    {
        return [
            'documentFile' => [
                UploadedFile::fake()->create('document.pdf', 100),
                UploadedFile::fake()->create('document123.pdf', 2000),
            ],
        ];
    }

    #[DataProvider('documentDataProvider')]
    public function testSaveDocumentReturnsDocumentOnSuccess(UploadedFile $documentFile): void
    {
        $signatureInitializeDTO = $this->createInitializeDTO($documentFile);

        Storage::fake('s3');

        $documentService = new DocumentService();
        $result = $documentService->saveDocument($signatureInitializeDTO);

        $this->assertInstanceOf(Document::class, $result);
        $this->assertEquals($documentFile->getClientOriginalName(), $result->original_filename);
    }

    /**
     * @throws FileNotFoundException
     */
    #[DataProvider('documentDataProvider')]
    public function testSaveDocumentReturnsExistingDocument(UploadedFile $documentFile): void
    {
        $documentHash = hash('sha256', File::get($documentFile));
        $existingDocument = Document::factory()->create(['hash' => $documentHash]);

        $signatureInitializeDTO = $this->createInitializeDTO($documentFile);

        $documentService = new DocumentService();
        $result = $documentService->saveDocument($signatureInitializeDTO);

        $this->assertNotNull($result);
        $this->assertEquals($existingDocument->id, $result->id);
    }

    #[DataProvider('documentDataProvider')]
    public function testSaveDocumentReturnsNullOnStoreFailure(UploadedFile $documentFile): void
    {
        $signatureInitializeDTO = $this->createInitializeDTO($documentFile);

        Storage::shouldReceive('disk->put')->andReturn(false);

        $documentService = new DocumentService();
        $result = $documentService->saveDocument($signatureInitializeDTO);

        $this->assertNull($result);
    }

    private function createInitializeDTO(UploadedFile $documentFile): SignatureInitializeDTO
    {
        $request = new CreateSignatureRequest();
        $request->files->set('document', $documentFile);

        return SignatureInitializeDTO::fromRequest($request);
    }
}
