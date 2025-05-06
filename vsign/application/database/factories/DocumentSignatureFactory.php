<?php

namespace Database\Factories;

use App\Models\DocumentSignature;
use App\Models\DocumentSignatureRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DocumentSignature>
 */
class DocumentSignatureFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'request_id' => DocumentSignatureRequest::factory(),
            'user_id' => User::factory(),
            'signed_pdf_path' => $this->faker->filePath(),
            'signed_at' => $this->faker->dateTime(),
        ];
    }
}
