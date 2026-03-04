# الدليل الشامل لهيكلية قاعدة البيانات (Schema: Education)

هذا الدليل يعتبر مرجعاً متكاملاً لكافة جداول وحقول قاعدة البيانات الخاصة بـ **المركز التعليمي (Education Schema)** في النظام (Digilians TS-V.2). يمكنك الاعتماد على هذا المرجع لاستخراج التقارير، بناء جمل الـ (SQL Queries)، وفهم ترابط البيانات ببعضها البعض بحرفية عالية.

تم تقسيم الجداول إلى **خمسة محاور رئيسية** بناءً على وظيفتها في بيئة العمل:

---

## 🏛️ المحور الأول: البنية التحتية والمنشآت (Facilities)
هذه الجداول مسؤولة عن أرشفة الأماكن التي تعقد فيها المحاضرات. وهي مترابطة تسلسلياً كالتالي:

1. **[campuses](file:///run/media/azouz/15ca02f9-a669-4c98-aeae-5b67cf6ee8bb1/azouz/digilians/TS-V.2%20ai/app/Modules/Educational/Domain/Models/Program.php#18-27) (الفروع / المقار الرئيسية)**
   - **الحقول الأساسية:** `name`, `code`, `address`, `status`.
   - **الاستخدام:** أعلى مستوى في الهرم المكاني (مثل مقرات المؤسسة المختلفة).

2. **`buildings` (المباني)**
   - **الحقول الأساسية:** `name`, `code`, `status`, `campus_id`.
   - **العلاقات:** يعود كل مبنى إلى فرع `campus_id`.

3. **`floors` (الأدوار / الطوابق)**
   - **الحقول الأساسية:** `floor_number`, `name`, `status`, `building_id`.
   - **العلاقات:** يعود كل دور لمبنى `building_id`.

4. **`room_types` (أنواع القاعات)**
   - **الحقول الأساسية:** `name`, `slug`, `color`, `icon`.

5. **`rooms` (القاعات والمعامل)**
   - **الحقول الأساسية:** `name`, `code`, `capacity` (السعة), `room_status`, `room_type_id`, `floor_id`.
   - **العلاقات:** تعود لدور `floor_id` ونوع `room_type_id`.
   - **استخراج التقارير:** مفيد جداً لمعرفة القاعات المتاحة ومقارنة نسبة استيعاب القاعة بعدد الطلاب.

> 💡 **فكرة تقرير:** دمج الجداول السابقة لاستخراج "تقرير استيعاب القاعات حسب الفرع"، يمكن عمل (`JOIN` من القاعات للوصول للفرع عن طريق المبنى والدور).

---

## 📚 المحور الثاني: البرامج الأكاديمية والهياكل (Academic Structure)
مسؤولة عن هيكلة المناهج وتوزيع المتدربين داخل النظام.

1. **`programs` (البرامج التدريبية)**
   - **الحقول الأساسية:** `name`, `code`, `description`, `status`, `starts_at`, `ends_at`.
   - **الاستخدام:** المظلة الكبيرة (مثلاً: برنامج دبلوم إدارة الأعمال دفعة ٢٠٢٦).

2. **[groups](file:///run/media/azouz/15ca02f9-a669-4c98-aeae-5b67cf6ee8bb1/azouz/digilians/TS-V.2%20ai/app/Modules/Educational/Domain/Models/Program.php#33-37) (المجموعات / الفصول)**
   - **الحقول الأساسية:** `name`, `capacity`, `term` (الفصل), `program_id`, `job_profile_id`, `status`.
   - **الاستخدام:** التوزيعات الداخلية للطلاب داخل البرنامج التدريبي.

3. **`tracks` (المسارات / التخصصات الكبرى)**
   - **الحقول الأساسية:** `name`, `code`, `slug`, `is_active`.

4. **`job_profiles` (الملفات الوظيفية / الفئات)**
   - **الحقول الأساسية:** `name`, `code`, `track_id`, `status`.
   - **العلاقات:** ترتبط بمسار أكبر `track_id`. يتم ربط الطلاب والمجموعات بها لاحقاً.

5. **`training_companies` (شركات التدريب المعتمدة)**
   - **الحقول الأساسية:** `name`, `registration_number`, `contact_email`, `status`.
   - **التقاطعات:** ترتبط الشركات بالتخصصات المطلوبة من المعلمين عبر `company_job_profiles`.

6. **الجداول المشتركة (Pivot Tables):**
   - `campus_program`: البرامج التدريبية المتاحة في كل فرع.
   - `track_responsibles` و `job_profile_responsibles`: الموظفين/المديرين المسؤولين عن مراقبة وإدارة تخصصات معينة `user_id`.

---

## 👥 المحور الثالث: المستخدمين والملفات (Profiles & Users)
جداول تفصل الملفات الخاصة لكل من الطلاب والمحاضرين، بحيث تكون مرتبطة بـ `users` (المستقرة خارج هذه السكيمة للتوثيق).

1. **`trainee_profiles` (ملفات المتدربين / الطلاب)**
   - **مفاتيح الربط الأساسية:** `user_id`، `program_id`، `group_id`، `job_profile_id`، `governorate_id`.
   - **بيانات ديموغرافية:** `national_id`, `arabic_name`, `english_name`, `date_of_birth`, `gender`, `nationality`.
   - **بيانات تخصصية:** `enrollment_status` (خريج/منسحب/منتظم)، `military_number`، `device_code` (خاص بأجهزة البصمة).
   - **لتقارير الطلاب:** هذا هو الجدول الأم لأي استعلام يخص إحصائيات الطلاب الديموغرافية والأكاديمية.

2. **الجداول التابعة للمتدرب (Trainee Dependents):**
   - `trainee_documents` (المستندات، حجمها ونوعها).
   - `trainee_emergency_contacts` (جهات الاتصال للطوارئ والقرابة).

3. **`instructor_profiles` (ملفات المحاضرين / المدربين)**
   - **مفاتيح الربط الأساسية:** `user_id`، `track_id`، `governorate_id`.
   - **البيانات التفصيلية:** `bio` (السيرة)، `employment_type` (نوع التوظيف: متفرغ، جزئي)، `specialization_notes` (ملاحظات التخصص)، والبيانات الشخصية كالمتدرب.

4. **الجداول التابعة للمدرب:**
   - `instructor_company_assignments`: الربط بين المحاضر وشركة التطبيق `company_id`.
   - `instructor_session_types`: أنواع الجلسات التي يستطيع المدرب تقديمها `session_type_id`.

5. **`governorates` (المحافظات والجهات):**
   - `name_ar`, `name_en` تستخدم كمرجع للطلاب والمدربين `governorate_id`.

---

## 🗓️ المحور الرابع: الجدولة والمحاضرات والحضور (Scheduling & Lectures)
هذا هو المحور التشغيلي الذي يكثر سحب التقارير اليومية منه بشكل مكثف.

1. **`session_types` (أنواع الجلسات/المحاضرات)**
   - **الحقول:** `name`، `description`. (أمثلة: عملي، نظري، اختبار).

2. **`schedule_templates` (قوالب الجدولة المؤتمتة)**
   - يقوم بتعريف جدول ثابت يتم منه توليد المحاضرات الفعلية.
   - **البيانات الزمنية:** `day_of_week`، `start_time`، `end_time`، `recurrence_type` (تكرار أسبوعي/زوجي/فردي)، `effective_from`، `effective_until`.
   - **روابط الكيانات:** `program_id`, `group_id`, `instructor_profile_id`, `room_id`, `session_type_id`.

3. **[lectures](file:///run/media/azouz/15ca02f9-a669-4c98-aeae-5b67cf6ee8bb1/azouz/digilians/TS-V.2%20ai/app/Modules/Educational/Domain/Models/Group.php#34-38) (المحاضرات الفعلية)**
   - **أهم جدول في النظام** لأعمال المتابعة اللحظية. هو ما ولده قالب الجدولة لتاريخ ووقت بعينه.
   - **مفاتيح زمنية ومحلية:** `starts_at` و `ends_at` (الوقت الفعلي للمحاضرة بالتاريخ)، `status` (مكتملة، قادمة، ملغاة).
   - **روابط الكيانات المُسندة:** البرنامج، المجموعة، المحاضر، القاعة، والمراقب `supervisor_id`.

4. **[attendances](file:///run/media/azouz/15ca02f9-a669-4c98-aeae-5b67cf6ee8bb1/azouz/digilians/TS-V.2%20ai/app/Modules/Educational/Domain/Models/Lecture.php#62-66) (سجل الحضور والغياب)**
   - **تقارير الحضور الدقيقة جداً!**
   - **البيانات:** `lecture_id` (المحاضرة)، `trainee_profile_id` (المتدرب)، `status` (حاضر، غائب، عذر، تأخير)، `check_in_time` (وقت البصمة)، `locked_at` (زمن الإقفال لضمان عدم التلاعب السهل).

> 💡 **فكرة تقرير (متابعة نسب الحضور):** لاستخراج غياب طالب معين، تقوم بربط جدول [attendances](file:///run/media/azouz/15ca02f9-a669-4c98-aeae-5b67cf6ee8bb1/azouz/digilians/TS-V.2%20ai/app/Modules/Educational/Domain/Models/Lecture.php#62-66) مع [lectures](file:///run/media/azouz/15ca02f9-a669-4c98-aeae-5b67cf6ee8bb1/azouz/digilians/TS-V.2%20ai/app/Modules/Educational/Domain/Models/Group.php#34-38) لمعرفة تفاصيل المادة والتاريخ، ومع `trainee_profiles` لمعرفة اسم الطالب والمجموعة.

---

## ⭐ المحور الخامس: التقييمات والجودة (Evaluations)
لمتابعة تقييم الجودة (للمكان، للمدرب، للمقرر).

1. **`evaluation_forms` (نماذج التقييم الهيكلية)**
   - `title`, `type`، وتحمل الاستبيانات التي يشرف عليها المركز.

2. **`evaluation_questions` (الأسئلة داخل النماذج)**
   - تحمل نصوص الأسئلة، ونوع الإجابة والخيارات داخل حقل الـ `options` بالـ JSON إذا كانت اختيارات متعددة.

3. **`lecture_form_assignments` (إسناد التقييم للمحاضرة)**
   - يربط نموذجا معينا `form_id` لتقييمه في محاضرة محددة `lecture_id`، ويحدد من يملك الصلاحية للتقييم في `allow_evaluator_types`.

4. **`lecture_evaluations` (مخرجات التقييم المسلمة)**
   - النموذج الذي سلمه الـ (`evaluator_id`). يحفظ ملاحظات عامة `overall_comments`، وتاريخ التسليم `submitted_at`. ولقد تم وضع حقل `form_snapshot` لحفظ نسخة لحظية لشكل النموذج منعاً لتعديات الماضي.

5. **`evaluation_answers` (الإجابات والتنقيط)**
   - يربط التقييم המסلم `lecture_evaluation_id` بكل سؤال `question_id` مع تسجيل الإجابة كـ `answer_value` أو تقييم رقمي `answer_rating`.

---

## 🚀 كيفية توظيف هذا المرجع في التقارير المتقدمة (SQL Joins Examples)

**إذا طُلب منك تقرير عن "ساعات محاضرات كل مدرب في شهر معين":**
```sql
SELECT
    ip.arabic_name AS instructor_name,
    COUNT(l.id) AS total_lectures,
    SUM(EXTRACT(EPOCH FROM (l.ends_at - l.starts_at))/3600) AS total_hours
FROM education.lectures l
JOIN education.instructor_profiles ip ON l.instructor_profile_id = ip.id
WHERE l.starts_at >= '2026-03-01' AND l.ends_at < '2026-04-01'
AND l.status != 'cancelled'
GROUP BY ip.id, ip.arabic_name
ORDER BY total_hours DESC;
```

**إذا طُلب منك تقرير عن "الطلاب الذين تجاوز غيابهم ٣ مرات في برنامج معين":**
```sql
SELECT
    tp.arabic_name AS trainee_name,
    g.name AS group_name,
    COUNT(a.id) AS absent_count
FROM education.attendances a
JOIN education.trainee_profiles tp ON a.trainee_profile_id = tp.id
JOIN education.groups g ON tp.group_id = g.id
WHERE a.status = 'absent' AND tp.program_id = ?
GROUP BY tp.id, tp.arabic_name, g.name
HAVING COUNT(a.id) >= 3;
```

هذا المرجع سيجعلك قادراً على صياغة أي هيكلة واستخلاص أي تقرير مالي، إداري أو أكاديمي بدقة متناهية من قاعدة البيانات!
