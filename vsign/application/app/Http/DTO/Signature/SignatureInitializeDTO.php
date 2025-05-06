<?php

namespace App\Http\DTO\Signature;

use App\Http\Requests\Api\V1\CreateSignatureRequest;
use Illuminate\Http\UploadedFile;

readonly class SignatureInitializeDTO
{
    private function __construct(
        public UploadedFile $document,
    ) {
    }

    public static function fromRequest(CreateSignatureRequest $request): self
    {
        /** @var UploadedFile $document */
        $document = $request->file('document');

        return new self(
            $document,
        );
    }
}
