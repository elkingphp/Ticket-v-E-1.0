# Comprehensive Data Migration Plan (خطة نقل البيانات الشاملة المحدثة)
## From `ts_digi` (Legacy) to `digi_test` (TS-V.2) - Enterprise Architecture

### 1. Overview & Core Principles (نظرة عامة ومبادئ أساسية)
The goal is to migrate data from the legacy monolithic database (`ts_digi`) into the new micro-schema architecture (`digi_test`: public, education, tickets schemas) **while preserving the new system's default settings, roles, and permissions.**

الهدف هو الاستحواذ على ونقل البيانات من قاعدة البيانات القديمة (`ts_digi`) إلى الهيكلية المعمارية الجديدة (`digi_test`) **مع الحفاظ التام على الإعدادات، الصلاحيات، والأدوار الموجودة حالياً في النظام الجديد** وتطبيق معايير أداء الشركات الكبرى (Enterprise Performance).

**Key Principles (مبادئ استراتيجية):**
1. **Never Truncate Core Configs:** Tables like `roles`, `permissions`, `ticket_statuses`, and `ticket_priorities` will NOT be dropped.
2. **The `legacy_id` Column is King:** We will completely drop the use of any intermediary `migration_id_map` table to reduce complexity and maintenance overhead. Instead, we always rely directly on `legacy_id` dynamically (e.g., `SELECT id FROM users WHERE legacy_id = ?`).
3. **Massive Upsert Performance:** We will NOT use Laravel's slow `updateOrCreate()` which runs `SELECT` then `INSERT/UPDATE` for every row. We will use native PostgreSQL `INSERT ... ON CONFLICT (legacy_id) DO UPDATE` to handle millions of rows efficiently.
4. **Temporary Constraint Suspension:** PostgreSQL constraints and triggers will be suspended gracefully at the session level during heavy inserts using `SET session_replication_role = replica;` to avoid constraint loops and massive slowdowns.

---

### 2. The 9-Phase Enterprise Migration Strategy (استراتيجية النقل ذات التسع مراحل)

Data must be imported in a strict, audited, and resumable order.
يجب أن يتم نقل البيانات بترتيب هرمي صارم، مع إمكانية التدقيق والاستئناف من أي نقطة.

#### **Phase 0: Data Audit (تدقيق وتطهير البيانات ما قبل النقل)**
*Analyze the legacy data before anything moves to prevent migration disasters.*
*   **Identify Orphans:** Find `trainee` without a valid `user`, `attendance` without a `lecture`, `ticket` without a `category`.
*   **Identify Integrity Issues:** Check for NULL problems in mandatory fields, duplicate emails in identity tables, and invalid date strings.

#### **Phase 1: Foundation & Identity (المرحلة الأساسية والهوية)**
1. **Users:** Migrate `ts_digi.users` \(\rightarrow\) `public.users`.
2. **Roles Mapping (Spatie):** Read legacy `role_users`. Softly map users to Spatie roles by generic slug names (e.g., 'student'). Do **NOT** import legacy permissions.
3. **Geography:** Map `ts_digi.cities` \(\rightarrow\) `education.governorates`.

#### **Phase 2: Educational Infrastructure (البنية التحتية للتعليم)**
1. **Campuses & Rooms:** 
   - `branch_locations` \(\rightarrow\) `education.campuses`.
   - `class_rooms` \(\rightarrow\) `education.rooms` (Generate and inject a hidden default "Main Building" and "Ground Floor" to satisfy the new structural requirements).
2. **Academic Setup:** 
   - `study_programs` \(\rightarrow\) `education.programs`.
   - `tracks` \(\rightarrow\) `education.tracks`.
   - `training_providers` \(\rightarrow\) `education.training_companies`.
3. **Groups:** `class_groups` \(\rightarrow\) `education.groups`.

#### **Phase 3: Profiles (الملفات الشخصية)**
1. **Trainees:** `trainees` \(\rightarrow\) `education.trainee_profiles`.
   - Resolve `user_id` by matching the identity imported in Phase 1 via `legacy_id`.
2. **Instructors:** `instructors` \(\rightarrow\) `education.instructor_profiles`.

#### **Phase 4: Education Operations (العمليات والمحاضرات التعليمية)**
1. **Lectures:** `academic_schedules` \(\rightarrow\) `education.lectures`.
   - Merge `date`, `start_time`, `end_time` securely into Postgres `timestamp`.
