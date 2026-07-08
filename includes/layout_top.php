<?php
// يتوقع أن يكون $pageTitle و $pageSubtitle و $current معرّفة قبل الاستدعاء، و $pdo متاحاً من db.php
$activeEvent = $pdo->query(
    "SELECT e.*, b.name AS building_name FROM evacuation_events e
     LEFT JOIN buildings b ON b.id = e.affected_building_id
     WHERE e.status = 'active' ORDER BY e.start_time DESC LIMIT 1"
)->fetch();
$navItems = [
    ['file' => 'index.php', 'label' => 'لوحة التحكم'],
    ['file' => 'employees.php', 'label' => 'الموظفون'],
    ['file' => 'assembly_points.php', 'label' => 'نقاط التجمع'],
    ['file' => 'events.php', 'label' => 'حدث الإخلاء'],
    ['file' => 'attendance.php', 'label' => 'تسجيل الحضور'],
    ['file' => 'missing.php', 'label' => 'الموظفون المفقودون'],
    ['file' => 'reports.php', 'label' => 'التقارير'],
];
$elapsed = $activeEvent ? (time() - strtotime($activeEvent['start_time'])) : 0;
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= h($pageTitle) ?> · نظام إدارة التجمع الطارئ</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style.css">
</head>
<body>
<div id="eams-root">
  <div id="eams-sidebar">
    <div class="brand">
      <div class="brand-mark"></div>
      <div>
        <div class="brand-title">نظام إدارة التجمع الطارئ</div>
        <div class="brand-sub">COOP Task 1 — Skills Assessment</div>
      </div>
    </div>
    <?php foreach ($navItems as $item): $isActive = ($current === $item['file']); ?>
      <a class="nav-item<?= $isActive ? ' active' : '' ?>" href="<?= h($item['file']) ?>">
        <span class="dot"></span><?= h($item['label']) ?>
      </a>
    <?php endforeach; ?>
    <div style="flex:1;"></div>
    <?php if ($activeEvent): ?>
      <div class="active-badge">
        <div class="active-badge-dot-row"><span class="dot-live"></span><span>حدث نشط</span></div>
        <div class="active-badge-title"><?= h($activeEvent['title']) ?></div>
        <div class="active-badge-time">منذ <?= h(formatDuration($elapsed)) ?></div>
      </div>
    <?php endif; ?>
  </div>
  <div id="eams-main">
    <div id="eams-header">
      <div>
        <div class="page-title"><?= h($pageTitle) ?></div>
        <div class="page-subtitle"><?= h($pageSubtitle) ?></div>
      </div>
      <?php if ($activeEvent): ?>
        <div class="event-banner">
          <span class="dot-live"></span>
          <span><?= h($activeEvent['title']) ?> · مستمر منذ <?= h(formatDuration($elapsed)) ?></span>
        </div>
      <?php endif; ?>
    </div>
    <div id="eams-content">
