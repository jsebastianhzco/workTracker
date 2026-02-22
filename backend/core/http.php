<?php

declare(strict_types=1);

require_once __DIR__ . '/jwt.php';

function json_response(array $data, int $statusCode = 200): void
{
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function request_json(): array
{
    $raw = file_get_contents('php://input') ?: '';
    if ($raw === '') {
        return [];
    }

    $decoded = json_decode($raw, true);
    return is_array($decoded) ? $decoded : [];
}

function bearer_token(): ?string
{
    $header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    if ($header === '') {
        return null;
    }

    if (preg_match('/Bearer\s+(.*)$/i', $header, $matches) !== 1) {
        return null;
    }

    return trim($matches[1]);
}

function require_auth_payload(): array
{
    $token = bearer_token();
    if (!$token) {
        json_response(['ok' => false, 'error' => 'Missing bearer token'], 401);
    }

    $payload = jwt_decode($token);
    if (!$payload) {
        json_response(['ok' => false, 'error' => 'Invalid or expired token'], 401);
    }

    return $payload;
}