2. **Attendances:** 
   - `attendance_days` \(\rightarrow\) Maps to the session/lecture entity (used for resolving schedules).
   - `attendance_trainees` \(\rightarrow\) `education.attendances` (The actual participant recording).

#### **Phase 5: Tickets (نظام التذاكر)**
1. **Mapping by Slug:** Map Taxonomies, Statuses, and Priorities via **Slugs**, NOT IDs. 
   - *Example:* Instead of `1 => 3`, use `'open' => 'new'`, `'closed' => 'resolved'`. Slugs survive architectural changes.
2. **Tickets:** `ticket_headers` \(\rightarrow\) `tickets.tickets`. Generate `gen_random_uuid()` (Requires `pgcrypto` extension) for the missing UUID field.
3. **History:** `ticket_detals` \(\rightarrow\) `tickets.ticket_threads` & `ticket_histories` \(\rightarrow\) `tickets.ticket_activities`.

#### **Phase 6: Data Validation & Checksums (تأكيد النقل والشهادة)**
Post-migration verification to guarantee 100% data integrity.
*   **Row Counts:** Ensure `legacy.users == new.users`.
*   **Checksums:** Run a hash checksum check over critical columns (e.g., aggregate of all emails) to ensure zero corruption.

#### **Phase 7: Index Rebuild & Analyze (إعادة بناء الفهارس وضبط الأداء)**
The massive influx of records will fragment indexes. 
*   Run Postgres `REINDEX SCHEMA tickets; REINDEX SCHEMA education; REINDEX SCHEMA public;`
*   Run Postgres `VACUUM ANALYZE;` to let the planner learn the new statistics.

#### **Phase 8: Integrity Verification (التأكد من التناسق المرجعي)**
A final automated pass to guarantee FK and Data matching.
*   **Row Integrity:** `SELECT COUNT(*) FROM legacy.users;` vs `SELECT COUNT(*) FROM public.users;`
*   **FK Integrity Check:** Ensure every `ticket` has a valid `category_id`, and every `attendance` links to a valid `lecture_id`.

---

### 3. Advanced Engineering Solutions (حلول برمجية متقدمة للأداء والموثوقية)

#### A. PostgreSQL Specific Bulk Upserting (التعامل مع ملايين السجلات)
Instead of processing memory-heavy ORM functions, we execute raw PG queries for bulk data:
```sql
INSERT INTO users (legacy_id, email, name, created_at, updated_at)
VALUES (?, ?, ?, ?, ?)
ON CONFLICT (legacy_id) 
DO UPDATE SET 
    email = EXCLUDED.email, 
    name = EXCLUDED.name,
    updated_at = EXCLUDED.updated_at;
```
*Chunking Size:* Data will be pushed in blocks of `2000` to `5000` rows per transaction depending on RAM, significantly faster than typical 100-row chunks.

#### B. Safe Constraint Suspension Disable (التعطيل الصحيح للقيود)
Before starting major data dumps, we instruct Postgres to hold firing triggers and FK validations:
```sql
-- At the start of the chunk execution
SET session_replication_role = replica;

-- At the end of the chunk execution
SET session_replication_role = DEFAULT;
```

#### C. Migration Logs for Tracking & Resuming (سجل التتبع والاستئناف)
A new dedicated table `migration_logs` will be generated before starting:
| Column | Type | Description |
|---|---|---|
| `id` | bigserial | |
| `entity_name` | string | e.g. "users", "attendances" |
| `processed_rows` | int | How many succeeded so far |
| `failed_rows` | int | How many failed |
| `status` | string | running, completed, failed |
| `started_at` | timestamp | |
| `finished_at`| timestamp | |

This table heavily impacts debugging and allows picking up right where we left off.

#### D. Specialized Console Command Flags (خيارات الشاشة السوداء)
The CLI commands will include critical operational flags:

1. **Dry Run Mode (المحاكاة):**
   ```bash
   php artisan migrate:legacy --dry-run
   ```
   Simulates `Phase 0` and mapping, writing output logs without actually committing an `INSERT`, letting engineers preview errors.

2. **Resume Capability (الاستئناف):**
   ```bash
   php artisan migrate:legacy --resume=phase4
   ```
   If the migration process kills halfway inside lectures (Phase 4), this forces it to skip Identity and Infrastructure and safely resume exactly from Operations.
