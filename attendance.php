<?php
require 'includes/db.php';

// معالجة تسجيل الوصول يجب أن تتم قبل أي إخراج HTML حتى يعمل التوجيه (header) بشكل صحيح
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['check_in'])) {
    $active = $pdo->query("SELECT id FROM evacuation_events WHERE status = 'active' ORDER BY start_time DESC LIMIT 1")->fetch();
    $apIdPost = (int)($_POST['ap_id'] ?? 0);
    if ($active) {
        $empId = (int)$_POST['employee_id'];
        $stmt = $pdo->prepare("INSERT IGNORE INTO attendance (employee_id, event_id, assembly_point_id, check_in_time) VALUES (?, ?, ?, NOW())");
        $stmt->execute([$empId, $active['id'], $apIdPost]);
    }
    header('Location: attendance.php?ap_id=' . $apIdPost);
    exit;
}

$pageTitle = 'تسجيل الحضور';
$pageSubtitle = 'تسجيل وصول الموظفين إلى نقطة التجمع';
$current = 'attendance.php';
include 'includes/layout_top.php'; // يوفّر $activeEvent

$points = [];
$candidates = [];
$recent = [];
$search = '';
$apId = 0;

if ($activeEvent) {
    $points = $pdo->query("SELECT id, name FROM assembly_points ORDER BY id")->fetchAll();
    $apId = (int)($_GET['ap_id'] ?? ($points[0]['id'] ?? 0));

    $search = trim($_GET['q'] ?? '');
    if (isset($_GET['qr'])) {
        $pick = $pdo->prepare(
            "SELECT employee_number FROM employees e
             WHERE e.id NOT IN (SELECT employee_id FROM attendance WHERE event_id = ?)
             ORDER BY RAND() LIMIT 1"
        );
        $pick->execute([$activeEvent['id']]);
        $row = $pick->fetch();
        $search = $row ? $row['employee_number'] : '';
    }

    $where = "e.id NOT IN (SELECT employee_id FROM attendance WHERE event_id = ?)";
    $params = [$activeEvent['id']];
    if ($search !== '') {
        $where .= " AND (e.name LIKE ? OR e.employee_number LIKE ?)";
        $params[] = "%$search%"; $params[] = "%$search%";
    }
    $candStmt = $pdo->prepare("SELECT e.*, b.name AS building_name FROM employees e JOIN buildings b ON b.id = e.building_id WHERE $where ORDER BY e.name, e.employee_number LIMIT 15");
    $candStmt->execute($params);
    $candidates = $candStmt->fetchAll();

    $recentStmt = $pdo->prepare(
        "SELECT a.check_in_time, e.name, p.name AS point_name FROM attendance a
         JOIN employees e ON e.id = a.employee_id
         JOIN assembly_points p ON p.id = a.assembly_point_id
         WHERE a.event_id = ? ORDER BY a.check_in_time DESC LIMIT 8"
    );
    $recentStmt->execute([$activeEvent['id']]);
    $recent = $recentStmt->fetchAll();
}
?>

<?php if ($activeEvent): ?>
<div class="two-col">
  <div class="card">
    <form method="get" class="ap-select-row">
      <div style="flex:1;">
        <label>نقطة التجمع الحالية (موقعك)</label><br>
        <select name="ap_id" onchange="this.form.submit()" style="width:100%; margin-top:5px;">
          <?php foreach ($points as $p): ?>
            <option value="<?= $p['id'] ?>" <?= $apId == $p['id'] ? 'selected' : '' ?>><?= h($p['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </form>
    <form method="get" class="filter-row" style="margin-top:16px;">
      <input type="hidden" name="ap_id" value="<?= $apId ?>">
      <input type="text" name="q" placeholder="ابحث بالاسم أو الرقم الوظيفي" value="<?= h($search) ?>">
      <button type="submit" class="btn-secondary">بحث</button>
      <a href="attendance.php?ap_id=<?= $apId ?>&qr=1" class="btn-secondary">محاكاة مسح QR</a>
    </form>
    <div style="margin-top:16px;">
      <?php foreach ($candidates as $c): ?>
        <div class="candidate-row">
          <div>
            <div class="bold"><?= h($c['name']) ?></div>
            <div class="muted small"><?= h($c['employee_number']) ?> · <?= h($c['department']) ?> · <?= h($c['building_name']) ?></div>
          </div>
          <form method="post">
            <input type="hidden" name="employee_id" value="<?= $c['id'] ?>">
            <input type="hidden" name="ap_id" value="<?= $apId ?>">
            <button type="submit" name="check_in" value="1" class="btn-safe">تسجيل الوصول</button>
          </form>
        </div>
      <?php endforeach; ?>
      <?php if (!$candidates): ?><div class="empty-note">لا يوجد موظفون مطابقون لم يصلوا بعد</div><?php endif; ?>
    </div>
  </div>
  <div class="card">
    <div class="card-title">آخر الحضور</div>
    <?php foreach ($recent as $r): ?>
      <div class="recent-row">
        <div>
          <div class="bold small"><?= h($r['name']) ?></div>
          <div class="muted small"><?= h($r['point_name']) ?></div>
        </div>
        <div class="muted small"><?= date('H:i', strtotime($r['check_in_time'])) ?></div>
      </div>
    <?php endforeach; ?>
    <?php if (!$recent): ?><div class="empty-note">لا يوجد حضور مسجّل بعد</div><?php endif; ?>
  </div>
</div>
<?php else: ?>
  <div class="card empty-state">
    <div class="card-title">لا يوجد حدث إخلاء نشط حالياً</div>
    <div class="muted" style="margin-bottom:18px;">يجب بدء حدث إخلاء أولاً لتسجيل الحضور</div>
    <a href="events.php" class="btn-primary" style="display:inline-block;">الذهاب إلى حدث الإخلاء</a>
  </div>
<?php endif; ?>

<?php include 'includes/layout_bottom.php'; ?>
