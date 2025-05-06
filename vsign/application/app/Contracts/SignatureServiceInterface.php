<?php

namespace App\Contracts;

use App\Models\Document;

interface SignatureServiceInterface
{
    public function signDocument(Document $document): string;

    public function decompressPdf(string $inputPath, string $outputPath): void;
}
