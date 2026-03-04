# 🚀 الخطة التنفيذية الصارمة لموديول الإدارة التعليمية (Core-Integrated Strict Plan)

تمثل هذه الوثيقة المرجع النهائي والصارم لبناء موديول "الإدارة التعليمية" (Educational Management System). تعتمد الخطة على التكامل التام مع البنية التحتية الحالية للنظام (Core) دون أي تكرار للمكونات الأساسية.

---

## 📌 المبادئ المعمارية المعتمدة (Architectural Mandates)

1. **لا تكرار للأساسيات (DRY Core):** استخدام نظام التصاريح (RBAC)، سجلات التدقيق (Audit Logging - ActivityService)، ونظام الإشعارات الموجود بالفعل في `Core` و `Users`.
2. **محرك موافقات مركزي (Centralized Approval Engine):** يبنى كجزء من وحدة `Core` لخدمة النظام التعليمي والتذاكر والموديولات المستقبلية.
3. **فصل الهويات (Identity Separation):** المتدربون والمحاضرون هم (Profiles) منفصلة ترتبط بجدول `users` عبر `MorphToOne/MorphToMany`، ولا يتم تدنيس جدول المستخدمين بتفاصيلهم المتخصصة.
4. **عزل التزامن (Concurrency Isolation):** منع أي Race Conditions باستخدام خليط من (DB-level Composite Indexes, `SELECT ... FOR UPDATE`, وفحوصات برمجية دقيقة) إلى جانب `version` للـ Optimistic Locking في الكيانات الحساسة.
5. **الاعتماد على الأحداث (Event-Driven Architecture):** فك ارتباط المكونات (Decoupling) بحيث تتخاطب الأنظمة ببعضها عن طريق إطلاق (Events).

---

## 🛠 مرحلة 1: التكامل والتطوير الأساسي (Core Enhancements)
*(لن نلمس الموديول التعليمي قبل إنجاز هذه المرحلة في الـ Core)*

### 1.1 ترقية نظام الرمزيات (RBAC Extension)
* **تسمية موحدة (Namespace):** ستكون جميع التصاريح الجديدة تحت البادئة `education.*` (مثال: `education.programs.create`، `education.schedule.manage`).
* التحديث يتم على أساس النظام الحالي دون إعادة بناء الـ Permissions.

### 1.2 بناء محرك الموافقات المركزي (Core / Approval Engine)
التحسينات الاحترافية الإلزامية:
* إنشاء `ApprovalRequest` بتصميم يستوعب كل الموديولات بوجود حقل السكيما:
  * `approvable_type`, `approvable_id`, `approvable_schema` (مثلاً `education.attendance`).
* **دعم الموافقات المتعددة والمستويات (Multi-level Approvals):**
  * سيحتوي الجدول على الحقول (Polymorphic Attributes, `status`, `requested_by`, `approved_by`, `rejected_by`, `approval_level`, `metadata` JSON, `expires_at`).
* **Event-Driven Execution:** عند القبول أو الرفض، سيتم إطلاق أحداث مثل `ApprovalApproved` أو `ApprovalRejected` لتقوم الـ Listeners بتبديل حالة الكيان أو إرسال الإشعار.
* إنشاء الـ Trait `MustBeApproved`.

---

## 🏢 مرحلة 2: البنية التحتية التعليمية (Educational Infrastructure)
*(إنشاء الموديول Educational والبدء بالكيانات الأساسية)*

### 2.1 المقرات الدراسية (Facilities)
* بناء الجداول الهرمية (Campus → Building → Floor → Room).
* **إضافات ضرورية:**
  * إضافة حقول `room_status` (active, maintenance, disabled) وإضافة `room_type` لمنع حجز القاعات المعطلة.

### 2.2 شركات التدريب (Training Companies)
* إنشاء الكيانات (Companies, Job Profiles) والعلاقة بينهما (Many-to-Many).
* تطبيق قواعد SoftDeletes وعدم السماح بحذف شركة لها تعاقدات فعالة.

---

## 👨‍🏫 مرحلة 3: الموارد البشرية والملفات الشخصية (HR & Profiles)

### 3.1 المحاضرون (Instructors Profile)
* بناء جدول `instructor_profiles` كالتصاق بـ `User` عن طريق `Morph`.
* ربط المحاضرين بالشركات والتخصصات بصلاحيات M:M.

