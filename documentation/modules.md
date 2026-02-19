# إدارة الموديولات (Module Management)

يتم إدارة النظام عبر محرك `nwidart/laravel-modules` مع إضافات برمجية مخصصة لضمان الاستقرار.

## العمليات الأساسية
يمكنك إدارة الموديولات عبر `ModuleManagerService` أو عبر أوامر Artisan:

### 1. إنشاء موديول جديد
`php artisan module:make MyModule`

### 2. تفعيل وتعطيل الموديولات
* يتم فحص الاعتمادات (Dependencies) تلقائياً.
* لا يمكن تعطيل موديول تعتمد عليه موديولات أخرى نشطة.
* يتم تخزين حالة الموديولات في Redis لسرعة الوصول في middleware التحقق.

### 3. نظام "الخطافات" (Hooks)
عند تغيير حالة أي موديول، يتم إطلاق أحداث (Events) يمكن الاستماع لها:
* `ModuleBooted`: عند التفعيل.
* `ModuleInstalled`: عند التثبيت الأولي.
* `ModuleDisabled`: عند التعطيل.

### 4. إدارة الأصول (Vite)
يتم تحميل أصول كل موديول بشكل مستقل. تأكد من إضافة المسارات في ملف `vite.config.js` الخاص بالموديول لضمان عمل الـ Multi-entry:
```javascript
export const paths = [
    'Modules/MyModule/Resources/assets/sass/app.scss',
    'Modules/MyModule/Resources/assets/js/app.js',
];
```

## التخلص التلقائي
عند حذف موديول، يقوم النظام تلقائياً بحذف كافة الصلاحيات (Permissions) المرتبطة به لضمان نظافة قاعدة البيانات.
