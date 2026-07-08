<?php
$host = 'localhost';
$dbname = 'eams_db';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    die('فشل الاتصال بقاعدة البيانات: ' . htmlspecialchars($e->getMessage()) .
        '<br>تأكد أن MySQL يعمل في XAMPP وأنك استوردت db.sql، وأن بيانات الاتصال في includes/db.php صحيحة.');
}

function h($s) {
    return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8');
}

function formatDuration($seconds) {
    $seconds = max(0, (int)$seconds);
    $h = intdiv($seconds, 3600);
    $m = intdiv($seconds % 3600, 60);
    $s = $seconds % 60;
    if ($h > 0) return "$h س $m د";
    return "$m د $s ث";
}
