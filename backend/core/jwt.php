<?php

declare(strict_types=1);

require_once __DIR__ . '/config.php';

function b64url_encode(string $data): string
{
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

function b64url_decode(string $data): string
{
    $remainder = strlen($data) % 4;
    if ($remainder > 0) {
        $data .= str_repeat('=', 4 - $remainder);
    }
    return base64_decode(strtr($data, '-_', '+/')) ?: '';
}

function jwt_encode(array $payload): string
{
    $header = ['typ' => 'JWT', 'alg' => JWT_ALGO];

    $segments = [
        b64url_encode(json_encode($header, JSON_UNESCAPED_SLASHES) ?: '{}'),
        b64url_encode(json_encode($payload, JSON_UNESCAPED_SLASHES) ?: '{}'),
    ];

    $signature = hash_hmac('sha256', implode('.', $segments), JWT_SECRET, true);
    $segments[] = b64url_encode($signature);

    return implode('.', $segments);
}

function jwt_decode(string $jwt): ?array
{
    $parts = explode('.', $jwt);
    if (count($parts) !== 3) {
        return null;
    }

    [$encodedHeader, $encodedPayload, $encodedSignature] = $parts;

    $headerJson = b64url_decode($encodedHeader);
    $payloadJson = b64url_decode($encodedPayload);
    $signature = b64url_decode($encodedSignature);

    $header = json_decode($headerJson, true);
    $payload = json_decode($payloadJson, true);

    if (!is_array($header) || !is_array($payload)) {
        return null;
    }

    if (($header['alg'] ?? null) !== JWT_ALGO) {
        return null;
    }

    $validSignature = hash_hmac('sha256', $encodedHeader . '.' . $encodedPayload, JWT_SECRET, true);
    if (!hash_equals($validSignature, $signature)) {
        return null;
    }

    $now = time();
    if (isset($payload['exp']) && (int)$payload['exp'] < $now) {
        return null;
    }

    return $payload;
}
