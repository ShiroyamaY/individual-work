<?php

namespace Database\Factories;

use App\Models\PublicKey;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PublicKey>
 */
class PublicKeyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'public_key' => $this->faker->text(100),
            'expires_at' => $this->faker->dateTime(),
        ];
    }
}
