<?php

namespace App\Services;

use App\Contracts\SignatureServiceInterface;
use App\Models\Document;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use setasign\Fpdi\PdfParser\CrossReference\CrossReferenceException;
use setasign\Fpdi\PdfParser\Filter\FilterException;
use setasign\Fpdi\PdfParser\PdfParserException;
use setasign\Fpdi\PdfParser\Type\PdfTypeException;
use setasign\Fpdi\PdfReader\PdfReaderException;
use setasign\Fpdi\Tcpdf\Fpdi;

class SignatureService implements SignatureServiceInterface
{
    /**
     * @throws CrossReferenceException
     * @throws PdfReaderException
     * @throws PdfParserException
     * @throws FilterException
     * @throws PdfTypeException
     */
    public function signDocument(Document $document): string
    {
        $privateKeyPath = config('signature_keys.private_key_path');
        $certificatePath = config('signature_keys.certificate_path');

        $privateKey = $this->ensurePrivateKeyExists($privateKeyPath);
        $certificate = $this->ensureCertificateExists($certificatePath);

        return $this->createSignedPdf($document, $privateKey, $certificate);
    }

    private function ensurePrivateKeyExists(string $privateKeyPath): string
    {
        if (! Storage::exists($privateKeyPath)) {
            throw new RuntimeException('Private key file not found');
        }

        return Storage::get($privateKeyPath);
    }

    private function ensureCertificateExists(string $certificatePath): string
    {
        if (! Storage::exists($certificatePath)) {
            throw new RuntimeException('Certificate file not found');
        }

        return Storage::get($certificatePath);
    }

    /**
     * @throws CrossReferenceException
     * @throws PdfReaderException
     * @throws PdfParserException
     * @throws PdfTypeException
     * @throws FilterException
     */
    private function createSignedPdf(Document $document, string $privateKey, string $certificate): string
    {
        $pdf = new Fpdi();

        $pdf->setPrintHeader(false);

        $tempPath = 'temp/'.Str::uuid().'.pdf';
        $pdfContent = Storage::disk('s3')->get($document->path);
        Storage::disk('local')->put($tempPath, $pdfContent);

        $tempFullPath = Storage::disk('local')->path($tempPath);
        $tempDecompressedPath = Storage::disk('local')->path('temp/'.Str::uuid().'_decompressed.pdf');
        $this->decompressPdf($tempFullPath, $tempDecompressedPath);

        $pageCount = $pdf->setSourceFile($tempDecompressedPath);
        /** @var User $user */
        $user = auth()->user();

        for ($i = 1; $i <= $pageCount; $i++) {
            $tplIdx = $pdf->importPage($i);
            $size = $pdf->getTemplateSize($tplIdx);
            $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
            $pdf->useTemplate($tplIdx);

            if ($i === 1) {
                $pdf->SetFont('helvetica', 'B', 10);
                $pdf->SetX(10);
                $pdf->SetY(10);
                $pdf->Cell(0, 5, "Signed: {$user->name} / ".config('app.name'), 0, 1, 'L');
                $pdf->Cell(0, 5, 'Date: '.date('Y-m-d H:i:s'), 0, 1, 'L');
            }
        }

        $pdf->setSignature($certificate, $privateKey, '', '', 2, [
            'Name' => $user->name,
            'Reason' => 'Document approval',
            'ContactInfo' => $user->email,
        ]);

        $finalPdfContent = $pdf->Output('', 'S');
        Storage::disk('local')->delete($tempFullPath);
        Storage::disk('local')->delete($tempDecompressedPath);

        return $finalPdfContent;
    }

    public function decompressPdf(string $inputPath, string $outputPath): void
    {
        $command = "gs -sDEVICE=pdfwrite -dCompatibilityLevel=1.4 -dPDFSETTINGS=/screen -dNOPAUSE -dQUIET -dBATCH -sOutputFile=$outputPath $inputPath";
        shell_exec($command);
    }
}
