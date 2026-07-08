<?php
require 'includes/db.php';

$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_point'])) {
    $name = trim($_POST['name'] ?? '');
    $location = trim($_POST['location'] ?? '') ?: '—';
    $capacity = (int)($_POST['capacity'] ?? 0) ?: 100;
    if ($name === '') {
        $error = 'اسم النقطة مطلوب';
    } else {
        $stmt = $pdo->prepare("INSERT INTO assembly_points (name, location, capacity) VALUES (?, ?, ?)");
        $stmt->execute([$name, $location, $capacity]);
        header('Location: assembly_points.php?added=1');
        exit;
    }
}

$pageTitle = 'نقاط التجمع';
$pageSubtitle = 'إدارة نقاط التجمع الخارجية';
$current = 'assembly_points.php';
include 'includes/layout_top.php';

$points = $pdo->query("SELECT * FROM assembly_points ORDER BY id")->fetchAll();
$counts = [];
if ($activeEvent) {
    $ptStmt = $pdo->prepare("SELECT assembly_point_id, COUNT(*) c FROM attendance WHERE event_id = ? GROUP BY assembly_point_id");
    $ptStmt->execute([$activeEvent['id']]);
    foreach ($ptStmt->fetchAll() as $row) { $counts[$row['assembly_point_id']] = (int)$row['c']; }
}
?>

<div class="two-col">
  <div class="point-grid">
    <?php foreach ($points as $p): $count = $counts[$p['id']] ?? 0; $pct = $p['capacity'] ? min(100, round($count / $p['capacity'] * 100)) : 0; ?>
      <div class="card">
        <div class="card-title" style="margin-bottom:4px;"><?= h($p['name']) ?></div>
        <div class="muted"><?= h($p['location']) ?></div>
        <div class="point-count-row">
          <bdi class="big-number"><?= $count ?></bdi>
          <span class="muted">السعة <?= $p['capacity'] ?></span>
        </div>
        <div class="progress-track"><div class="progress-fill" style="width:<?= $pct ?>%"></div></div>
      </div>
    <?php endforeach; ?>
  </div>

  <div class="card">
    <div class="card-title">إنشاء نقطة تجمع</div>
    <?php if ($error): ?><div class="form-error"><?= h($error) ?></div><?php endif; ?>
    <?php if (isset($_GET['added'])): ?><div class="form-success">تمت إضافة النقطة بنجاح</div><?php endif; ?>
    <form method="post" class="form-col">
      <label>اسم النقطة</label>
      <input type="text" name="name" required>
      <label>الموقع</label>
      <input type="text" name="location">
      <label>السعة</label>
      <input type="number" name="capacity" value="100">
      <button type="submit" name="add_point" value="1" class="btn-primary">إضافة النقطة</button>
    </form>
  </div>
</div>

<?php include 'includes/layout_bottom.php'; ?>
