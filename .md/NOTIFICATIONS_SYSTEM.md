# نظام الإشعارات المتكامل (Notification System)

## نظرة عامة
تم بناء نظام إشعارات متكامل وقوي يدعم تعدد القنوات (Database, Mail, WebSocket, Slack)، مع ميزات متقدمة مثل التحقق من صحة القنوات، أرشفة الإشعارات، إدارة الحدود (Thresholds)، والإشعارات المجمعة.

## الميزات الرئيسية
- **قنوات متعددة:** دعم لقاعدة البيانات، البريد الإلكتروني، البث المباشر (Reverb)، وقنوات بديلة (Slack).
- **إدارة الحدود (Rate Limiting & Thresholds):** التحكم في عدد الإشعارات لمنع الإغراق.
- **التحديثات اللحظية:** تحديثات فورية عبر WebSocket باستخدام Laravel Reverb.
- **الأرشفة التلقائية:** نقل الإشعارات القديمة إلى جدول أرشيف للحفاظ على الأداء.
- **الموثوقية:** إعادة محاولة الإشعارات الفاشلة وتسجيل الأخطاء في قنوات منفصلة.
- **لوحة تحكم المشرف:** إدارة كاملة للإشعارات، الإحصائيات، والحدود.

## كيفية الاستخدام

### 1. إرسال إشعار
استخدم `AlertService` لإرسال الإشعارات لضمان تطبيق جميع القواعد (Rate Limiting, Thresholds, Fallback).

```php
use Modules\Core\Application\Services\AlertService;

// الحقن عبر الـ Container
public function someAction(AlertService $alertService)
{
    $alertService->sendAlert(
        'system_health', // نوع الحدث
        [
            'title' => 'عنوان الإشعار',
            'message' => 'نص الإشعار',
            'action_url' => '/some/url',
            'priority' => 'critical' // info, warning, critical
        ],
        $user // المتلقي (User Model or Collection)
    );
}

// أو باستخدام الـ Helper
app(AlertService::class)->sendAlert('event_type', $data, $user);
```

### 2. أنواع الإشعارات المدعومة
- `audit_critical`: تدقيق العمليات الحرجة.
- `system_health`: تنبيهات صحة النظام.
- `user_registered`: تسجيل مستخدم جديد.
- `threshold_exceeded`: تجاوز الحدود المسموح بها.

### 3. المهام المجدولة (Scheduled Commands)
تم إعداد المهام التالية في `routes/console.php`:

- `notifications:monitor`: فحص صحة القنوات (كل 5 دقائق).
- `notifications:retry-failed`: إعادة محاولة الإشعارات الفاشلة (كل ساعة).
- `notifications:archive`: أرشفة الإشعارات القديمة (يومياً).

يمكن تشغيلها يدوياً عبر:
```bash
php artisan notifications:monitor --detailed
php artisan notifications:retry-failed
php artisan notifications:archive --days=30
```

### 4. إعدادات Reverb (WebSocket)
تأكد من تشغيل خادم Reverb للحصول على التحديثات اللحظية:
```bash
php artisan reverb:start
```

## لوحة التحكم (Admin Panel)
يمكن للمشرفين (Super Admin) الوصول إلى لوحة التحكم عبر:
- **الرابط:** `/admin/notifications/dashboard`
- **الميزات:**
    - عرض الإحصائيات (يومية، حسب النوع، حسب الأهمية).
    - إدارة حدود الإشعارات (تفعيل/تعطيل، تعديل الأعداد).
    - أدوات الصيانة (أرشفة، تنظيف).

## الهيكلية (Structure)
- **Services:** `Modules/Core/Application/Services`
- **Notifications:** `app/Notifications`
- **Events/Listeners:** `Modules/Core/Application/Listeners`
- **Controllers:** `Modules/Core/Http/Controllers/Admin`
- **Commands:** `app/Console/Commands`

## استكشاف الأخطاء (Troubleshooting)

### الإشعارات لا تصل؟
1. تحقق من سجل الإشعارات: `storage/logs/notifications.log`
2. استخدم أمر المراقبة: `php artisan notifications:monitor --detailed`
3. تحقق من إعدادات `.env` خاصة `BROADCAST_CONNECTION` و `MAIL_MAILER`.

### Reverb لا يعمل؟
1. تأكد من أن `php artisan reverb:start` يعمل.
2. تحقق من إعدادات المنفذ (8080 افتراضياً) وتعارضها.

---
**تم التطوير بواسطة: Digilians Team**
