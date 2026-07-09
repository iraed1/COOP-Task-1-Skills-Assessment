<?php
require 'includes/db.php';

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare(
    "SELECT e.*, b.name AS building_name FROM employees e JOIN buildings b ON b.id = e.building_id WHERE e.id = ?"
);
$stmt->execute([$id]);
$employee = $stmt->fetch();

if (!$employee) {
    die('الموظف غير موجود');
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>بطاقة الموظف · <?= h($employee['name']) ?></title>
<link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;700;900&display=swap" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/JsBarcode/3.11.5/JsBarcode.all.min.js"></script>
<style>
  body { font-family:'Tajawal',sans-serif; background:#f4f5f7; display:flex; align-items:center; justify-content:center; min-height:100vh; margin:0; }
  .badge { background:#fff; width:340px; border-radius:16px; box-shadow:0 10px 30px rgba(0,0,0,.08); padding:26px; text-align:center; border:1px solid #eee; }
  .badge .org { color:#888; font-size:12px; font-weight:700; letter-spacing:.3px; }
  .badge h2 { margin:10px 0 2px; font-size:19px; }
  .badge .sub { color:#777; font-size:13px; margin-bottom:16px; }
  .badge svg { max-width:100%; }
  .badge .num { margin-top:6px; font-size:13px; color:#555; direction:ltr; }
  .actions { margin-top:18px; display:flex; gap:8px; justify-content:center; }
  .actions button, .actions a { border:none; background:#111; color:#fff; padding:10px 20px; border-radius:8px; font-family:inherit; cursor:pointer; font-size:13px; text-decoration:none; }
  .actions a.secondary { background:#eee; color:#111; }
  @media print {
    .actions { display:none; }
    body { background:#fff; }
    .badge { box-shadow:none; border:none; }
  }
</style>
</head>
<body>
  <div class="badge">
    <div class="org">نظام إدارة التجمع الطارئ</div>
    <h2><?= h($employee['name']) ?></h2>
    <div class="sub"><?= h($employee['department']) ?> · <?= h($employee['building_name']) ?></div>
    <svg id="barcode"></svg>
    <div class="num"><?= h($employee['employee_number']) ?></div>
    <div class="actions">
      <button onclick="window.print()">طباعة البطاقة</button>
      <a href="employees.php" class="secondary">رجوع</a>
    </div>
  </div>
  <script>
    JsBarcode("#barcode", <?= json_encode($employee['employee_number']) ?>, {
      format: "CODE128",
      displayValue: false,
      height: 60,
      margin: 6
    });
  </script>
</body>
</html>