### 3.2 المتدربون (Trainees Profile)
* بناء جدول `trainee_profiles` للبيانات العلمية.
* **الخصوصية الصارمة (Data Privacy Layer):**
  * تشفير البيانات الحساسة جداً (رقم قومي، ديانة، حالة طبية).
  * لا يتم استدعاء الحقول الحساسة بواسطة `SELECT *`، الـ Repositories ستستدعي الـ (`id`, `name`) فقط، ولن يتم استدعاء الحقول الحساسة إلا بـ Permission مخصص.
  * الـ UI سيعرض البيانات الطبية بنظام الـ Masking (إخفاء النجوم).

---

## 📚 مرحلة 4: المحرك التشغيلي والجدولة (Programs & Scheduling Engine)
*(أخطر جزء في المشروع - الحماية المطلقة للبيانات)*

### 4.1 الهيئة الأكاديمية
* البرامج (Programs) والمجموعات (Groups) وتطبيق الـ State Machine على حالاتها.

### 4.2 محرك الجدولة وكشف التعارض (Scheduling & Conflict Detection)
* **الحماية على مستوى قاعدة البيانات (Database Level):**
  * إضافة `Unique Composite Index` لمنع أي تداخل نهائي. (كمثال لمراعاة الوقت والقاعة: `room_id`, `start_time`, `end_time`).
* **الحماية على المستوى البرمجي (Row-level Locking):**
  * استخدام `lockForUpdate()` مع `DB::transaction()` لكل محاولة جدولة.
* **Optimistic Locking:**
  * إدراج عمود `version` رقمي في جدول المحاضرات والقاعات كضمانة رابعة لعدم كسر النظام في التحديث المزدوج.
* وضع ضوابط وتحديد سرعة الطلبات العادية أو المكثفة للمخدم (Rate Limiting).

---

## 🎯 مرحلة 5: المراقبة والحضور (Monitoring & Attendance)

### 5.1 النماذج الديناميكية (Dynamic Forms)
* محرك نماذج للتقييم والمراقبة يدعم مكونات إدخال متعددة.

### 5.2 منظومة الحضور والإغلاق (Attendance Lock System)
* جداول حضور المتدربين للربط بالمحاضرات.
* **Audit Trail للإغلاق:**
  * سيتم تفعيل `AttendanceLockPolicy` و `MonitoringLockPolicy`.
  * ستشمل الجداول أعمدة `locked_at` و `locked_by` لمعرفة من أغلق الجلسة ومتى.
* أي محاولة لتغيير كشف مغلق ستتحول للحالة المؤقتة وترسل لـ (Approval Engine).

---

## 🔒 مرحلة 6: الأمن وضبط الأداء (Security & Performance)

1. **القيود المرجعية (Database-level constraints):**
   * الاعتماد على قوة محرك البيانات (MySQL/PostgreSQL) في الـ Foreign Keys لمنع حيازة البيانات غير المكتملة (Orphan Data)، وليس فقط الـ Laravel Validations.
2. **تحديد السرعة (Rate Limiting):**
   * المسارات المحورية (إنشاء الجداول وحفظ الحضور) ستتم تغطيتها بـ Rate Limit صارم للحد من هجمات DDoS ولتقليل العبء.
3. **تطبيق الفهارس الاستباقي (Early Index Review):**
   * جميع حقول البحث المشتركة وعمليات الجدولة ستُخلق لها الفهارس (Indexes) منذ ملف الـ Migration الأول (بدون تأخير للمراحل القادمة).
4. **سجل التدقيق (Audit Logging):**
   * لن يكون هناك كيان بلا مراقبة عبر `ActivityService`.

---

## 📝 الترتيب التنفيذي للعمل (Execution Order)
1. **[مرحلة التنفيذ 1]:** إعداد الـ Permissions التعليمية ومحرك الموافقات المركزي داخل الـ Core.
2. **[مرحلة التنفيذ 2]:** بناء موديول `Educational` وهيكلة الـ Facilities ورموز الحجز.
3. **[مرحلة التنفيذ 3]:** ملفات الموارد البشرية كـ Polymorphic Profiles.
4. **[مرحلة التنفيذ 4]:** محرك الجدول والفهرسة التزامنية المعقدة.
5. **[مرحلة التنفيذ 5]:** منظومة الحضور وديناميكية النماذج.

*(وثيقة تحليلية رسمية للإطار الفني، تُراجع قبل كتابة كل Migration لضمان الموثوقية).*
