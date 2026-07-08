<?php
require 'includes/db.php';

$pageTitle = 'الموظفون المفقودون';
$pageSubtitle = 'الموظفون الذين لم يُسجَّل وصولهم';
$current = 'missing.php';
include 'includes/layout_top.php'; // يوفّر $activeEvent

$search = trim($_GET['q'] ?? '');
$missing = [];
$totalFiltered = 0;

if ($activeEvent) {
    $where = "e.id NOT IN (SELECT employee_id FROM attendance WHERE event_id = ?)";
    $params = [$activeEvent['id']];
    if ($search !== '') {
        $where .= " AND (e.name LIKE ? OR e.employee_number LIKE ? OR e.department LIKE ?)";
        $params[] = "%$search%"; $params[] = "%$search%"; $params[] = "%$search%";
    }
    $countStmt = $pdo->prepare("SELECT COUNT(*) c FROM employees e WHERE $where");
    $countStmt->execute($params);
    $totalFiltered = (int)$countStmt->fetch()['c'];

    $listStmt = $pdo->prepare("SELECT e.*, b.name AS building_name FROM employees e JOIN buildings b ON b.id = e.building_id WHERE $where ORDER BY e.id DESC LIMIT 100");
    $listStmt->execute($params);
    $missing = $listStmt->fetchAll();
}
?>

<?php if ($activeEvent): ?>
<div class="card">
  <form method="get" class="filter-row">
    <input type="text" name="q" placeholder="بحث بالاسم أو الرقم الوظيفي أو القسم" value="<?= h($search) ?>">
    <button type="submit" class="btn-secondary">بحث</button>
  </form>
  <div class="muted-label">عرض <?= min(100, $totalFiltered) ?> من أصل <?= $totalFiltered ?> موظف مفقود</div>
  <div class="grid-row grid-head grid-4">
    <span>الاسم</span><span>الرقم الوظيفي</span><span>القسم</span><span>آخر مبنى معروف</span>
  </div>
  <?php foreach ($missing as $e): ?>
    <div class="grid-row grid-4">
      <span class="bold"><?= h($e['name']) ?></span>
      <span class="muted"><?= h($e['employee_number']) ?></span>
      <span><?= h($e['department']) ?></span>
      <span class="stat-danger bold"><?= h($e['building_name']) ?></span>
    </div>
  <?php endforeach; ?>
  <?php if (!$missing): ?><div class="empty-note">لا يوجد موظفون مفقودون مطابقون</div><?php endif; ?>
</div>
<?php else: ?>
  <div class="card empty-state"><div class="muted">لا يوجد حدث إخلاء نشط حالياً</div></div>
<?php endif; ?>

<?php include 'includes/layout_bottom.php'; ?>
