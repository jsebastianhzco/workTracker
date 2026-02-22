<?php

declare(strict_types=1);

require_once __DIR__ . '/../core/db.php';
require_once __DIR__ . '/../core/http.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    json_response(['ok' => false, 'error' => 'Method not allowed'], 405);
}

$body = request_json();
$email = trim((string)($body['email'] ?? ''));
$password = trim((string)($body['password'] ?? ''));

if ($email === '' || $password === '') {
    json_response(['ok' => false, 'error' => 'Email and password are required'], 422);
}

try {
    $stmt = db()->prepare('SELECT id, employee_id, role_id ,  email, password_hash FROM users WHERE email = :email LIMIT 1');
    $stmt->execute(['email' => $email]);
    $employee = $stmt->fetch();

    if (!$employee) {
        json_response(['ok' => false, 'error' => 'Invalid credentials'], 401);
    }

    $passwordHash = (string)($employee['password_hash'] ?? '');

    $validPassword = password_verify($password, $passwordHash) || hash_equals($passwordHash, $password);
    if (!$validPassword) {
        json_response(['ok' => false, 'error' => 'Invalid credentials'], 401);
    }

    $now = time();
    $payload = [
        'sub' => (int)$employee['id'],
        'email' => $employee['email'],
        'iat' => $now,
        'exp' => $now + JWT_EXP_SECONDS,
    ];

    $token = jwt_encode($payload);

    json_response([
        'ok' => true,
        'token' => $token,
        'user' => [
            'employee' => [
                'id' => (int)$employee['id'],
                'email' => $employee['email'],
            ],
        ],
    ]);
} catch (Throwable $e) {
    json_response(['ok' => false, 'error' => 'Server error', 'detail' => $e->getMessage()], 500);
}
