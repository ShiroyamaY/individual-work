<?php

namespace App\Http\Controllers\Api\V1;

use App\Contracts\PublicKeyServiceInterface;
use App\Http\Resources\Api\V1\PublicKeyResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Response;

readonly class PublicKeyController
{
    public function __construct(
        private PublicKeyServiceInterface $publicKeyService,
    ) {
    }

    public function getPublicKey(): PublicKeyResource | JsonResponse
    {
        $publicKey = $this->publicKeyService->getPublicKey();

        return $publicKey
            ? PublicKeyResource::make($publicKey)->response()->setStatusCode(200)
            : Response::json(['message' => 'Public keys are temporarily unavailable.'], 500);
    }
}
