<?php

namespace App\Http\DTO;

use App\Models\DocumentSignature;
use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Support\Facades\Storage;

readonly class DocumentSignedDTO
{
    public function __construct(
        public string $requestId,
        public string $originalFilename,
        public string $tempUrl,
        public Carbon $signedAt,
        public string $urlExpiresAt,
    ) {
    }

    public static function fromDocumentSignature(DocumentSignature $documentSignature): self
    {
        $disk = Storage::disk('s3_external');
        $signedPdfPath = $documentSignature->getSignedPdfPath();
        $urlExpiresAt = now()->addDay();
        $temporaryUrl = $disk->temporaryUrl($signedPdfPath, $urlExpiresAt);

        return new self(
            requestId: $documentSignature->request->getId(),
            originalFilename: $documentSignature->request->document->getOriginalFilename(),
            tempUrl: $temporaryUrl,
            signedAt: $documentSignature->getSignedAt(),
            urlExpiresAt: $urlExpiresAt
        );
    }

    /**
     * @return array{
     *     request_id: int|string,
     *     title: string,
     *     temp_url: string,
     *     signed_at: string|DateTimeInterface
     * }
     */
    public function toArray(): array
    {
        return [
            'request_id' => $this->requestId,
            'title' => $this->originalFilename,
            'temp_url'  => $this->tempUrl,
            'signed_at' => $this->signedAt,
            'url_expires_at' => $this->urlExpiresAt,
        ];
    }
}
