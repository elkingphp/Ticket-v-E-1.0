# Unified Architecture Guide (Unified User & Identity)

## 1. الكيان المركزي للمستخدم (Canonical User Entity)
تم توحيد نموذج المستخدم ليكون في المسار:
`Modules\Users\Domain\Models\User.php`

**القاعدة الذهبية:** لا تقم أبداً بالإشارة إلى كلاس المستخدم بمساره الكامل (FQCN) داخل قاعدة البيانات. استخدم دائماً الاسم المستعار (Alias) القصير.

## 2. استراتيجية الـ Morph Map
يتم تسجيل الاسم المستعار كـ `user` في `AppServiceProvider.php` عبر:
```php
Relation::morphMap([
    'user' => \Modules\Users\Domain\Models\User::class,
]);
```
**لماذا؟** هذا يسمح بنقل الموديل من موديول إلى آخر مستقبلاً دون كسر العلاقات في جداول (Notifications, Roles, Personal Tokens, Activity Logs).

## 3. محرك التحليلات (Analytics Architecture)
- **الخدمة:** `App\Services\AnalyticsService.php`.
- **الأداء:** تعتمد الخدمة على `Chunking` لمعالجة البيانات الضخمة ونظام `Cache` لمدة ساعة لتقليل ضغط الـ SQL.
- **التوسعة:** عند إضافة ميزة جديدة (مثل الفروع)، أضف ميثود جديدة للخدمة وقم بربطها عبر الـ `DashboardOrchestrator`.

## 4. نظام التعافي (Real-Time Fallback)
أي فشل في قناة الـ `broadcast` يتم التقاطه بواسطة `App\Listeners\HandleNotificationFailure` ويتم تسجيله في جدول `notification_retry_logs` للتحكم اليدوي أو الإعادة الآلية.
