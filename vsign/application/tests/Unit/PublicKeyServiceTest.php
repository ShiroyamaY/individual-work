<?php

namespace Tests\Unit;

use App\Models\PublicKey;
use App\Services\PublicKeyService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use RuntimeException;
use Tests\TestCase;

class PublicKeyServiceTest extends TestCase
{
    use RefreshDatabase;

    public function testGetPublicKeyReturnsExistingKeyIfNotExpired(): void
    {
        $publicKey = PublicKey::factory()->create(['expires_at' => now()->addDays(10)]);
        $service = new PublicKeyService();

        $result = $service->getPublicKey();

        $this->assertEquals($publicKey->id, $result->id);
    }

    public function testGetPublicKeyGeneratesNewKeyIfExpired(): void
    {
        $publicKey = PublicKey::factory()->create(['expires_at' => now()->subDays(10)]);
        $service = new PublicKeyService();

        $result = $service->getPublicKey();

        $this->assertNotEquals($publicKey->id, $result->id);
        $this->assertFalse($result->isExpired());
    }

    public function testGetPublicKeyReturnsNullOnError(): void
    {
        $mock = Mockery::mock(PublicKeyService::class)->makePartial();
        $mock->shouldReceive('generatePublicKey')->andThrow(new RuntimeException('Error'));

        $result = $mock->getPublicKey();

        $this->assertNull($result);
    }
}
