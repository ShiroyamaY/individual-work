<?php

namespace App\Models;

use App\Enums\SignatureRequestStatus;
use Database\Factories\DocumentSignatureRequestFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $id
 * @property int $document_id
 * @property Document $document
 * @property SignatureRequestStatus $status
 */
class DocumentSignatureRequest extends Model
{
    /** @use HasFactory<DocumentSignatureRequestFactory> */
    use HasFactory;

    use HasUuids;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'document_id',
        'status',
    ];

    protected $keyType = 'string';

    public $incrementing = false;

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'status' => SignatureRequestStatus::class,
    ];

    /**
     * @return BelongsTo<Document, $this>
     */
    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function getId(): string
    {
        return $this->id;
    }
}
