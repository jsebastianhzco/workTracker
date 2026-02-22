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
$shiftId = (int)($body['shift_id'] ?? 0);
$clockOut = trim((string)($body['clock_out'] ?? ''));

if ($employeeId <= 0 || $shiftId <= 0 || $clockOut === '') {
    json_response(['ok' => false, 'error' => 'shift_id and clock_out are required'], 422);
}

$date = DateTime::createFromFormat('Y-m-d H:i:s', $clockOut);
if (!$date) {
    json_response(['ok' => false, 'error' => 'Invalid clock_out format, expected Y-m-d H:i:s'], 422);
}

try {
    $pdo = db();

    $check = $pdo->prepare(
        'SELECT id, employee_id, clock_in, clock_out, total_break_minutes FROM shifts WHERE id = :id LIMIT 1'
    );
    $check->execute(['id' => $shiftId]);
    $shift = $check->fetch();

    if (!$shift) {
        json_response(['ok' => false, 'error' => 'Shift not found'], 404);
    }

    if ((int)$shift['employee_id'] !== $employeeId) {
        json_response(['ok' => false, 'error' => 'Shift does not belong to user'], 403);
    }

    if (!empty($shift['clock_out'])) {
        json_response(['ok' => false, 'error' => 'Shift already closed'], 409);
    }

    $breakMinutes = max(0, (int)($shift['total_break_minutes'] ?? 0));
    $workedMinutes = max(0, (int)$date->getTimestamp() - (int)strtotime((string)$shift['clock_in']));
    $workedMinutes = (int)floor($workedMinutes / 60) - $breakMinutes;
    if ($workedMinutes < 0) {
        $workedMinutes = 0;
    }

    $update = $pdo->prepare(
        'UPDATE shifts
         SET clock_out = :clock_out,
             total_work_minutes = :total_work_minutes,
             status = "completed"
         WHERE id = :id'
    );
    $update->execute([
        'clock_out' => $date->format('Y-m-d H:i:s'),
        'total_work_minutes' => $workedMinutes,
        'id' => $shiftId,
    ]);

    json_response(['ok' => true, 'message' => 'Shift closed successfully']);
} catch (Throwable $e) {
    json_response(['ok' => false, 'error' => 'Server error', 'detail' => $e->getMessage()], 500);
}
