<?php

namespace App\Http\Resources\Api\V1;

use App\Enums\SignatureRequestStatus;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property string $id
 * @property SignatureRequestStatus $status
 */
class SignatureRequestResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'request_id' => $this->id,
            'status' => $this->status,
        ];
    }
}
