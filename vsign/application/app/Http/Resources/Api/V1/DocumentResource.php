<?php

namespace App\Http\Resources\Api\V1;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property int $id
 * @property string $original_filename
 * @property int $size
 * @property string $path
 * @property Carbon $created_at
 */
class DocumentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->original_filename,
            'size' => $this->size,
            'path' => $this->path,
            'createdAt' => $this->created_at,
        ];
    }
}
