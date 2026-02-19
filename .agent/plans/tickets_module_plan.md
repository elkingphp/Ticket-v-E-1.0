# Ticket Module Implementation Plan

## 1. System Analysis & Requirements
The **Tickets Module** is an enterprise-grade component designed to handle inquiries and complaints with high precision and scalability. It features a hierarchical classification system and advanced routing logic.

### Key Entities & Logic
1.  **Classification Hierarchy:**
    *   **Stages**: The high-level lifecycle step (e.g., Application, Graduation).
    *   **Categories**: Grouping of complaints (e.g., Academic, Financial).
    *   **Complaints**: Specific issues.
    *   **Sub-complaints**: Granular details (Multi-select).
2.  **SLA Architecture (Inheritance Model):**
    *   **Logic**: `Complaint SLA` overrides `Category SLA`, which overrides `Stage SLA`.
    *   **Calculation**: System calculates `due_at` timestamp upon creation based on the resolved SLA hours.
    *   **Status**: `Overdue` is calculated dynamically (`now() > due_at`), not stored as a boolean.
3.  **Routing & Assignment (Hierarchy Model):**
    *   **Logic**: `Complaint Group` ?? `Category Group` ?? `Stage Group` ?? `Default Group`.
    *   **Escalation**: Logic to re-route tickets if SLA is breached (Future Scope/Phase 5).
4.  **Communication:**
    *   **Internal Notes**: Staff-only visibility.
    *   **External Replies**: Customer-facing.
    *   **Read Receipts**: Track if staff/user has read the latest reply.

## 2. Database Schema Design (Refined)

### Settings & Lookup Tables
*   `ticket_stages`: `id`, `name`, `sla_hours` (nullable)
*   `ticket_categories`: `id`, `name`, `stage_id`, `sla_hours` (nullable)
*   `ticket_complaints`: `id`, `name`, `category_id`, `sla_hours` (nullable)
*   `ticket_sub_complaints`: `id`, `name`, `complaint_id`
*   `ticket_statuses`: `id`, `name`, `color`, `is_default`
*   `ticket_priorities`: `id`, `name`, `color`, `is_default`

### Routing tables
*   `ticket_groups`: `id`, `name`, `is_default`
*   `ticket_group_members`: `group_id`, `user_id`
*   `ticket_routing`:
    *   `entity_type` (Stage, Category, Complaint)
    *   `entity_id`
    *   `group_id`
    *   *Note: This polymorphic or separate table approach allows granular routing.*

### Operational Tables
*   `tickets`:
    *   `id`, `uuid` (Indexed)
    *   `user_id` (Requester - Indexed)
    *   `stage_id`, `category_id`, `complaint_id`
    *   `subject`
    *   `details`
    *   `status_id` (Indexed), `priority_id` (Indexed)
    *   `assigned_group_id` (Indexed), `assigned_to` (Indexed)
    *   `due_at` (Timestamp - Indexed for SLA queries)
    *   `closed_at`, `reopened_at`
    *   `timestamps` (Indexed)
*   `ticket_sub_complaint_pivot`: `ticket_id`, `sub_complaint_id`
*   `ticket_threads`:
    *   `id`, `ticket_id`, `user_id`
    *   `content`
    *   `type` (enum: 'message', 'internal_note')
    *   `is_read_by_staff`, `is_read_by_user`
    *   `timestamps`
*   `ticket_attachments`: `id`, `ticket_id`, `thread_id`, `file_path`, `file_name`, `file_size`, `mime_type`
*   `ticket_audit_logs`: `id`, `ticket_id`, `user_id`, `action`, `old_value`, `new_value`, `created_at`
*   `ticket_email_templates`: `id`, `event_key`, `subject`, `body`

## 3. Implementation Steps

### Phase 1: Module Setup & Migrations
1.  **Module**: Create `Tickets` module structure. [X]
2.  **Migrations**: Implement optimized schema with indices on foreign keys and frequent query columns. [X]
3.  **Models**: Implement relationships and the **SLA/Routing Inheritance Logic** as model traits or service classes. [X]

### Phase 2: Administrator Configuration (Backend)
1.  **Configuration UI**: [x]
    *   [x] Create Admin Controllers (Stages, Categories, Complaints, Statuses, Priorities, Groups, Templates)
    *   [x] Create Admin Views (Blade templates with Tailwind/Bootstrap)
    *   [x] Define Admin Routes
2.  **Email Templates**: CRUD for notification templates. [x]

### Phase 3: User Implementation (Frontend)
- [x] **Creation Form**:
    - [x] Dynamic selection (Stage -> Category -> Complaint).
    - [x] File upload support (Spatie MediaLibrary or standard storage).
- [x] **List View**: Dashboard with status/priority badges.
- [x] **Detail View**: Show specific ticket and its thread.
- [x] **Reply Form**: Add replies to the ticket.
    *   Visual indicators for Status.
    *   Secure view (Owner policy check).

### Phase 4: Support Agent & Workflow (Backend Logic)
- [x] **Agent Interface**:
    - [x] View assigned tickets (Group or Individual).
    - [x] Status management (Open -> In Progress -> Resolved).
    - [x] Internal Notes (Agents discussion).
- [x] **Notification System**:
    - [x] Events: `TicketCreated`, `TicketReplied`, `TicketStatusChanged`.
    - [x] Listeners: Send emails (Customer & Agent).

### Phase 5: Testing & Refinement
- [x] **Verification**:
    - [x] Manual verification via Tinker script (CRUD + Routing).
    - [x] Confirmed Email Template seeding.
- [ ] **Automated Tests**:
    - [ ] Configure `phpunit.xml` for Postgres to run full suite (Current tests require Postgres schemas).
- [x] **UI Polish**:
    - [x] Integrate Tickets into Sidebar Menu.
    - [x] Verify responsive design on Agent dashboard.

## 4. Verification & Logic
*   **SLA Calculation**: `calculateDueAt($stage, $category, $complaint)` method.
*   **Routing Resolution**: `resolveAssignedGroup($stage, $category, $complaint)` method.
*   **Performance**: Verify indexes on `tickets` table for dashboard queries.

---
**Ready for Review**: This plan incorporates all architectural feedback (SLA Inheritance, Dynamic Querying, Granular Routing, Performance Indexes).
