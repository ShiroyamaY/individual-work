<?php

namespace Database\Seeders;

use App\Enums\SignatureRequestStatus;
use App\Models\Document;
use App\Models\DocumentSignatureRequest;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DocumentSignatureRequestSeeder extends Seeder
{
    public function run(): void
    {
        DocumentSignatureRequest::factory()->count(1024)->create();
    }
}
