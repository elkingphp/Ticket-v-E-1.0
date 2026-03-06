# TS_DIGI Schema Summary

## Table: `academic_schedules`
- id (bigint)
- branch_location_id (bigint)
- study_program_id (bigint)
- class_group_id (bigint)
- class_room_id (bigint)
- date (date)
- day (character varying(16))
- lecture_number (integer)
- number_day (integer)
- number_week (integer)
- start_time (time without time zone)
- end_time (time without time zone)
- status (character varying(255))
- status_updated_at (timestamp without time zone) [NULL]
- session_type_id (bigint)
- updated_by (bigint) [NULL]
- created_by (bigint)
- deleted_at (timestamp without time zone) [NULL]
- created_at (timestamp without time zone) [NULL]
- updated_at (timestamp without time zone) [NULL]

## Table: `answer_form`
- id (bigint)
- attendance_day_id (bigint)
- form_auditing_id (bigint)
- audit_question_id (bigint)
- answer_text (character varying(1024)) [NULL]
- answer_choices (character varying(255)) [NULL]
- answer_yes_no (boolean) [NULL]
- answer_file (character varying(255)) [NULL]
- user_id (bigint)
- key_bucket (uuid) [NULL]
- status (character varying(255)) [NULL]
- deleted_at (timestamp without time zone) [NULL]
- created_at (timestamp without time zone) [NULL]
- updated_at (timestamp without time zone) [NULL]

## Table: `assign_audit`
- id (bigint)
- attendance_day_id (bigint)
- assign_to_user_id (bigint)
- user_id (bigint)
- status (character varying(255))
- deleted_at (timestamp without time zone) [NULL]
- created_at (timestamp without time zone) [NULL]
- updated_at (timestamp without time zone) [NULL]

## Table: `attendance_days`
- id (bigint)
- day (character varying(255))
- time (time without time zone)
- date (date)
- status (character varying(255))
- session_type_id (bigint)
- branch_location_id (bigint)
- study_program_id (bigint)
- class_group_id (bigint)
- class_room_id (bigint)
- academic_schedule_id (bigint) [NULL]
- track_id (bigint)
- instructor_id (bigint)
- created_by (bigint)
- audit_status (character varying(255))
- to_time (time without time zone) [NULL]
- parent_id (bigint) [NULL]
- updated_by (bigint) [NULL]
- deleted_at (timestamp without time zone) [NULL]
- created_at (timestamp without time zone) [NULL]
- updated_at (timestamp without time zone) [NULL]

## Table: `attendance_reasons`
- id (bigint)
- attendance_day_id (bigint)
- attendance_trainee_id (bigint)
- trainee_id (bigint)
- reason_text (text)
- deleted_at (timestamp without time zone) [NULL]
- created_at (timestamp without time zone) [NULL]
- updated_at (timestamp without time zone) [NULL]

## Table: `attendance_trainees`
- id (bigint)
- status (character varying(255))
- trainee_id (bigint)
- attendance_day_id (bigint)
- session_type_id (bigint)
- branch_location_id (bigint) [NULL]
- study_program_id (bigint) [NULL]
- class_group_id (bigint) [NULL]
- is_active (boolean)
- deleted_at (timestamp without time zone) [NULL]
- created_at (timestamp without time zone) [NULL]
- updated_at (timestamp without time zone) [NULL]
- form_auditing_id (bigint) [NULL]
- key_bucket (uuid) [NULL]

## Table: `audit_question`
- id (bigint)
- question_ar (character varying(1024))
- question_en (character varying(1024)) [NULL]
- is_required (boolean)
- answer_type (character varying(255))
- choices (json) [NULL]
- user_id (bigint)
- status (character varying(255))
- deleted_at (timestamp without time zone) [NULL]
- created_at (timestamp without time zone) [NULL]
- updated_at (timestamp without time zone) [NULL]

## Table: `branch_locations`
- id (bigint)
- name (character varying(255))
- code (character varying(8)) [NULL]
- address_location (character varying(255)) [NULL]
- branch_type (character varying(255))
- parent_id (bigint) [NULL]
- created_by (bigint)
- deleted_at (timestamp without time zone) [NULL]
- created_at (timestamp without time zone) [NULL]
- updated_at (timestamp without time zone) [NULL]

## Table: `cache`
- key (character varying(255))
- value (text)
- expiration (integer)

## Table: `cache_locks`
- key (character varying(255))
- owner (character varying(255))
- expiration (integer)

## Table: `cities`
- id (bigint)
- name (character varying(255))
- code (character varying(255)) [NULL]
- created_by (bigint)
- deleted_at (timestamp without time zone) [NULL]
- created_at (timestamp without time zone) [NULL]
- updated_at (timestamp without time zone) [NULL]

