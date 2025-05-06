<?php

namespace App\Http\DTO;

final readonly class KeyPairDTO
{
    public function __construct(
        public string $privateKey,
        public string $publicKey,
    ) {
    }
}
