<?php

namespace Database\Factories;

use App\Models\Document;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Document>
 */
class DocumentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'path' => fake()->filePath(),
            'original_filename' => fake()->name().'.pdf',
            'mime_type' => fake()->mimeType(),
            'size' => fake()->numberBetween(1000, 5000000),
            'hash' => fake()->sha256(),
        ];
    }
}
