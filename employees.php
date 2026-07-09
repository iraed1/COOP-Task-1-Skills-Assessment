<?php
require 'includes/db.php';

$departments = ['تقنية المعلومات', 'الموارد البشرية', 'المالية', 'العمليات', 'المبيعات', 'التسويق', 'الهندسة', 'خدمة العملاء'];

$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_employee'])) {
    $number = trim($_POST['number'] ?? '');
    $name = trim($_POST['name'] ?? '') ?: 'موظف جديد';
    $dept = $_POST['department'] ?? $departments[0];
    $buildingId = $_POST['building_id'] ?? '';
    $contact = trim($_POST['contact'] ?? '') ?: null;

    if ($number === '' || $buildingId === '') {
        $error = 'الرقم الوظيفي والمبنى مطلوبان';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO employees (employee_number, name, department, building_id, emergency_contact) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$number, $name, $dept, $buildingId, $contact]);
            header('Location: employees.php?added=1');
            exit;
        } catch (PDOException $e) {
            $error = 'الرقم الوظيفي مستخدم مسبقاً أو حدث خطأ آخر';
        }
    }
}

$pageTitle = 'الموظفون';
$pageSubtitle = 'إدارة بيانات الموظفين';
$current = 'employees.php';
include 'includes/layout_top.php';

$buildings = $pdo->query("SELECT id, name FROM buildings ORDER BY id")->fetchAll();

$search = trim($_GET['q'] ?? '');
$buildingFilter = $_GET['building'] ?? 'all';
$deptFilter = $_GET['dept'] ?? 'all';

$where = [];
$params = [];
if ($search !== '') { $where[] = "(name LIKE ? OR employee_number LIKE ?)"; $params[] = "%$search%"; $params[] = "%$search%"; }
if ($buildingFilter !== 'all') { $where[] = "building_id = ?"; $params[] = $buildingFilter; }
if ($deptFilter !== 'all') { $where[] = "department = ?"; $params[] = $deptFilter; }
$whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

$countStmt = $pdo->prepare("SELECT COUNT(*) c FROM employees $whereSql");
$countStmt->execute($params);
$totalFiltered = (int)$countStmt->fetch()['c'];

$listStmt = $pdo->prepare("SELECT e.*, b.name AS building_name FROM employees e JOIN buildings b ON b.id = e.building_id $whereSql ORDER BY e.id DESC LIMIT 100");
$listStmt->execute($params);
$employees = $listStmt->fetchAll();
?>

<div class="two-col">
  <div class="card">
    <form method="get" class="filter-row">
      <input type="text" name="q" placeholder="بحث بالاسم أو الرقم الوظيفي" value="<?= h($search) ?>">
      <select name="building">
        <option value="all">كل المباني</option>
        <?php foreach ($buildings as $b): ?>
          <option value="<?= $b['id'] ?>" <?= (string)$buildingFilter === (string)$b['id'] ? 'selected' : '' ?>><?= h($b['name']) ?></option>
        <?php endforeach; ?>
      </select>
      <select name="dept">
        <option value="all">كل الأقسام</option>
        <?php foreach ($departments as $d): ?>
          <option value="<?= h($d) ?>" <?= $deptFilter === $d ? 'selected' : '' ?>><?= h($d) ?></option>
        <?php endforeach; ?>
      </select>
      <button type="submit" class="btn-secondary">تصفية</button>
    </form>

    <div class="muted-label">عرض <?= min(100, $totalFiltered) ?> من أصل <?= $totalFiltered ?></div>

    <div class="grid-row grid-row-5 grid-head">
      <span>الاسم</span><span>الرقم الوظيفي</span><span>القسم</span><span>المبنى</span><span>الباركود</span>
    </div>
    <?php foreach ($employees as $e): ?>
      <div class="grid-row grid-row-5">
        <span class="bold"><?= h($e['name']) ?></span>
        <span class="muted"><?= h($e['employee_number']) ?></span>
        <span><?= h($e['department']) ?></span>
        <span><?= h($e['building_name']) ?></span>
        <span><a href="barcode.php?id=<?= (int)$e['id'] ?>" target="_blank" class="btn-outline">عرض/طباعة</a></span>
      </div>
    <?php endforeach; ?>
    <?php if (!$employees): ?><div class="empty-note">لا يوجد موظفون مطابقون</div><?php endif; ?>
  </div>

  <div class="card">
    <div class="card-title">إضافة موظف</div>
    <?php if ($error): ?><div class="form-error"><?= h($error) ?></div><?php endif; ?>
    <?php if (isset($_GET['added'])): ?><div class="form-success">تمت إضافة الموظف بنجاح</div><?php endif; ?>
    <form method="post" class="form-col">
      <label>الاسم</label>
      <input type="text" name="name">
      <label>الرقم الوظيفي</label>
      <input type="text" name="number" required>
      <label>القسم</label>
      <select name="department">
        <?php foreach ($departments as $d): ?><option value="<?= h($d) ?>"><?= h($d) ?></option><?php endforeach; ?>
      </select>
      <label>المبنى</label>
      <select name="building_id">
        <?php foreach ($buildings as $b): ?><option value="<?= $b['id'] ?>"><?= h($b['name']) ?></option><?php endforeach; ?>
      </select>
      <label>رقم اتصال الطوارئ (اختياري)</label>
      <input type="text" name="contact">
      <button type="submit" name="add_employee" value="1" class="btn-primary">إضافة الموظف</button>
    </form>
  </div>
</div>

<?php include 'includes/layout_bottom.php'; ?>
