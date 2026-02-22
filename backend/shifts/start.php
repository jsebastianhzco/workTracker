<?php

declare(strict_types=1);

require_once __DIR__ . '/../core/db.php';
require_once __DIR__ . '/../core/http.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    json_response(['ok' => false, 'error' => 'Method not allowed'], 405);
}

$payload = require_auth_payload();
$employeeId = (int)($payload['sub'] ?? 0);
$body = request_json();
$locationId = (int)($body['location_id'] ?? 0);

if ($employeeId <= 0 || $locationId <= 0) {
    json_response(['ok' => false, 'error' => 'employee and location are required'], 422);
}

try {
    $pdo = db();

    $activeStmt = $pdo->prepare(
        'SELECT id FROM shifts WHERE employee_id = :employee_id AND clock_out IS NULL ORDER BY id DESC LIMIT 1'
    );
    $activeStmt->execute(['employee_id' => $employeeId]);
    $activeShift = $activeStmt->fetch();

    if ($activeShift) {
        json_response(['ok' => false, 'error' => 'There is already an active shift'], 409);
    }

    $insert = $pdo->prepare(
        'INSERT INTO shifts (employee_id, location_id, clock_in) VALUES (:employee_id, :location_id, NOW())'
    );
    $insert->execute([
        'employee_id' => $employeeId,
        'location_id' => $locationId,
    ]);

    $shiftId = (int)$pdo->lastInsertId();

    $get = $pdo->prepare('SELECT id, employee_id, location_id, clock_in, clock_out FROM shifts WHERE id = :id LIMIT 1');
    $get->execute(['id' => $shiftId]);
    $shift = $get->fetch();

    json_response([
        'ok' => true,
        'shift_id' => $shiftId,
        'shift' => $shift,
    ], 201);
} catch (Throwable $e) {
    json_response(['ok' => false, 'error' => 'Server error', 'detail' => $e->getMessage()], 500);
}
