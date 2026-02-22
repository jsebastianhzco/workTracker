<?php

declare(strict_types=1);

require_once __DIR__ . '/../core/db.php';
require_once __DIR__ . '/../core/http.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
    json_response(['ok' => false, 'error' => 'Method not allowed'], 405);
}

$payload = require_auth_payload();
$employeeId = (int)($payload['sub'] ?? 0);

if ($employeeId <= 0) {
    json_response(['ok' => false, 'error' => 'Invalid token subject'], 401);
}

try {
    $stmt = db()->prepare(
        'SELECT id, employee_id, location_id, clock_in, clock_out
         FROM shifts
         WHERE employee_id = :employee_id AND clock_out IS NULL
         ORDER BY id DESC LIMIT 1'
    );
    $stmt->execute(['employee_id' => $employeeId]);
    $shift = $stmt->fetch();

    json_response([
        'ok' => true,
        'shift' => $shift ?: null,
    ]);
} catch (Throwable $e) {
    json_response(['ok' => false, 'error' => 'Server error', 'detail' => $e->getMessage()], 500);
}
