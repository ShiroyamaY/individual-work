<?php

namespace App\Models;

use Carbon\Carbon;
use Database\Factories\PublicKeyFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property Carbon $expires_at
 * @property string $public_key
 */
class PublicKey extends Model
{
    /** @use HasFactory<PublicKeyFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'public_key',
        'expires_at',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function getPublicKey(): string
    {
        return $this->public_key;
    }

    public function setPublicKey(string $publicKey): self
    {
        $this->public_key = $publicKey;

        return $this;
    }

    public function getExpiresAt(): Carbon
    {
        return $this->expires_at;
    }

    public function setExpiresAt(Carbon $expiresAt): self
    {
        $this->expires_at = $expiresAt;

        return $this;
    }
}
