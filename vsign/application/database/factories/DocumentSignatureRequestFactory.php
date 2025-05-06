<?php

namespace Database\Factories;

use App\Enums\SignatureRequestStatus;
use App\Models\Document;
use App\Models\DocumentSignatureRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DocumentSignatureRequest>
 */
class DocumentSignatureRequestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => $this->faker->uuid(),
            'document_id' => Document::factory()->create()->getId(),
            'status' => $this->faker->randomElement([
                SignatureRequestStatus::PENDING,
                SignatureRequestStatus::COMPLETED,
                SignatureRequestStatus::REJECTED,
            ]),
        ];
    }
}
