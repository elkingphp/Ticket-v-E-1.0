# Digilians Admin Portal - Documentation

## Overview
Digilians is a robust Laravel 11 administrative portal built with the Velzon Admin Template. It supports full localization (English/Arabic) with dynamic RTL/LTR layout switching and advanced role-based access control (RBAC).

## Key Features

### 1. Localization & RTL Support
- **Dynamic Switching**: Toggle between English (LTR) and Arabic (RTL) seamlessly.
- **Auto-direction**: The system automatically adjusts the `dir` and `lang` attributes of the HTML tag.
- **RTL Assets**: Loads `app-rtl.min.css` and `bootstrap-rtl.min.css` for Arabic.
- **DataTables**: Fully localized with Arabic JSON and RTL support.

### 2. User & Access Management
- **User CRUD**: Complete management of users, status, and profile details.
- **Role Management**: Define roles and assign multiple permissions.
- **Permission CRUD**: (New) Manage individual security permissions grouped by module.
- **Authenticated Access**: Routes are protected by `auth`, `verified`, and Spatie's permission middleware.

### 3. Dashboard & Analytics
- **Dynamic Widgets**: Real-time stats for Users, Roles, and Logs.
- **ApexCharts**: Visual data distribution for system activity.
- **Caching**: Dashboard metrics are cached for 5 minutes to optimize performance.

### 4. Advanced Security
- **Fortify Integration**: Handles Login, Registration, and Password Resets.
- **2FA Support**: Optional Two-Factor Authentication via Fortify.
- **Session Management**: Feature to "Logout Other Devices" from the profile page.
- **Audit Logs**: Comprehensive tracking of all system events with detail view and IP logging.

### 5. Reporting & Exports
- **CSV Export**: (New) Efficiently export Users and Audit Logs to CSV for offline analysis.
- **Streaming**: Uses stream-based responses to handle large datasets safely.

### 6. Notifications
- **Multi-channel**: Supports Database, Mail, and Broadcast (Pusher/Reverb).
- **Bell Dropdown**: Real-time notification updates in the topbar.

## Performance Optimizations
- **Repository Caching**: Settings are cached forever and cleared on update.
- **Dashboard Caching**: Short-term caching for analytics.
- **Lazy Loading**: Audit log details are loaded via AJAX.

## Deployment & Maintenance
- **Caches**: Run `php artisan optimize:clear` after any configuration change.
- **Queues**: Ensure `php artisan queue:work` is running for notifications.
- **Tests**: Comprehensive Feature tests in `tests/Feature/LocalizationTest.php` and `tests/Feature/UserSystemIntegrityTest.php`.

---

# بوابة ديجيليانز للإدارة - التوثيق

## نظرة عامة
ديجيليانز هي بوابة إدارية قوية تم بناؤها باستخدام Laravel 11 وقالب Velzon. تدعم التعريب الكامل (الإنجليزية/العربية) مع تبديل ديناميكي لتخطيطات RTL/LTR والتحكم المتقدم في الوصول القائم على الأدوار (RBAC).

## المميزات الرئيسية

### 1. التعريب ودعم RTL
- **التبديل الديناميكي**: التبديل بين الإنجليزية والعرية بسلاسة.
- **الاتجاه التلقائي**: يقوم النظام بضبط سمات `dir` و `lang` تلقائياً.
- **ملفات RTL**: يتم تحميل ملفات CSS الخاصة باللغة العربية تلقائياً.

### 2. إدارة المستخدمين والوصول
- **CRUD المستخدمين**: إدارة كاملة للمستخدمين والحالات والملفات الشخصية.
- **إدارة الأدوار**: تعريف الأدوار وتخصيص الصلاحيات.
- **إدارة الصلاحيات**: (جديد) إدارة صلاحيات الأمان الفردية مقسمة حسب الموديول.

### 3. لوحة التحكم والتحليلات
- **عناصر واجهة ديناميكية**: إحصائيات مباشرة للمستخدمين والأدوار والسجلات.
- **الرسوم البيانية**: توزيع البيانات باستخدام ApexCharts.
- **التخزين المؤقت**: يتم تخزين مقاييس لوحة التحكم مؤقتاً لمدة 5 دقائق.

### 4. الأمن المتقدم
- **تكامل Fortify**: معالجة الدخول والتسجيل واستعادة كلمة المرور.
- **دعم 2FA**: خيار المصادقة الثنائية.
- **إدارة الجلسات**: ميزة "تسجيل الخروج من الأجهزة الأخرى".
- **سجلات المراجعة**: تتبع شامل لجميع أحداث النظام.

### 5. التقارير والتصدير
- **تصدير CSV**: (جديد) تصدير قوائم المستخدمين وسجلات المراجعة لتحليلها يدوياً.

### 6. التنبيهات
- **قنوات متعددة**: دعم التنبيهات عبر قاعدة البيانات، البريد، والبث المباشر.
