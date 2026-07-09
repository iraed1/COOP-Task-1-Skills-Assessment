<?php
// نقطة نهاية AJAX: تستقبل رقم موظف تم مسحه بالباركود وتسجل حضوره فوراً
header('Content-Type: application/json; charset=utf-8');
require 'includes/db.php';

$code = trim($_POST['code'] ?? '');
$apId = (int)($_POST['ap_id'] ?? 0);

$active = $pdo->query(
    "SELECT id FROM evacuation_events WHERE status = 'active' ORDER BY start_time DESC LIMIT 1"
)->fetch();

if (!$active) {
    echo json_encode(['status' => 'no_event', 'message' => 'لا يوجد حدث إخلاء نشط حالياً']);
    exit;
}

if ($code === '' || $apId <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'بيانات المسح غير صحيحة']);
    exit;
}

$empStmt = $pdo->prepare("SELECT * FROM employees WHERE employee_number = ?");
$empStmt->execute([$code]);
$employee = $empStmt->fetch();

if (!$employee) {
    echo json_encode(['status' => 'not_found', 'message' => 'الرقم الوظيفي غير موجود: ' . $code]);
    exit;
}

$checkStmt = $pdo->prepare("SELECT id FROM attendance WHERE employee_id = ? AND event_id = ?");
$checkStmt->execute([$employee['id'], $active['id']]);

if ($checkStmt->fetch()) {
    echo json_encode([
        'status' => 'already',
        'message' => 'تم تسجيل هذا الموظف مسبقاً',
        'name' => $employee['name'],
    ]);
    exit;
}

$stmt = $pdo->prepare(
    "INSERT INTO attendance (employee_id, event_id, assembly_point_id, check_in_time) VALUES (?, ?, ?, NOW())"
);
$stmt->execute([$employee['id'], $active['id'], $apId]);

echo json_encode([
    'status' => 'success',
    'message' => 'تم تسجيل الوصول بنجاح',
    'name' => $employee['name'],
]);
