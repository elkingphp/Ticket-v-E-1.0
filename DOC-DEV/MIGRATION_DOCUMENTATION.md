# Migration Documentation: Legacy to Modular System

## Phase 1: Schema Mapping

### Core Entities

| Table (Legacy) | Table (New) | Mapping Logic |
| :--- | :--- | :--- |
| `cities` | `education.governorates` | Identical IDs, name mapping. |
| `branch_locations` | `education.campuses` | Filter `branch_type = 'LOCATION'`. |
| `branch_locations` | `education.buildings` | Filter `branch_type = 'BUILDING'`, parent is campus. |
| `branch_locations` | `education.floors` | Filter `branch_type = 'FLOOR'`, parent is building. |
| `class_rooms` | `education.rooms` | Linked to floors/buildings. |
| `study_programs` | `education.programs` | Code generation from name if missing. |
| `tracks` | `education.tracks` | Slugified name for unique codes. |
| `training_providers` | `education.training_companies` | Mapping contact details and status. |

### User System

| Table (Legacy) | Table (New) | Mapping Logic |
| :--- | :--- | :--- |
| `users` | `users` | Preserve ID. Resolve `username`/`phone` conflicts by appending ID. Default password/email fallback. |
| `instructors` | `education.instructor_profiles` | Link to `users`. Create user if missing. |
| `trainees` | `education.trainee_profiles` | Link to `users`. Create user if missing. |

### Educational Activity

| Table (Legacy) | Table (New) | Mapping Logic |
| :--- | :--- | :--- |
| `class_groups` | `education.groups` | Status mapping, capacity mapping. |
| `group_trainees` | `education.group_trainee` | Enrollment preservation. |
| `attendance_days` | `education.lectures` | Time mapping (`time` -> `starts_at`, `to_time` -> `ends_at`). |
| `attendance` | `education.attendances` | Trainee profile link. |

### Support System (Tickets)

| Table (Legacy) | Table (New) | Mapping Logic |
| :--- | :--- | :--- |
| `ticket_headers` | `tickets.tickets` | `ticket_number` resolved with ID suffix. `subject`/`details` mapping. |
| `ticket_detals` | `tickets.ticket_threads` | Thread history preservation. |

### Audit/Evaluation

| Table (Legacy) | Table (New) | Mapping Logic |
| :--- | :--- | :--- |
| `audit_question` | `education.evaluation_questions` | Question text, type, options. |
| `form_auditing` | `education.evaluation_forms` | Form metadata. |
| `answer_form` | `education.evaluation_answers` | Answer data (text/choices/yes_no). |

## Phase 2: ID Mapping Strategy
A persistent `migration_id_map` table is used to track `(table_name, old_id) -> new_id`. Highly critical for multi-pass migrations and maintaining referential integrity across schemas.

## Phase 3: Ordered Migration
Migration follows strict dependency order to avoid foreign key violations:
1. Governorates
2. Campuses -> Buildings -> Floors -> Rooms
3. Programs -> Tracks -> Job Profiles
4. Companies
5. Users & Roles
6. Instructor & Trainee Profiles
7. Groups & Enrollments
8. Lectures & Attendance
9. Tickets & Threads
10. Evaluations
