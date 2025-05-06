<?php

namespace Tests\Feature;

use App\Contracts\PublicKeyServiceInterface;
use App\Models\PublicKey;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicKeyControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testReturnsStatus200WithPublicKey(): void
    {
        $mockService = $this->mock(PublicKeyServiceInterface::class, function ($mock) {
            $mock->shouldReceive('getPublicKey')
                ->once()
                ->andReturn(new PublicKey([
                    'public_key' => 'test-public-key',
                    'expires_at' => now()->addDay(),
                ]));
        });

        $this->app->instance(PublicKeyServiceInterface::class, $mockService);

        $response = $this->getJson(route('api.v1.public-key.get-public-key'));

        $response->assertStatus(200)
            ->assertJsonPath('public_key', 'test-public-key');

        $responseData = $response->json();
        $expiresAt = Carbon::parse($responseData['expires_at']);
        $this->assertTrue($expiresAt->isFuture(), 'The expires_at date should be in the future');
    }

    public function testReturnsStatus500WhenPublicKeyUnavailable(): void
    {
        $mockService = $this->mock(PublicKeyServiceInterface::class, function ($mock) {
            $mock->shouldReceive('getPublicKey')
                ->once()
                ->andReturn(null);
        });

        $this->app->instance(PublicKeyServiceInterface::class, $mockService);

        $response = $this->getJson(route('api.v1.public-key.get-public-key'));

        $response->assertStatus(500)
            ->assertExactJson(['message' => 'Public keys are temporarily unavailable.']);
    }
}
