<?php

namespace App\Contracts;

use App\Http\DTO\KeyPairDTO;
use App\Models\PublicKey;

interface PublicKeyServiceInterface
{
    public function getPublicKey(): ?PublicKey;

    public function generatePublicKey(): KeyPairDTO;
}
