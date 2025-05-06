<?php

return [
    'private_key_bits' => 2048,
    'private_key_type' => OPENSSL_KEYTYPE_RSA,
    'private_key_path' => 'keys/private_key.pem',
    'certificate_path' => 'crts/certificate.crt',
    'expires_at' => 30,
    'certificate_dn' => [
        'countryName' => env('CERT_COUNTRY_NAME', 'MD'),
        'stateOrProvinceName' => env('CERT_STATE_OR_PROVINCE_NAME', 'Chisinau'),
        'localityName' => env('CERT_LOCALITY_NAME', 'Chisinau'),
        'organizationName' => env('CERT_ORGANIZATION_NAME', 'Vsign'),
        'organizationalUnitName' => env('CERT_ORGANIZATIONAL_UNIT_NAME', 'Vsign'),
        'commonName' => env('CERT_COMMON_NAME', 'vsign.localdev.me'),
        'emailAddress' => env('CERT_EMAIL_ADDRESS', 'vsign.certs@vsign.com'),
    ],
];
