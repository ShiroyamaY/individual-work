<?php

namespace App\Mail;

use App\Http\DTO\DocumentSignedDTO;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DocumentSignedMail extends Mailable
{
    use Queueable, SerializesModels;

    public DocumentSignedDTO $documentSignedDTO;

    public function __construct(DocumentSignedDTO $documentSignedDTO)
    {
        $this->documentSignedDTO = $documentSignedDTO;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Document Signed Mail',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.documents.signed',
            with: [
                'documentName' => $this->documentSignedDTO->originalFilename,
                'tempUrl' => $this->documentSignedDTO->tempUrl,
                'signedAt' => $this->documentSignedDTO->signedAt,
                'urlExpiresAt' => $this->documentSignedDTO->urlExpiresAt,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
