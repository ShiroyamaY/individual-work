<?php

namespace App\Jobs;

use App\Http\DTO\DocumentSignedDTO;
use App\Mail\DocumentSignedMail;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendDocumentSignedEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $email;

    protected DocumentSignedDTO $documentSignedDTO;

    public function __construct(string $email, DocumentSignedDTO $documentSignedDTO)
    {
        $this->email = $email;
        $this->documentSignedDTO = $documentSignedDTO;
    }

    /**
     * @throws Exception
     */
    public function handle(): void
    {
        try {
            Mail::to($this->email)->send(new DocumentSignedMail($this->documentSignedDTO));
            Log::info("Email sent to {$this->email} about the signing of the document '{$this->documentSignedDTO->requestId}'");
        } catch (Exception $e) {
            Log::error("Error sending email to {$this->email}: ".$e->getMessage());
            throw $e;
        }
    }
}
