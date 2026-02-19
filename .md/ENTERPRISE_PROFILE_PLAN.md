# Enterprise-Level Profile Implementation Plan

This plan outlines the systematic approach to transform the current user profile into an Enterprise-Grade identity and security control center.

## Phase A: Security Foundation (The Trust Layer) 🔐

### 1. Live Session Management
- **Architectural Shift**: Move `SESSION_DRIVER` to `database` (Current: Redis).
- **Session Identification**: Implement a hash-based fingerprinting system using `sha1(user_agent + ip_subnet)` to group and identify devices.
- **Management Logic**:
    - Build `SessionManagerService` to fetch, parse, and terminate sessions.
    - Prevent accidental self-termination.
    - Transactional integrity for session cleanup.
- **Database Optimization**: Add indexes on `sessions(user_id, last_activity)`.

### 2. Activity Timeline (The Transparency Feed)
- **Service Layer**: Create `ActivityFeedService` to abstract `AuditLog` retrieval.
    - **Normalization**: Map raw audit events to human-readable timeline items (e.g., `updated` -> `Profile Updated`).
    - **Masking**: Ensure sensitive data is never exposed in the timeline.
- **UI Interaction**: Implement a "Load More" pagination pattern (10 items per page) for stability.
- **Performance**: Indexing `audit_logs(user_id, created_at)` for fast retrieval.

### 3. Re-authentication (Sudo Mode)
- **Security Logic**: Implement a "Sudo Mode" gate for critical actions (changing email, 2FA, viewing sessions).
- **Mechanism**: Store `sudo_verified_at` in the session with a 10-minute TTL.
- **Middleware**: Create `VerifySudoMode` middleware.

## Phase B: Intelligence Layer (The Proactive Layer) 🧠

### 1. Weighted Profile Completion & Risk Scoring
- **Dynamic Scoring**:
    - Implement `SecurityScoreService` to calculate a weighted profile score.
    - Weights: 2FA (20%), Email Verified (20%), Phone (10%), Name (5%), etc.
- **Persistence**: Add `profile_completion_score` and `security_risk_level` columns to the `users` table, updated via an Observer.
- **Risk Evaluation**: Visual "Security Status Banner" (Low/Medium/High Risk) based on the score.

### 2. Advanced Notification Matrix
- **Matrix Resolver**: Implement `NotificationPreferenceResolver` to determine channels dynamically in the `via()` method of notifications.
- **Mandatory Policy**: Mark security-critical events as `is_mandatory` (defined in DB) to prevent users from disabling them.
- **Modular Design**: Decouple preference logic from individual notification classes.

## Phase C: Compliance & Expansion (The Enterprise Standard) ⚖️

### 1. Data Export (GDPR Compliance)
- **Asynchronous Processing**: Implement a queued Job to generate the data export (JSON).
- **Security**: Generate a temporary, signed download link valid for 24 hours.

### 2. Account Lifecycle Management
- **Scheduled Deletion**: Implement `scheduled_for_deletion_at` with a 30-day retention policy before final purging via a scheduled task.

### 3. Adaptive Security Alerts
- **Anomaly Detection**: Send alerts on subnet or country changes (using GeoIP if available).

## Technical Guardrails 🛡️
- **Performance**: Caching scores, indexing heavy tables, and lazy loading tabs.
- **Security**: Device fingerprinting, rate limiting sensitive actions, and Transaction-safe operations.

---
**Next Steps**:
1. Apply Database Migrations (User columns, Session indexes).
2. Implement `SessionManagerService` and `ActivityFeedService`.
3. Create Sudo Mode Middleware.
4. Build the Enterprise-UX (Timeline, Matrix, Banner).
