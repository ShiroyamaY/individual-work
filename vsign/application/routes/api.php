<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\DocumentSignatureController;
use App\Http\Controllers\Api\V1\PublicKeyController;
use App\Http\Controllers\Api\V1\UserController;
use Illuminate\Support\Facades\Route;

Route::get('public-key', [PublicKeyController::class, 'getPublicKey'])->name('public-key.get-public-key');
Route::post('sign-request', [DocumentSignatureController::class, 'initialize'])->name('signature-request.initialize');
Route::get('total-users', [UserController::class, 'getTotalUsers'])->name('total-users.get');
Route::get('documents-signed', [DocumentSignatureController::class, 'signedDocumentsCount'])->name('document-signed.get');

Route::middleware('guest:sanctum')->group(function () {
    Route::prefix('auth')
        ->as('auth.')
        ->group(function () {
            Route::get('github/redirect', [AuthController::class, 'redirectToGithub'])->name('redirect-to-github');
            Route::get('github/callback', [AuthController::class, 'handleGithubCallback'])->name('handle-github-callback');
        });
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('user', [AuthController::class, 'user'])->name('auth.user');

    Route::prefix('documents')
        ->as('documents.')
        ->group(function () {
            Route::get('to-sign', [DocumentSignatureController::class, 'documentsToSign'])->name('to-sign');
            Route::post('{document}/sign', [DocumentSignatureController::class, 'sign'])->name('sign');
        });
});
