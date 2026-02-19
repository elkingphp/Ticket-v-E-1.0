# System Operations Runbook

## 1. التعامل مع أعطال الـ WebSockets
- **العرض:** الإشعارات لا تظهر لحظياً (تحتاج Refresh).
- **الحل:** 
    1. تأكد من تشغيل خادم Reverb: `php artisan reverb:start`.
    2. تحقق من جدول `notification_retry_logs` لمعرفة أسباب الفشل.
    3. تصفير كاش الـ Broadcasting: `php artisan cache:forget reverb`.

## 2. تحديث الإحصائيات (Manual Refresh)
التحليلات في الـ Dashboard تعتمد على الـ Cache. لإجبار النظام على إعادة الحساب الفوري:
```bash
php artisan cache:forget global_profile_completion
php artisan cache:forget user_growth_trends
```

## 3. تنظيف البيانات الضخمة
يتم تنظيف السجلات تلقائياً كل أسبوع (أكبر من 90 يوماً). للتنفيذ اليدوي (مثلاً لتنظيف سجلات أقدم من 30 يوماً):
```bash
php artisan logs:cleanup --days=30
```

## 4. فحص الأمان
- لمراجعة محاولات الدخول الفاشلة: `grep "Security: Failed login" storage/logs/laravel.log`.
- لتعطيل الـ 2FA الإلزامي للطوارئ: قم بإزالة `2fa.mandatory` من `bootstrap/app.php`.
