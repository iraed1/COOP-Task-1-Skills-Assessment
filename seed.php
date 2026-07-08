<?php
require 'includes/db.php';

$firstNames = [
    'محمد', 'أحمد', 'عبدالله', 'خالد', 'فهد', 'سعود', 'ناصر', 'تركي', 'بندر', 'ماجد',
    'عبدالعزيز', 'سلطان', 'عمر', 'يوسف', 'إبراهيم', 'عبدالرحمن', 'فيصل', 'وليد', 'زياد', 'مشعل',
    'سلمان', 'راشد', 'حمد', 'طلال', 'عبدالمجيد', 'يزيد', 'عادل', 'باسل', 'ريان', 'عمار',
    'حسام', 'كريم', 'أنس', 'مالك', 'سامي', 'عثمان', 'نواف', 'رائد', 'شادي', 'مروان',
    'فاطمة', 'نورة', 'سارة', 'مريم', 'العنود', 'ريم', 'هند', 'لطيفة', 'منى', 'أمل',
    'رنا', 'دانة', 'شهد', 'غادة', 'وعد', 'رهف', 'جواهر', 'بشاير', 'أروى', 'حصة',
    'ندى', 'لينا', 'رغد', 'مها', 'عبير', 'سلمى', 'دلال', 'أفنان', 'روان', 'غدير'
];

$middleNames = [
    'عبدالله', 'محمد', 'أحمد', 'سعد', 'إبراهيم', 'خالد', 'ناصر', 'سالم', 'حمد', 'راشد',
    'فهد', 'سلطان', 'عبدالعزيز', 'يوسف', 'علي', 'حسن', 'عمر', 'زيد', 'ماجد', 'طلال'
];

$lastNames = [
    'العتيبي', 'القحطاني', 'الشمري', 'الدوسري', 'الحربي', 'الغامدي', 'السبيعي',
    'المطيري', 'الزهراني', 'الشهري', 'العنزي', 'المالكي', 'العمري', 'الشريف', 'الحازمي'
];

$departments = ['تقنية المعلومات', 'الموارد البشرية', 'المالية', 'العمليات', 'الأمن والسلامة', 'خدمة العملاء'];

$buildingIds = $pdo->query("SELECT id FROM buildings")->fetchAll(PDO::FETCH_COLUMN);

// نولد 100 اسم ثلاثي مختلف (بدون تكرار)
$usedNames = [];
$fullNames = [];
while (count($fullNames) < 100) {
    $name = $firstNames[array_rand($firstNames)] . ' ' . $middleNames[array_rand($middleNames)] . ' ' . $lastNames[array_rand($lastNames)];
    if (!in_array($name, $usedNames)) {
        $usedNames[] = $name;
        $fullNames[] = $name;
    }
}

$stmt = $pdo->prepare("INSERT INTO employees (employee_number, name, department, building_id, emergency_contact) VALUES (?, ?, ?, ?, ?)");

foreach ($fullNames as $i => $fullName) {
    $empNumber = 'EMP' . str_pad($i + 1, 4, '0', STR_PAD_LEFT);
    $dept = $departments[array_rand($departments)];
    $buildingId = $buildingIds[array_rand($buildingIds)];
    $phone = '05' . rand(10000000, 99999999);

    $stmt->execute([$empNumber, $fullName, $dept, $buildingId, $phone]);
}

echo "تم توليد " . count($fullNames) . " موظف بأسماء ثلاثية مختلفة بنجاح.";
