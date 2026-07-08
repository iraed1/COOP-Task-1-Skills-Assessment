<?php
require 'includes/db.php';
$pageTitle = 'لوحة التحكم';
$pageSubtitle = 'نظرة عامة على حالة الإخلاء';
$current = 'index.php';
include 'includes/layout_top.php'; // يوفّر $activeEvent

$totalEmployees = $pdo->query("SELECT COUNT(*) c FROM employees")->fetch()['c'];
$assemblyPointCount = $pdo->query("SELECT COUNT(*) c FROM assembly_points")->fetch()['c'];

$evacuatedCount = 0;
$missingCount = 0;
$eventStatusLabel = 'منتهٍ';
$eventStatusColor = 'var(--gray)';

$perPointStats = $pdo->query("SELECT id, name, capacity FROM assembly_points ORDER BY id")->fetchAll();
foreach ($perPointStats as &$p) { $p['count'] = 0; }
unset($p);

if ($activeEvent) {
    $eventStatusLabel = 'نشط';
    $eventStatusColor = 'var(--danger)';

    $stmt = $pdo->prepare("SELECT COUNT(DISTINCT employee_id) c FROM attendance WHERE event_id = ?");
    $stmt->execute([$activeEvent['id']]);
    $evacuatedCount = (int)$stmt->fetch()['c'];
    $missingCount = $totalEmployees - $evacuatedCount;

    $ptStmt = $pdo->prepare("SELECT assembly_point_id, COUNT(*) c FROM attendance WHERE event_id = ? GROUP BY assembly_point_id");
    $ptStmt->execute([$activeEvent['id']]);
    $counts = [];
    foreach ($ptStmt->fetchAll() as $row) { $counts[$row['assembly_point_id']] = (int)$row['c']; }
    foreach ($perPointStats as &$p) { $p['count'] = $counts[$p['id']] ?? 0; }
    unset($p);
}
?>

<div class="stat-grid">
  <div class="card stat-card">
    <div class="stat-label">إجمالي الموظفين</div>
    <div class="stat-value"><?= $totalEmployees ?></div>
  </div>
  <div class="card stat-card">
    <div class="stat-label">تم إخلاؤهم</div>
    <div class="stat-value stat-safe"><?= $evacuatedCount ?></div>
  </div>
  <div class="card stat-card">
    <div class="stat-label">مفقودون</div>
    <div class="stat-value stat-danger"><?= $missingCount ?></div>
  </div>
  <div class="card stat-card">
    <div class="stat-label">نقاط التجمع</div>
    <div class="stat-value"><?= $assemblyPointCount ?></div>
  </div>
  <div class="card stat-card">
    <div class="stat-label">حالة الإخلاء</div>
    <div class="stat-value" style="font-size:22px; color:<?= $eventStatusColor ?>;"><?= $eventStatusLabel ?></div>
  </div>
</div>

<div class="card" style="margin-top:28px;">
  <div class="card-title">الحضور حسب نقاط التجمع</div>
  <?php foreach ($perPointStats as $p): $pct = $p['capacity'] ? min(100, round($p['count'] / $p['capacity'] * 100)) : 0; ?>
    <div class="point-row">
      <div class="point-row-top">
        <span class="point-name"><?= h($p['name']) ?></span>
        <bdi class="point-count"><?= $p['count'] ?> / <?= $p['capacity'] ?></bdi>
      </div>
      <div class="progress-track"><div class="progress-fill" style="width:<?= $pct ?>%"></div></div>
    </div>
  <?php endforeach; ?>
</div>

<?php include 'includes/layout_bottom.php'; ?>