## Table: `class_groups`
- id (bigint)
- round_group (character varying(32))
- name_round_group (character varying(32)) [NULL]
- round_number (integer)
- max_trainees (integer)
- track_id (bigint) [NULL]
- study_program_id (bigint) [NULL]
- class_room_id (bigint) [NULL]
- branch_location_id (bigint) [NULL]
- status (character varying(255))
- created_by (bigint)
- deleted_at (timestamp without time zone) [NULL]
- created_at (timestamp without time zone) [NULL]
- updated_at (timestamp without time zone) [NULL]

## Table: `class_rooms`
- id (bigint)
- name (character varying(255))
- capacity (integer) [NULL]
- branch_location_id (bigint)
- created_by (bigint)
- deleted_at (timestamp without time zone) [NULL]
- created_at (timestamp without time zone) [NULL]
- updated_at (timestamp without time zone) [NULL]

## Table: `failed_jobs`
- id (bigint)
- uuid (character varying(255))
- connection (text)
- queue (text)
- payload (text)
- exception (text)
- failed_at (timestamp without time zone)

## Table: `form_auditing`
- id (bigint)
- Subject_title (character varying(255))
- session_type_id (bigint)
- study_program_id (bigint) [NULL]
- class_group_id (bigint) [NULL]
- type_hall (character varying(255))
- track_id (bigint) [NULL]
- user_id (bigint)
- deleted_at (timestamp without time zone) [NULL]
- created_at (timestamp without time zone) [NULL]
- updated_at (timestamp without time zone) [NULL]

## Table: `group_trainees`
- id (bigint)
- status (character varying(255))
- branch_location_id (bigint) [NULL]
- study_program_id (bigint) [NULL]
- class_group_id (bigint) [NULL]
- building_id (bigint) [NULL]
- floor_id (bigint) [NULL]
- class_room_id (bigint) [NULL]
- trainee_id (bigint)
- created_by (bigint)
- deleted_at (timestamp without time zone) [NULL]
- created_at (timestamp without time zone) [NULL]
- updated_at (timestamp without time zone) [NULL]

## Table: `histories`
- id (bigint)
- serial (integer)
- created_by (bigint) [NULL]
- action (character varying(255))
- model_type (character varying(255))
- model_id (bigint) [NULL]
- old_data (json) [NULL]
- new_data (json) [NULL]
- ip_address (character varying(255)) [NULL]
- created_at (timestamp without time zone) [NULL]
- updated_at (timestamp without time zone) [NULL]

## Table: `instructors`
- id (bigint)
- name_ar (character varying(255))
- name_en (character varying(255))
- address (character varying(255)) [NULL]
- email (character varying(255)) [NULL]
- phone (character varying(32)) [NULL]
- nationalID (character varying(16)) [NULL]
- passport_number (character varying(16)) [NULL]
- birthdate (date) [NULL]
- gender (character varying(255))
- status (character varying(255))
- city_id (bigint) [NULL]
- created_by (bigint)
- deleted_at (timestamp without time zone) [NULL]
- created_at (timestamp without time zone) [NULL]
- updated_at (timestamp without time zone) [NULL]

## Table: `job_batches`
- id (character varying(255))
- name (character varying(255))
- total_jobs (integer)
- pending_jobs (integer)
- failed_jobs (integer)
- failed_job_ids (text)
- options (text) [NULL]
- cancelled_at (integer) [NULL]
- created_at (integer)
- finished_at (integer) [NULL]

## Table: `jobs`
- id (bigint)
- queue (character varying(255))
- payload (text)
- attempts (smallint)
- reserved_at (integer) [NULL]
- available_at (integer)
- created_at (integer)

## Table: `migrations`
- id (integer)
- migration (character varying(255))
- batch (integer)

## Table: `password_reset_tokens`
- email (character varying(255))
- token (character varying(255))
- created_at (timestamp without time zone) [NULL]

## Table: `permissions`
- id (bigint)
- title (character varying(255))
- description (character varying(255)) [NULL]
- name (character varying(255))
- value (character varying(32))
- group (character varying(255)) [NULL]
- created_at (timestamp without time zone) [NULL]
- updated_at (timestamp without time zone) [NULL]

## Table: `personal_access_tokens`
- id (bigint)
- tokenable_type (character varying(255))
- tokenable_id (bigint)
- name (character varying(255))
- token (character varying(64))
- abilities (text) [NULL]
- last_used_at (timestamp without time zone) [NULL]
- expires_at (timestamp without time zone) [NULL]
- created_at (timestamp without time zone) [NULL]
- updated_at (timestamp without time zone) [NULL]

## Table: `power_bis`
- id (bigint)
- name (character varying(255))
- url (character varying(255))
- created_by (bigint)
- created_at (timestamp without time zone) [NULL]
- updated_at (timestamp without time zone) [NULL]

