# Education System Database Architecture & Technical Documentation
# الوثيقة الفنية والبنية المعمارية لقاعدة بيانات النظام التعليمي

This document provides a comprehensive, professional, and structured technical documentation for the `education` schema in the Digilians TS-V.2 application, written for senior software engineers and database administrators.
تهدف هذه الوثيقة إلى توفير مرجع فني تقني شامل واحترافي لسكيمة `education`، مصمم خصيصاً لمهندسي البرمجيات ومديري قواعد البيانات.

---

## 1. Domain: Facilities Management (إدارة المنشآت)

### Table: `rooms` | جدول: القاعات
**Business Purpose / الغرض التجاري:** 
Tracks physical rooms, labs, and halls where lectures take place. It manages capacity and real-time room status to prevent overbooking.
يُتَبع القاعات الجغرافية والمعامل والمدرجات التي تُعقد فيها المحاضرات، ويدير سعتها التحيعية وحالتها لمنع الحجوزات المزدوجة.

**Relationships / العلاقات:**
- `belongsTo`: `floors` (Floor / الدور), `room_types` (Room Type / نوع القاعة)
- `hasMany`: [lectures](/app/Modules/Educational/Domain/Models/Group.php#34-38) (محاضرات), `schedule_templates` (قوالب الجدولة)

**Columns / الحقول:**
| Column Name (الحقل) | Data Type (النوع) | Nullable | Default | FK | Enum | Indexed | Description (الوصف) |
|---|---|---|---|---|---|---|---|
| `id` | bigint | No | Auto | No | No | Yes (PK) | Unique Identifier / المعرف |
| `floor_id` | bigint | No | - | Yes | No | Yes | Reference to Floor / رقم الدور |
| `room_type_id` | bigint | Yes | - | Yes | No | Yes | Reference to Room Type / مرجع نوع القاعة |
| `name` | varchar(255) | No | - | No | No | No | Room Name (e.g., Einstein Hall) / اسم القاعة |
| `code` | varchar(255) | No | - | No | No | Yes | Internal Code / كود القاعة الداخلي |
| `capacity` | integer | No | - | No | No | No | Max capacity of students / السعة القصوى للمتدربين |
| `room_status` | varchar(255) | No | 'active' | No | Yes | No | Readiness status / حالة الجاهزية |
| `room_type` | varchar(255) | No | - | No | Yes | No | (Legacy/Fallback) Type of room / نوع القاعة القديم |

**Enum Values / القيم الثابتة:**
**Table: `room_status`**
| Value (القيمة) | Meaning (المعنى) |
|---|---|
| `active` | Room is ready and can be booked / القاعة جاهزة ومتاحة للحجز |
| `maintenance` | Room is under maintenance, cannot be booked / تحت الصيانة، مغلقة للحجز |
| `disabled` | Room is permanently closed or out of service / خارج الخدمة |

**Security Implications & Constraints / الأمان والقيود:**
- **Business Constraints:** A room cannot be hard-deleted if it has past lectures. It must be set to `disabled`. (لا يمكن حذف قاعة إذا كان بها محاضرات سابقة، يجب تغيير حالتها إلى معطلة).
- **Validation:** Overlapping bookings for the same `room_id` at the same `starts_at`/`ends_at` must be strictly prevented.

---

## 2. Domain: Academic Structure (الهيكل الأكاديمي)

### Table: `programs` | جدول: البرامج التدريبية
**Business Purpose / الغرض التجاري:**
Represents a major educational offering (e.g., Diploma of Business Administration 2026). It dictates the full timeframe of study.
يمثل المظلة التعليمية الكبرى (مثل دبلومة إدارة الأعمال)، ويحدد الإطار الزمني للكلية.

**Relationships / العلاقات:**
- `belongsToMany`: [campuses](/app/Modules/Educational/Domain/Models/Program.php#18-27) (الفروع)
- `hasMany`: [groups](/app/Modules/Educational/Domain/Models/Program.php#33-37) (المجموعات), [lectures](/app/Modules/Educational/Domain/Models/Group.php#34-38) (المحاضرات), `trainee_profiles` (الطلاب)

**Columns / الحقول:**
| Column Name | Data Type | Null | Default | FK | Enum | Indexed | Description |
|---|---|---|---|---|---|---|---|
| `id` | bigint | No | Auto | No | No | Yes(PK)| Identifier / المعرف |
| `name` | varchar | No | - | No | No | No | Program Name / اسم البرنامج |
| `status` | varchar | No | 'draft' | No | Yes | Yes | Program state / حالة البرنامج |
| `starts_at` | date | Yes| - | No | No | Yes | Global start date / تاريخ البدء |
| `ends_at` | date | Yes| - | No | No | Yes | Global end date / تاريخ الانتهاء |

**Enum Values / القيم الثابتة:**
**Table: `programs.status`**
| Value | Meaning (المعنى) |
|---|---|
| `draft` | Being planned, hidden from public / مسودة غير معلنة |
| `published` | Open for enrollment / متاح للتسجيل |
| `running` | Currently active / البرنامج قيد التنفيذ حالياً |
| `completed`| Finished successfully / انتهى بنجاح |
| `archived` | Archived for history / مؤرشف |

---

### Table: [groups](/app/Modules/Educational/Domain/Models/Program.php#33-37) | جدول: المجموعات الافتراضية
**Business Purpose / الغرض التجاري:**
Divides program enrollees into physical or virtual classrooms (e.g., Group A vs Group B) to respect room capacities.
تقسيم الطلاب داخل البرنامج إلى فصول ومجموعات لضمان عدم تجاوز سعة القاعات وتوزيع المدربين.

**Relationships / العلاقات:**
- `belongsTo`: `programs`, `job_profiles`
- `hasMany`: `trainee_profiles`, [lectures](/app/Modules/Educational/Domain/Models/Group.php#34-38)

**Columns / الحقول:**
| Column Name | Data Type | Null | Default | FK | Enum | Indexed | Description |
|---|---|---|---|---|---|---|---|
| `id` | bigint | No | Auto | No | No | Yes(PK)| Identifier / المعرف |
| `program_id` | bigint | No | - | Yes| No | Yes | Belongs to Program / مرجع البرنامج |
| `name` | varchar | No | - | No | No | No | Group Name / اسم المجموعة |
| `capacity` | integer | No | 20 | No | No | No | Maximum enrollees / سعة المجموعة |

---

## 3. Domain: User Profiles (الملفات الشخصية)

### Table: `trainee_profiles` | جدول: ملفات المتدربين (الطلاب)
**Business Purpose / الغرض التجاري:**
Extends the global `users` table to add specific academic, administrative, and demographic details for Trainees.
يمتد من جدول المستخدمين الأساسي ليضيف البيانات الأكاديمية والطبية والدراسية الخاصة بالمتدربين لحساباتهم.

**Relationships / العلاقات:**
- `belongsTo`: `users` (النظام الأساسي), `programs`, [groups](/app/Modules/Educational/Domain/Models/Program.php#33-37), `governorates`
- `hasMany`: [attendances](/app/Modules/Educational/Domain/Models/Lecture.php#62-66) (الحضور), `trainee_documents` (المستندات)

**Columns / الحقول:**
| Column Name | Data Type | Null | Default | FK | Enum | Indexed | Description |
|---|---|---|---|---|---|---|---|
| `id` | bigint | No | Auto | No | No | Yes(PK)| Identifier / المعرف |
| `user_id` | bigint | No | - | Yes| No | Yes(UQ)| Auth user ID / مرجع المستخدم بالنظام |
| `program_id` | bigint | Yes| - | Yes| No | Yes | Current Program / برنامجه الحالي |
| `group_id` | bigint | Yes| - | Yes| No | Yes | Current Group / مجموعته الحالية |
| `national_id`| text | Yes| - | No | No | Yes | ID for Government tracking / الرقم القومي |
| `enrollment_status`|varchar | No |'active'| No | Yes| Yes | Academic state / الحالة الأكاديمية |
| `gender` | varchar | Yes| - | No | Yes| No | (male/female) / الجنس |

**Enum: `enrollment_status`**
| Value | Meaning / المعنى |
|---|---|
| `active` | Currently studying / يدرس بانتظام |
| `on_leave`| Temporarily postponed study / إجازة رسمية |
| `graduated`| Finished all requirements / خريج |
| `withdrawn`| Left the institution / منسحب |
| `suspended`| Punished or admin blocked / موقوف |

---

### Table: `instructor_profiles` | جدول: ملفات المدربين
**Business Purpose / الغرض التجاري:**
Maintains data for teaching staff, including employment forms and specialties.
ملفات خاصة بالهيئة التدريسية، تحدد تخصصهم الأكاديمي ونوع عقودهم.

**Relationships / العلاقات:**
- `belongsTo`: `users`, `tracks` (التخصص الأكاديمي)
- `hasMany`: [lectures](/app/Modules/Educational/Domain/Models/Group.php#34-38) (المحاضرات التي يدرسها)

**Columns / الحقول:**
| Column Name | Data Type | Null | Default | FK | Enum | Indexed | Description |
|---|---|---|---|---|---|---|---|
| `user_id` | bigint | No | - | Yes| No | Yes(UQ)| Linked system user / مرجع المستخدم |
| `employment_type` | varchar | No |'contractor'| No | Yes| No | Type of hire (full_time, part_time, contractor) / نوع التعاقد |
| `status` | varchar | No |'active'| No | Yes| Yes | active/inactive/suspended / حالة العمل |

---

## 4. Domain: Operations & Scheduling (المحاضرات والجدولة)

### Table: [lectures](/app/Modules/Educational/Domain/Models/Group.php#34-38) | جدول: المحاضرات (الأهم تشغيلياً)
**Business Purpose / الغرض التجاري:**
The core transactional table of the system. It locks a specific Group, Room, and Instructor in time (Start-End).
العمود الفقري للنظام التشغيلي. يضمن تخصيص (مجموعة، مدرب، قاعة) في زمان ومكان محددين دون تعارض.

**Relationships / العلاقات:**
- `belongsTo`: `programs`, [groups](/app/Modules/Educational/Domain/Models/Program.php#33-37), `instructor_profiles`, `rooms`, `session_types`
- `hasMany`: [attendances](/app/Modules/Educational/Domain/Models/Lecture.php#62-66) (غياب وحضور), `lecture_evaluations` (تقييمات المحاضرة)

**Columns / الحقول:**
| Column Name | Data Type | Null | Default | FK | Enum | Indexed | Description |
|---|---|---|---|---|---|---|---|
| `id` | bigint | No | Auto | No | No | Yes(PK)| Identifier / المعرف |
| `group_id` | bigint | No | - | Yes| No | Yes | Students attending / المجموعة الحاضرة |
| `room_id` | bigint | Yes| - | Yes| No | Yes | Physical location / مكان الانعقاد |
| `instructor_profile_id`| bigint | Yes| - | Yes| No | Yes | Teacher / المحاضر المسند إليه |
| `starts_at` | timestamp | No | - | No | No | Yes | Exact Start time / البداية الفعلية |
| `ends_at` | timestamp | No | - | No | No | Yes | Exact End time / النهاية الفعلية |
| `status` | varchar | No |'scheduled'| No | Yes| Yes | Session status / حالة المحاضرة |

**Enum: `lectures.status`**
| Value | Meaning / المعنى |
|---|---|
| `scheduled` | Upcoming / مجدولة ولم تبدأ بعد |
| `running` | Happening currently / قيد التشغيل والتبصيم الآن |
| `completed`| Finished completely / انتهت وتم الحضور |
| `cancelled`| Cancelled permanently / ملغاة |

**Security & Business Constraints / الأمان والقيود:**
1. **Overlap Prevention**: Logic strictly prevents `group_id` from overlapping in time. `instructor_profile_id` and `room_id` overlaps yield warnings but do not hard-block.
2. **Attendance Lock**: Once marked `completed` and attendances are finalized, historical alterations should require an Audit log.

---

### Table: [attendances](/app/Modules/Educational/Domain/Models/Lecture.php#62-66) | جدول: سجل الحضور
**Business Purpose / الغرض التجاري:**
Captures micro-transactions of student presence. Essential for grading, certifications, and warnings.
يسجل حركات دخول المتدربين. أساسي للشهادات والحرمان وإحصائيات الغياب.

**Columns / الحقول:**
| Column Name | Data Type | Null | Default | FK | Enum | Indexed | Description |
|---|---|---|---|---|---|---|---|
| `lecture_id` | bigint | No | - | Yes| No | Yes | Which lecture / أي محاضرة |
| `trainee_profile_id` | bigint | No| - | Yes| No | Yes | Which student / من هو الطالب |
| `status` | varchar | No |'absent'|No | Yes| Yes | (present, absent, late, excused) / حالة الحضور |
| `check_in_time`| time | Yes| - | No | No | No | Exact physical check-in / ساعة التبصيم |
| `locked_at` | timestamp| Yes| - | No | No | No | Prevents further edits / إقفال التعديل للنزاهة |

---

## 5. Domain: Evaluations (نظام التقييم والجودة)

### Architecture of Evaluations / هندسة الجودة والتقييم:
1. **`evaluation_forms`**: Definers (What are we evaluating? e.g., Instructor mid-term Evaluation).
2. **`evaluation_questions`**: JSON-powered flexible structures (`type: rating, text, multiple_choice`).
3. **`lecture_evaluations`**: The physical paper submitted by someone (`evaluator_role: trainee, observer, admin`). Soft-saves the structure to `form_snapshot` to freeze history.
4. **`evaluation_answers`**: Key-Value answers to questions linked to `lecture_evaluations`.

*(Detailed columns excluded for brevity, follow standard polymorphic submission structures)*.

---

## 6. Architecture & Data Flow Summary (ملخص المعمارية وتدفق البيانات)

### 📊 Data Flow (تدفق البيانات):
1. **Setup**: Admins define `Campuses` \(\rightarrow\) `Buildings` \(\rightarrow\) `Rooms`.
2. **Academic Structure**: Admins define `Programs` and [Groups](/app/Modules/Educational/Http/Controllers/Web/ScheduleTemplateController.php#207-212), then assign [Trainees](/app/Modules/Educational/Domain/Models/Lecture.php#77-88).
3. **Automated Planning**: `schedule_templates` define the recurring weekly rhythm (e.g., Every Sunday 9-12 for Group A).
4. **Execution Engine**: The System generates tangible [lectures](/app/Modules/Educational/Domain/Models/Group.php#34-38) from templates for a specific time range.
5. **Real-time Engine**: `Attendances` are gathered against [lectures](/app/Modules/Educational/Domain/Models/Group.php#34-38), and `Lecture_Evaluations` are dispatched to students to rate.

### 📈 Mermaid ERD (مخطط العلاقات الهندسي)

```mermaid
erDiagram
    %% Core Entities %%
    CAMPUSES ||--o{ BUILDINGS : "has"
    BUILDINGS ||--o{ ROOMS : "contains (via floors)"
    PROGRAMS ||--o{ GROUPS : "manages"
    
    %% Users & Profiles %%
    GROUPS ||--o{ TRAINEE_PROFILES : "enrolls"
    INSTRUCTOR_PROFILES ||--o|{ LECTURES : "teaches"
    
    %% Heartbeat: Lectures %%
    ROOMS ||--o{ LECTURES : "hosts"
    GROUPS ||--o{ LECTURES : "attends"
    
    %% Transactions %%
    LECTURES ||--o{ ATTENDANCES : "records"
    TRAINEE_PROFILES ||--o{ ATTENDANCES : "creates"
    
    LECTURES ||--o{ LECTURE_EVALUATIONS : "receives feedback"
    EVALUATION_FORMS ||--o{ LECTURE_EVALUATIONS : "template of"
```

---

## 7. Strategic Analysis: Risks, Scalability & Improvements
## (التحليل الاستراتيجي التوسعي: المخاطر والتحسينات)

### ⚠️ Potential Design Risks (المخاطر التصميمية المحتملة):
1. **JSON in `evaluation_questions.options`**: Querying text directly inside JSON is slow in high-volume situations. If analytics on dropdown answers are frequently run, it's a risk.
2. **[attendances](/app/Modules/Educational/Domain/Models/Lecture.php#62-66) vs Biometric Devices**: Synchronizing 500+ physical machine check-ins simultaneously requires robust Job Queues; doing it synchronously in [attendances](/app/Modules/Educational/Domain/Models/Lecture.php#62-66) table updates could deadlock.

### 🚀 Scalability Concerns (مخاوف التوسع والأداء):
1. **The [lectures](/app/Modules/Educational/Domain/Models/Group.php#34-38) Table Bloat**: At 10,000 students × 5 lectures/week × 52 weeks = 2.6 Million attendance rows per year. 
   - **Solution**: Implement **Table Partitioning** on [attendances](/app/Modules/Educational/Domain/Models/Lecture.php#62-66) by academic year/semester (`partition by range(created_at)`).
2. **Overlap Validation Performance**: [checkScheduleOverlap](/app/Modules/Educational/Http/Controllers/Web/ScheduleTemplateController.php#213-265) query does multiple OR conditions over unindexed JSON constraints. 
   - **Solution**: Postgres **GIST / Range types (`tsrange`)** for `starts_at` / `ends_at` to do lightning-fast geometric overlap checks instead of traditional `<= >=` logic.

### 📉 Missing Indexes (فهارس مفقودة مقترحة للمراجعة):
While standard FKs are indexed, composite indexes are vital here:
- `CREATE INDEX idx_lectures_group_time ON education.lectures(group_id, starts_at, ends_at);` (Critical for avoiding schedule overlaps).
- `CREATE INDEX idx_attendances_student_lecture ON education.attendances(trainee_profile_id, lecture_id);` (To enforce uniqueness of check-ins).
- `CREATE INDEX idx_lecture_evaluations_lecture_evaluator ON education.lecture_evaluations(lecture_id, evaluator_id);` (Prevent multiple form submissions).

### 💡 Suggested Improvements (التحسينات المقترحة):
1. **Soft Deletes Mastery**: Apply cascading soft deletes. Deleting a [Group](/app/Modules/Educational/Domain/Models/Group.php#10-49) must gracefully cascade a soft delete to future `Lectures`.
2. **Evaluations Versioning**: Relying on `form_snapshot` JSONB is clever, but having a `form_version_id` helps structure bulk BI (Business Intelligence) reporting better.
3. **Database Caching Layer (Redis)**: Cache the master lists (`Room_Types`, `Session_Types`) heavily, as they are queried on almost every single Page Load/API Call but rarely change.
