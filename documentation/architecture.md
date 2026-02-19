# البنية المعمارية للبرنامج

يعتمد النظام معمارية "المونوليث الموديولي الموزع" (Modular Monolith) مع الالتزام بتطبيق طبقات هندسة البرمجيات (Layered Architecture) داخل كل موديول.

## هيكلية الموديولات (Module Structure)
كل موديول يتبع الهيكل التالي لضمان فصل الاهتمامات (Separation of Concerns):

1. **Domain Layer**: تحتوي على الموديلات (Models) والواجهات البرمجية (Interfaces) والسمات (Traits).
2. **Application Layer**: تحتوي على خدمات منطق العمل (Services) والقواعد (Policies) والمهام (Jobs).
3. **Infrastructure Layer**: تحتوي على المستودعات (Repositories) وقواعد البيانات (Migrations) والبذور (Seeders).
4. **Http Layer**: تحتوي على المتحكمات (Controllers) وطلبات التحقق (Requests) والوسطاء (Middleware).
5. **Resources Layer**: تحتوي على الواجهات (Views) واللغات (Lang) والأصول (Assets).
6. **Routes**: ملفات توجيه الويب والـ API.
7. **Providers**: مزودات الخدمة الخاصة بالموديول.

## أنماط التصميم المستخدمة (Design Patterns)
* **Repository Pattern**: لعزل منطق الوصول للبيانات عن منطق العمل.
* **Service Layer**: لتركيز منطق العمل في مكان واحد قابل لإعادة الاستخدام.
* **Singleton**: مستخدم في نظام الإعدادات لتحسين الأداء.
* **Observer/Event Driven**: للتواصل بين الموديولات عبر الـ Hooks.

## تدفق البيانات
1. الطلب (Request) يصل للمتحكم (Controller).
2. المتحكم يستخدم "طلب التحقق" (Form Request) للتأكد من البيانات.
3. المتحكم يستدعي "الخدمة" (Service) المقابلة.
4. الخدمة تستخدم "المستودع" (Repository) لجلب أو حفظ البيانات.
5. المستودع يتعامل مع "الموديل" (Model) لإجراء العمليات على قاعدة البيانات.