## Table: `priority_tickets`
- id (bigint)
- name (character varying(255))
- is_hidden (boolean)
- created_by (bigint)
- deleted_at (timestamp without time zone) [NULL]
- created_at (timestamp without time zone) [NULL]
- updated_at (timestamp without time zone) [NULL]

## Table: `programs_in_locations`
- id (bigint)
- branch_location_id (bigint)
- study_program_id (bigint)
- created_by (bigint)
- status (character varying(255))
- created_at (timestamp without time zone) [NULL]
- updated_at (timestamp without time zone) [NULL]

## Table: `question_form`
- id (bigint)
- form_auditing_id (bigint)
- audit_question_id (bigint)
- order_question (integer)
- user_id (bigint)
- status (character varying(255))
- created_at (timestamp without time zone) [NULL]
- updated_at (timestamp without time zone) [NULL]

## Table: `reasons_audits`
- id (bigint)
- attendance_day_id (bigint)
- user_id (bigint)
- reason_title (character varying(255)) [NULL]
- reason_text (character varying(2048)) [NULL]
- deleted_at (timestamp without time zone) [NULL]
- created_at (timestamp without time zone) [NULL]
- updated_at (timestamp without time zone) [NULL]

## Table: `role_permissions`
- id (bigint)
- role_id (bigint)
- permission_id (bigint)
- created_by (bigint)
- created_at (timestamp without time zone) [NULL]
- updated_at (timestamp without time zone) [NULL]

## Table: `role_users`
- id (bigint)
- user_id (bigint)
- role_id (bigint)
- is_active (boolean)
- deleted_at (timestamp without time zone) [NULL]
- created_at (timestamp without time zone) [NULL]
- updated_at (timestamp without time zone) [NULL]

## Table: `roles`
- id (bigint)
- name (character varying(255))
- description (character varying(255)) [NULL]
- created_by (bigint)
- deleted_at (timestamp without time zone) [NULL]
- created_at (timestamp without time zone) [NULL]
- updated_at (timestamp without time zone) [NULL]

## Table: `session_types`
- id (bigint)
- name (character varying(255))
- status (character varying(255))
- created_by (bigint)
- deleted_at (timestamp without time zone) [NULL]
- created_at (timestamp without time zone) [NULL]
- updated_at (timestamp without time zone) [NULL]

## Table: `sessions`
- id (character varying(255))
- user_id (bigint) [NULL]
- ip_address (character varying(45)) [NULL]
- user_agent (text) [NULL]
- payload (text)
- last_activity (integer)

## Table: `status_tickets`
- id (bigint)
- name (character varying(255))
- is_hidden (boolean)
- created_by (bigint)
- deleted_at (timestamp without time zone) [NULL]
- created_at (timestamp without time zone) [NULL]
- updated_at (timestamp without time zone) [NULL]

## Table: `study_programs`
- id (bigint)
- name (character varying(255))
- number_months (integer) [NULL]
- start_date (date) [NULL]
- end_date (date) [NULL]
- created_by (bigint)
- deleted_at (timestamp without time zone) [NULL]
- created_at (timestamp without time zone) [NULL]
- updated_at (timestamp without time zone) [NULL]

## Table: `subject_tickets`
- id (bigint)
- name (character varying(255))
- sla_minutes (integer) [NULL]
- is_hidden (boolean)
- parent_id (bigint) [NULL]
- role_id (bigint) [NULL]
- created_by (bigint)
- deleted_at (timestamp without time zone) [NULL]
- created_at (timestamp without time zone) [NULL]
- updated_at (timestamp without time zone) [NULL]

## Table: `system_settings`
- id (bigint)
- name (character varying(255))
- value (character varying(255))
- created_at (timestamp without time zone) [NULL]
- updated_at (timestamp without time zone) [NULL]

## Table: `ticket_assign_tos`
- id (bigint)
- is_close (boolean)
- close_at (timestamp without time zone) [NULL]
- ticket_id (bigint)
- assign_to_role_id (bigint) [NULL]
- assign_to_user_id (bigint) [NULL]
- created_by (bigint)
- deleted_at (timestamp without time zone) [NULL]
- created_at (timestamp without time zone) [NULL]
- updated_at (timestamp without time zone) [NULL]

## Table: `ticket_detals`
- id (bigint)
- attachment (character varying(255)) [NULL]
- notes (text) [NULL]
- is_internal (boolean)
- role_access_id (bigint) [NULL]
- ticket_id (bigint)
- created_by (bigint)
- deleted_at (timestamp without time zone) [NULL]
- created_at (timestamp without time zone) [NULL]
- updated_at (timestamp without time zone) [NULL]

