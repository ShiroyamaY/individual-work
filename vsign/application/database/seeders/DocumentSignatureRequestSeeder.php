<?php

namespace Database\Seeders;

use App\Models\DocumentSignatureRequest;
use Illuminate\Database\Seeder;

class DocumentSignatureRequestSeeder extends Seeder
{
    public function run(): void
    {
        DocumentSignatureRequest::factory()->count(1024)->create();
    }
}
