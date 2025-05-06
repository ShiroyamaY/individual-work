<x-mail::message>
# Document Signed

A document has been signed on your behalf.

**Document Details:**
- Document: {{ $documentName }}
- Signed at: {{ $signedAt }}

<x-mail::button :url="$tempUrl">
    Download Document
</x-mail::button>

This download link will expire on {{ $urlExpiresAt }}.

If you did not expect this document to be signed, please contact our support team immediately.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