## Table: `ticket_headers`
- id (bigint)
- ticket_number (character varying(16))
- title (character varying(255))
- sub_subject_id (character varying(255)) [NULL]
- round_group (character varying(32)) [NULL]
- attachment (character varying(255)) [NULL]
- description (text) [NULL]
- round_number (integer) [NULL]
- request_provider (character varying(255))
- track_id (bigint) [NULL]
- priority_id (bigint)
- status_id (bigint)
- phase_id (bigint) [NULL]
- subject_id (bigint) [NULL]
- user_id (bigint)
- study_program_id (bigint) [NULL]
- created_by (bigint)
- deleted_at (timestamp without time zone) [NULL]
- created_at (timestamp without time zone) [NULL]
- updated_at (timestamp without time zone) [NULL]
- serial_number_pc (character varying(255)) [NULL]
- location_id (bigint) [NULL]
- building_id (bigint) [NULL]
- floor_id (bigint) [NULL]
- class_room_id (bigint) [NULL]
- attendance_day_id (bigint) [NULL]

## Table: `ticket_histories`
- id (bigint)
- serial (integer)
- type (character varying(64))
- old_value (character varying(128)) [NULL]
- new_value (character varying(128))
- category (character varying(32))
- ticket_id (bigint)
- created_by (bigint)
- deleted_at (timestamp without time zone) [NULL]
- created_at (timestamp without time zone) [NULL]
- updated_at (timestamp without time zone) [NULL]

## Table: `track_headers`
- id (bigint)
- track_id (bigint)
- study_program_id (bigint) [NULL]
- user_id (bigint)
- created_by (bigint)
- deleted_at (timestamp without time zone) [NULL]
- created_at (timestamp without time zone) [NULL]
- updated_at (timestamp without time zone) [NULL]

## Table: `tracks`
- id (bigint)
- code (character varying(16))
- name (character varying(255))
- type (character varying(255))
- status (character varying(255))
- parent_id (bigint) [NULL]
- study_program_id (bigint) [NULL]
- created_by (bigint)
- deleted_at (timestamp without time zone) [NULL]
- created_at (timestamp without time zone) [NULL]
- updated_at (timestamp without time zone) [NULL]

## Table: `trainees`
- id (bigint)
- name_ar (character varying(255))
- name_en (character varying(255))
- round_number (integer) [NULL]
- address (character varying(255)) [NULL]
- nationality (character varying(255)) [NULL]
- email (character varying(255))
- phone (character varying(32))
- nationalID (character varying(16)) [NULL]
- passport_number (character varying(16)) [NULL]
- round_group (character varying(32)) [NULL]
- birthdate (date) [NULL]
- gender (character varying(255))
- type (character varying(255))
- status (character varying(255))
- city_id (bigint) [NULL]
- track_id (bigint) [NULL]
- study_program_id (bigint) [NULL]
- created_by (bigint)
- deleted_at (timestamp without time zone) [NULL]
- created_at (timestamp without time zone) [NULL]
- updated_at (timestamp without time zone) [NULL]
- serial_number_pc (character varying(255)) [NULL]
- military_id (character varying(255)) [NULL]
- religion (character varying(255))
- belief (character varying(255))

## Table: `training_provider_instructors`
- id (bigint)
- status (character varying(255))
- training_provider_id (bigint)
- instructor_id (bigint)
- track_id (bigint) [NULL]
- created_by (bigint)
- deleted_at (timestamp without time zone) [NULL]
- created_at (timestamp without time zone) [NULL]
- updated_at (timestamp without time zone) [NULL]

## Table: `training_provider_tracks`
- id (bigint)
- status (character varying(255))
- training_provider_id (bigint)
- track_id (bigint)
- created_by (bigint)
- deleted_at (timestamp without time zone) [NULL]
- created_at (timestamp without time zone) [NULL]
- updated_at (timestamp without time zone) [NULL]

## Table: `training_providers`
- id (bigint)
- name (character varying(255))
- website (character varying(255)) [NULL]
- email (character varying(255)) [NULL]
- logo (character varying(255)) [NULL]
- address (character varying(255)) [NULL]
- status (character varying(255))
- type (character varying(255))
- created_by (bigint)
- deleted_at (timestamp without time zone) [NULL]
- created_at (timestamp without time zone) [NULL]
- updated_at (timestamp without time zone) [NULL]

## Table: `users`
- id (bigint)
- name (character varying(255))
- email (character varying(255))
- email_verified_at (timestamp without time zone) [NULL]
- password (character varying(255))
- avatar (character varying(255)) [NULL]
- phone (character varying(16)) [NULL]
- gender (character varying(255))
- status (character varying(255))
- type (character varying(255))
- training_provider_id (bigint) [NULL]
- remember_token (character varying(100)) [NULL]
- deleted_at (timestamp without time zone) [NULL]
- created_at (timestamp without time zone) [NULL]
- updated_at (timestamp without time zone) [NULL]
- access_location_id (bigint) [NULL]
- access_building_id (bigint) [NULL]
- access_floor_id (bigint) [NULL]

