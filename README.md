## رابط الموقع المباشر (Live Demo)
[اضغط هنا لتجربة الموقع](https://coop-task-1-skills-assessment-production.up.railway.app/)

# نظام إدارة التجمع الطارئ — نسخة PHP + MySQL (لـ XAMPP)

تطبيق PHP كامل يعمل بقاعدة بيانات MySQL حقيقية، يطابق مخطط قاعدة البيانات المذكور في وثيقة المشروع.

## خطوات التشغيل على XAMPP

1. انسخ مجلد `php-app` بالكامل إلى `htdocs` داخل مجلد تثبيت XAMPP (مثلاً: `C:\xampp\htdocs\eams` أو `/Applications/XAMPP/htdocs/eams`).
2. شغّل **Apache** و **MySQL** من لوحة تحكم XAMPP.
3. افتح `http://localhost/phpmyadmin` وأنشئ قاعدة البيانات عبر استيراد ملف `db.sql` (تبويب Import). هذا ينشئ قاعدة `eams_db` والجداول الخمسة، ويزرع 3 مبانٍ و4 نقاط تجمع تلقائياً.
4. (اختياري لكن موصى به) افتح `http://localhost/eams/seed.php` مرة واحدة فقط — هذا يولّد 500 موظف تجريبي وحدث إخلاء نشط تجريبي مع سجلات حضور، عشان تجرّب النظام مباشرة ببيانات واقعية.
5. افتح `http://localhost/eams/index.php` وابدأ الاستخدام.

## بيانات الاتصال بقاعدة البيانات

معرّفة في `includes/db.php`:
- Host: `localhost`
- Database: `eams_db`
- User: `root`
- Password: `` (فارغة، افتراضي XAMPP)

إذا كانت بيانات MySQL عندك مختلفة، عدّل هذا الملف فقط.

## بنية المشروع

```
php-app/
├── db.sql                  # مخطط قاعدة البيانات + بيانات أولية (مبانٍ، نقاط تجمع)
├── seed.php                # توليد 500 موظف + حدث تجريبي (يُشغَّل مرة واحدة اختيارياً)
├── index.php               # لوحة التحكم
├── employees.php           # الموظفون (عرض/بحث/فلترة/إضافة)
├── assembly_points.php      # نقاط التجمع (عرض/إضافة)
├── events.php               # حدث الإخلاء (إنشاء/إنهاء/سجل)
├── attendance.php           # تسجيل الحضور (بحث/محاكاة QR/تسجيل وصول)
├── missing.php              # الموظفون المفقودون (تلقائي)
├── reports.php              # التقارير + تصدير PDF (عبر طباعة المتصفح)
├── includes/
│   ├── db.php               # اتصال PDO + دوال مساعدة
│   ├── layout_top.php        # الهيدر والسايدبار المشتركان
│   └── layout_bottom.php
└── assets/
    └── style.css            # كل التنسيقات (نفس تصميم النموذج الأولي)
```

## ملاحظات

- كل الصفحات تستخدم PDO مع Prepared Statements لمنع SQL Injection.
- جدول `attendance` فيه قيد `UNIQUE(employee_id, event_id)` يمنع تسجيل وصول الموظف نفسه مرتين بنفس الحدث.
- زر "تصدير تقرير PDF" بصفحة التقارير يفتح نافذة طباعة المتصفح بتنسيق مخصص للطباعة (Ctrl+P → حفظ كـ PDF).
- لإعادة توليد بيانات تجريبية جديدة: فرّغ الجداول `attendance` و `evacuation_events` و `employees` عبر phpMyAdmin ثم أعد فتح `seed.php`.
