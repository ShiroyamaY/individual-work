<?php

namespace App\Services;

use App\Contracts\PublicKeyServiceInterface;
use App\Http\DTO\KeyPairDTO;
use App\Models\PublicKey;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class PublicKeyService implements PublicKeyServiceInterface
{
    private int $expiresAt;

    private string $privateKeyPath;

    private string $certificatePath;

    public function __construct()
    {
        $this->expiresAt = is_int(config('signature_keys.expires_at')) ? config('signature_keys.expires_at') : 30;
        $this->privateKeyPath = is_string(config('signature_keys.private_key_path')) ? config('signature_keys.private_key_path') : 'keys/private_key';
        $this->certificatePath = is_string(config('signature_keys.certificate_path')) ? config('signature_keys.certificate_path') : 'crts/certificate.crt';
    }

    public function getPublicKey(): ?PublicKey
    {
        $publicKey = PublicKey::query()->latest()->first();

        if (! $publicKey || $publicKey->isExpired()) {
            try {
                $keysDTO = $this->generatePublicKey();

                $expiresAt = now()->addDays($this->expiresAt);

                $publicKey = new PublicKey();
                $publicKey
                    ->setPublicKey($keysDTO->publicKey)
                    ->setExpiresAt($expiresAt)
                    ->save();

                Storage::put($this->privateKeyPath, $keysDTO->privateKey);
                Storage::put($this->certificatePath, $this->generateCertificate($keysDTO->privateKey));
            } catch (RuntimeException $exception) {
                Log::error($exception->getMessage());

                return null;
            }
        }

        return $publicKey;
    }

    public function generatePublicKey(): KeyPairDTO
    {
        $config = config('signature_keys');

        $resource = openssl_pkey_new((array) $config);
        if ($resource) {
            openssl_pkey_export($resource, $privateKey);
            $keyDetails = openssl_pkey_get_details($resource);

            if ($keyDetails === false) {
                throw new RuntimeException('Unable to retrieve key details');
            }

            return new KeyPairDTO($privateKey, $keyDetails['key']);
        }

        throw new RuntimeException('Unable to generate public key');
    }

    private function generateCertificate(string $privateKey): string
    {
        $config = config('signature_keys');

        $dn = $config['certificate_dn'];

        $csr = openssl_csr_new($dn, $privateKey, $config);
        if (! $csr) {
            throw new RuntimeException('Unable to create CSR');
        }

        $certificate = openssl_csr_sign($csr, null, $privateKey, $this->expiresAt, $config, time());
        if (! $certificate) {
            throw new RuntimeException('Unable to generate certificate');
        }

        openssl_x509_export($certificate, $certificatePem);

        return $certificatePem;
    }
}
