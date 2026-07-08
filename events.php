<?php
require 'includes/db.php';

$reasonOptions = ['حريق', 'إنذار كاذب', 'تدريب', 'تسرب غاز'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_event'])) {
    $reason = in_array($_POST['reason'] ?? '', $reasonOptions, true) ? $_POST['reason'] : $reasonOptions[0];
    $buildingId = (($_POST['building_id'] ?? 'all') === 'all') ? null : (int)$_POST['building_id'];
    $pdo->prepare("UPDATE evacuation_events SET status='ended', end_time = NOW() WHERE status='active'")->execute();
    $stmt = $pdo->prepare("INSERT INTO evacuation_events (title, emergency_type, start_time, affected_building_id, status) VALUES (?, ?, NOW(), ?, 'active')");
    $stmt->execute(["إخلاء طارئ - $reason", $reason, $buildingId]);
    header('Location: events.php');
    exit;
}
if (isset($_GET['end'])) {
    $stmt = $pdo->prepare("UPDATE evacuation_events SET status='ended', end_time = NOW() WHERE id = ? AND status='active'");
    $stmt->execute([(int)$_GET['end']]);
    header('Location: events.php');
    exit;
}

$pageTitle = 'حدث الإخلاء';
$pageSubtitle = 'إنشاء ومتابعة أحداث الإخلاء';
$current = 'events.php';
include 'includes/layout_top.php';

$buildings = $pdo->query("SELECT id, name FROM buildings ORDER BY id")->fetchAll();
$events = $pdo->query(
    "SELECT e.*, b.name AS building_name FROM evacuation_events e
     LEFT JOIN buildings b ON b.id = e.affected_building_id
     ORDER BY e.start_time DESC"
)->fetchAll();
$showForm = isset($_GET['new']);
?>

<div class="two-col">
  <div class="card">
    <div class="card-title-row">
      <div class="card-title">سجل الأحداث</div>
      <a href="events.php<?= $showForm ? '' : '?new=1' ?>" class="btn-accent"><?= $showForm ? 'إلغاء' : 'إنشاء حدث جديد' ?></a>
    </div>
    <?php foreach ($events as $ev): ?>
      <div class="event-row">
        <div class="event-row-top">
          <div>
            <div class="bold"><?= h($ev['title']) ?></div>
            <div class="muted small"><?= h($ev['building_name'] ?? 'جميع المباني') ?> · بدأ <?= date('H:i', strtotime($ev['start_time'])) ?></div>
          </div>
          <span class="badge <?= $ev['status'] === 'active' ? 'badge-active' : 'badge-ended' ?>"><?= $ev['status'] === 'active' ? 'نشط' : 'منتهٍ' ?></span>
        </div>
        <?php if ($ev['status'] === 'active'): ?>
          <a href="events.php?end=<?= $ev['id'] ?>" class="btn-outline">إنهاء الحدث</a>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
    <?php if (!$events): ?><div class="empty-note">لا يوجد أحداث بعد</div><?php endif; ?>
  </div>

  <?php if ($showForm): ?>
  <div class="card">
    <div class="card-title">إنشاء حدث إخلاء جديد</div>
    <form method="post" class="form-col">
      <label>السبب</label>
      <select name="reason">
        <?php foreach ($reasonOptions as $r): ?><option value="<?= h($r) ?>"><?= h($r) ?></option><?php endforeach; ?>
      </select>
      <label>المبنى المتأثر</label>
      <select name="building_id">
        <option value="all">جميع المباني</option>
        <?php foreach ($buildings as $b): ?><option value="<?= $b['id'] ?>"><?= h($b['name']) ?></option><?php endforeach; ?>
      </select>
      <div class="muted small">وقت البدء: يُسجَّل تلقائياً عند الإنشاء</div>
      <button type="submit" name="create_event" value="1" class="btn-danger">بدء الإخلاء</button>
    </form>
  </div>
  <?php endif; ?>
</div>

<?php include 'includes/layout_bottom.php'; ?>
