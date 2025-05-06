<?php

namespace App\Models;

use Carbon\Carbon;
use Database\Factories\DocumentSignatureFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $request_id
 * @property int $user_id
 * @property string $signed_pdf_path
 * @property Carbon $signed_at
 * @property DocumentSignatureRequest $request
 */
class DocumentSignature extends Model
{
    /** @use HasFactory<DocumentSignatureFactory> */
    use HasFactory;

    protected $fillable = [
        'request_id',
        'user_id',
        'signed_pdf_path',
        'signed_at',
    ];

    public $timestamps = false;

    protected $casts = [
        'signed_at' => 'datetime',
    ];

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function getSignedPdfPath(): string
    {
        return $this->signed_pdf_path;
    }

    /**
     * @return BelongsTo<DocumentSignatureRequest, $this>
     */
    public function request(): BelongsTo
    {
        return $this->belongsTo(DocumentSignatureRequest::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getSignedAt(): ?Carbon
    {
        return $this->signed_at;
    }
}
