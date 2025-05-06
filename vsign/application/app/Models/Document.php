<?php

namespace App\Models;

use Carbon\Carbon;
use Database\Factories\DocumentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int $id
 * @property string $path
 * @property string $original_filename
 * @property string $mime_type
 * @property int $size
 * @property string $hash
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property DocumentSignatureRequest $signatureRequest
 */
class Document extends Model
{
    /** @use HasFactory<DocumentFactory> */
    use HasFactory;

    protected $fillable = [
        'path',
        'original_filename',
        'mime_type',
        'size',
        'hash',
    ];

    public function getId(): int
    {
        return $this->id;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function setPath(string $path): self
    {
        $this->path = $path;

        return $this;
    }

    public function getOriginalFilename(): string
    {
        return $this->original_filename;
    }

    public function setOriginalFilename(string $originalFilename): self
    {
        $this->original_filename = $originalFilename;

        return $this;
    }

    public function getMimeType(): string
    {
        return $this->mime_type;
    }

    public function setMimeType(string $mimeType): self
    {
        $this->mime_type = $mimeType;

        return $this;
    }

    public function getSize(): int
    {
        return $this->size;
    }

    public function getCreatedAt(): Carbon
    {
        return $this->created_at;
    }

    public function getUpdatedAt(): Carbon
    {
        return $this->updated_at;
    }

    /**
     * @return HasOne<DocumentSignatureRequest, $this>
     */
    public function signatureRequest(): HasOne
    {
        return $this->hasOne(DocumentSignatureRequest::class);
    }
}
