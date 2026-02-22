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
    $stmt = db()->prepare(
        'SELECT
            u.id AS user_id,
            u.password_hash,
            u.role_id,
            e.id AS employee_id,
            e.first_name,
            e.last_name,
            e.email,
            e.hire_date,
            e.is_active
         FROM users u
         INNER JOIN employees e ON e.id = u.employee_id
         WHERE u.email = :email
         LIMIT 1'
    );
    $stmt->execute(['email' => $email]);
    $row = $stmt->fetch();

    if (!$row) {
        json_response(['ok' => false, 'error' => 'Invalid credentials'], 401);
    }

    if ((int)($row['is_active'] ?? 0) !== 1) {
        json_response(['ok' => false, 'error' => 'User is inactive'], 403);
    }

    $passwordHash = (string)($row['password_hash'] ?? '');

    $validPassword = password_verify($password, $passwordHash) || hash_equals($passwordHash, $password);
    if (!$validPassword) {
        json_response(['ok' => false, 'error' => 'Invalid credentials'], 401);
    }

    $now = time();
    $payload = [
        'sub' => (int)$row['employee_id'],
        'uid' => (int)$row['user_id'],
        'role_id' => (int)$row['role_id'],
        'email' => $row['email'],
        'iat' => $now,
        'exp' => $now + JWT_EXP_SECONDS,
    ];

    $token = jwt_encode($payload);

    json_response([
        'ok' => true,
        'token' => $token,
        'user' => [
            'employee' => [
                'id' => (int)$row['employee_id'],
                'first_name' => $row['first_name'],
                'last_name' => $row['last_name'],
                'email' => $row['email'],
                'hire_date' => $row['hire_date'],
            ],
            'user_id' => (int)$row['user_id'],
            'role_id' => (int)$row['role_id'],
        ],
    ]);
} catch (Throwable $e) {
    json_response(['ok' => false, 'error' => 'Server error', 'detail' => $e->getMessage()], 500);
}
