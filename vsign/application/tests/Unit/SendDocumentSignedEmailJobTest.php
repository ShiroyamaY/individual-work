<?php

namespace Tests\Unit;

use App\Http\DTO\DocumentSignedDTO;
use App\Jobs\SendDocumentSignedEmailJob;
use App\Mail\DocumentSignedMail;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Tests\TestCase;

class SendDocumentSignedEmailJobTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @throws Exception
     */
    public function test_email_sent_successfully(): void
    {
        Mail::fake();
        $testEmail = 'test@example.com';
        $documentSignedDTO = new DocumentSignedDTO(Str::uuid(), 'document.pdf', 'http://example.com', now(), now()->addDay());

        Log::shouldReceive('info')
            ->once()
            ->with("Email sent to $testEmail about the signing of the document '$documentSignedDTO->requestId'");

        Log::shouldReceive('error')
            ->never();

        $job = new SendDocumentSignedEmailJob($testEmail, $documentSignedDTO);

        $job->handle();

        Mail::assertSent(DocumentSignedMail::class, static function ($mail) {
            return $mail->hasTo('test@example.com');
        });
    }

    public function test_email_send_failed(): void
    {
        Mail::fake();
        Mail::shouldReceive('to->send')->andThrow(new Exception('Failed to send email'));
        Log::shouldReceive('error')->once()->with('Error sending email to test@example.com: Failed to send email');

        $dto = new DocumentSignedDTO(Str::uuid(), 'document.pdf', 'http://example.com', now(), now()->addDay());
        $job = new SendDocumentSignedEmailJob('test@example.com', $dto);

        $this->expectException(Exception::class);

        $job->handle();
    }
}
