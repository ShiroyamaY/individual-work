<?php

namespace App\Providers;

use App\Contracts\DocumentServiceInterface;
use App\Contracts\DocumentSignatureServiceInterface;
use App\Contracts\PublicKeyServiceInterface;
use App\Contracts\SignatureRequestServiceInterface;
use App\Contracts\SignatureServiceInterface;
use App\Services\DocumentService;
use App\Services\DocumentSignatureRequestService;
use App\Services\DocumentSignatureService;
use App\Services\PublicKeyService;
use App\Services\SignatureService;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(
            PublicKeyServiceInterface::class,
            PublicKeyService::class
        );

        $this->app->singleton(
            SignatureRequestServiceInterface::class,
            DocumentSignatureRequestService::class
        );

        $this->app->singleton(
            DocumentServiceInterface::class,
            DocumentService::class
        );

        $this->app->singleton(
            DocumentSignatureServiceInterface::class,
            DocumentSignatureService::class
        );

        $this->app->singleton(
            SignatureServiceInterface::class,
            SignatureService::class
        );
    }

    public function boot(): void
    {
        JsonResource::withoutWrapping();
    }
}
