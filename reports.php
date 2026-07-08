<?php
require 'includes/db.php';

$pageTitle = 'التقارير';
$pageSubtitle = 'إحصائيات ونتائج عملية الإخلاء';
$current = 'reports.php';
include 'includes/layout_top.php'; // يوفّر $activeEvent

$totalEmployees = $pdo->query("SELECT COUNT(*) c FROM employees")->fetch()['c'];
$evacuatedCount = 0;
$missingCount = 0;
$durationLabel = '—';
$completionRateLabel = '—';

$perPointStats = $pdo->query("SELECT id, name, capacity FROM assembly_points ORDER BY id")->fetchAll();
foreach ($perPointStats as &$p) { $p['count'] = 0; }
unset($p);

if ($activeEvent) {
    $stmt = $pdo->prepare("SELECT COUNT(DISTINCT employee_id) c FROM attendance WHERE event_id = ?");
    $stmt->execute([$activeEvent['id']]);
    $evacuatedCount = (int)$stmt->fetch()['c'];
    $missingCount = $totalEmployees - $evacuatedCount;

    $end = $activeEvent['end_time'] ?? date('Y-m-d H:i:s');
    $durationLabel = formatDuration(strtotime($end) - strtotime($activeEvent['start_time']));
    $completionRateLabel = $totalEmployees ? round($evacuatedCount / $totalEmployees * 100) . '%' : '—';

    $ptStmt = $pdo->prepare("SELECT assembly_point_id, COUNT(*) c FROM attendance WHERE event_id = ? GROUP BY assembly_point_id");
    $ptStmt->execute([$activeEvent['id']]);
    $counts = [];
    foreach ($ptStmt->fetchAll() as $row) { $counts[$row['assembly_point_id']] = (int)$row['c']; }
    foreach ($perPointStats as &$p) { $p['count'] = $counts[$p['id']] ?? 0; }
    unset($p);
}
?>

<div class="stat-grid stat-grid-4">
  <div class="card stat-card">
    <div class="stat-label">مدة الإخلاء</div>
    <div class="stat-value" style="font-size:30px;"><?= h($durationLabel) ?></div>
  </div>
  <div class="card stat-card">
    <div class="stat-label">نسبة الإنجاز</div>
    <div class="stat-value stat-safe" style="font-size:30px;"><?= h($completionRateLabel) ?></div>
  </div>
  <div class="card stat-card">
    <div class="stat-label">تم إخلاؤهم</div>
    <div class="stat-value" style="font-size:30px;"><?= $evacuatedCount ?></div>
  </div>
  <div class="card stat-card">
    <div class="stat-label">مفقودون</div>
    <div class="stat-value stat-danger" style="font-size:30px;"><?= $missingCount ?></div>
  </div>
</div>

<div class="card" style="margin:20px 0;">
  <div class="card-title">عدد الأشخاص في كل نقطة تجمع</div>
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

<button onclick="window.print()" class="btn-primary">تصدير تقرير PDF</button>

<?php include 'includes/layout_bottom.php'; ?>
