<?php

namespace Tests\Unit;

use App\Contracts\SignatureServiceInterface;
use App\Models\Document;
use App\Models\User;
use App\Services\SignatureService;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use OpenSSLAsymmetricKey;
use RuntimeException;
use setasign\Fpdi\PdfParser\CrossReference\CrossReferenceException;
use setasign\Fpdi\PdfParser\Filter\FilterException;
use setasign\Fpdi\PdfParser\PdfParserException;
use setasign\Fpdi\PdfParser\Type\PdfTypeException;
use setasign\Fpdi\PdfReader\PdfReaderException;
use Tests\TestCase;

class SignatureServiceTest extends TestCase
{
    use RefreshDatabase;

    private SignatureServiceInterface $signatureService;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->signatureService = new SignatureService();
        Storage::fake('local');
        Storage::fake('s3');
    }

    /**
     * @throws CrossReferenceException
     * @throws PdfReaderException
     * @throws PdfParserException
     * @throws PdfTypeException
     * @throws FilterException
     */
    public function testSignDocumentThrowsExceptionWhenPrivateKeyNotFound(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Private key file not found');

        config(['signature_keys.private_key_path' => 'non_existent_key.pem']);
        $document = Document::factory()->create();

        $this->signatureService->signDocument($document);
    }

    /**
     * @throws CrossReferenceException
     * @throws PdfReaderException
     * @throws PdfParserException
     * @throws FilterException
     * @throws PdfTypeException
     */
    public function testSignDocumentThrowsExceptionWhenCertificateNotFound(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Certificate file not found');

        config([
            'signature_keys.private_key_path' => 'private_key.pem',
            'signature_keys.certificate_path' => 'non_existent_cert.pem',
        ]);
        Storage::put('private_key.pem', 'private key content');

        $document = Document::factory()->create();

        $this->signatureService->signDocument($document);
    }

    /**
     * @throws CrossReferenceException
     * @throws PdfReaderException
     * @throws PdfParserException
     * @throws FilterException
     * @throws PdfTypeException
     */
    public function testSignDocumentReturnsPdfContentWhenSuccessful(): void
    {
        config([
            'signature_keys.private_key_path' => 'private_key.pem',
            'signature_keys.certificate_path' => 'certificate.crt',
        ]);

        $privateKey = $this->setPrivateKey();
        $this->setCertificate($privateKey);

        $user = User::factory()->create();

        $this->actingAs($user);

        $pdf = new \TCPDF();
        $pdf->AddPage();
        $pdf->SetFont('helvetica', '', 12);
        $pdf->Cell(0, 10, 'Hello, World!', 0, 1);
        $pdfContent = $pdf->Output('', 'S');
        $document = Document::factory()->create();
        Storage::disk('s3')->put($document->path, $pdfContent);

        $signatureServiceMock = $this->getMockBuilder(SignatureService::class)
            ->onlyMethods(['decompressPdf'])
            ->getMock();

        $signatureServiceMock->expects($this->once())
            ->method('decompressPdf')
            ->willReturnCallback(function ($inputPath, $outputPath) {
                copy($inputPath, $outputPath);
            });

        $result = $signatureServiceMock->signDocument($document);

        $this->assertNotEmpty($result);
    }

    private function setPrivateKey(): false|OpenSSLAsymmetricKey
    {
        $privateKey = openssl_pkey_new([
            'digest_alg' => 'sha256',
            'private_key_bits' => 1024,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ]);

        openssl_pkey_export($privateKey, $privateKeyString);

        Storage::put('private_key.pem', $privateKeyString);

        return $privateKey;
    }

    private function setCertificate(OpenSSLAsymmetricKey $privateKey): void
    {
        $config = config('signature_keys');

        $dn = $config['certificate_dn'];

        $csr = openssl_csr_new($dn, $privateKey, $config);
        if (! $csr) {
            throw new RuntimeException('Unable to create CSR');
        }

        $certificate = openssl_csr_sign($csr, null, $privateKey, 1, $config, time());
        if (! $certificate) {
            throw new RuntimeException('Unable to generate certificate');
        }

        openssl_x509_export($certificate, $certificatePem);

        Storage::put('certificate.crt', $certificatePem);
    }
}
