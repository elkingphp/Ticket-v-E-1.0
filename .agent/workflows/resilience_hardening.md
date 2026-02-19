# ERMO Self-Healing & Resilience Plan (Pre-Chaos Phase)

This plan outlines the implementation of a "Self-Protecting" layer for the Enterprise Runtime Module Orchestrator (ERMO), elevating it from a monitored system to a self-healing infrastructure.

## 🛠 Phase 1: Infrastructure Resilience (Isolation)
**Objective**: Ensure ERMO logic never causes a Fatal Error if the backing infrastructure (Redis) fails.

### 1.1 Redis Soft-Failure Fallback
*   Wrap all `TraceModuleLifecycle` and `HealthOrchestrator` Redis calls in `try-catch` blocks.
*   If Redis is unavailable:
    *   **Logging**: Log a high-priority system warning.
    *   **Graceful Pass-through**: Middleware should continue the request lifecycle without blocking, essentially treating the counter as 0.
*   **Optimization**: Implement `evalSha` pre-loading for Lua scripts to reduce Redis CPU overhead.

### 1.2 Observability Integrity
*   Update `TraceModuleLifecycle` to ensure that `ERMO_EMERGENCY_BYPASS` only disables the "Enforcement" (Blocking), but keeps the "Tracing" (Metrics) active if possible.

---

## 🛡 Phase 2: Self-Healing (Circuit Breaker)
**Objective**: Prevent a failing module from dragging down the entire cluster.

### 2.1 Failure Window Tracking
*   Track failures in a sliding time window (e.g., last 60 seconds).
*   Criteria: `failed_checks >= threshold` (e.g., 5 critical failures).

### 2.2 Automatic Trip Logic
*   **Action**: When threshold is reached, ERMO automatically transitions the module to `degraded` or `maintenance`.
*   **Notification**: Dispatch a `ModuleCircuitTripped` event + Audit Log entry.

---

## 🚧 Phase 3: Load Shedding (Backpressure)
**Objective**: Protect system resources from saturation.

### 3.1 Adaptive Throttling
*   Add `max_concurrent_requests` setting to modules (Configurable per-module).
*   **Middleware Enforcement**: If `active_requests >= max_concurrent_requests`:
    *   Return `503 Service Unavailable`.
    *   Header: `Retry-After: 30`.
    *   Increment `ermo_load_shed_total` metric.

---

## 📈 Phase 4: Observability Upgrades (SLIs)
**Objective**: Transform raw metrics into Service Level Indicators.

### 4.1 SLI Integration
*   Add calculated fields to `/api/v1/ermo/prometheus-metrics`:
    *   `ermo_module_availability_ratio` (Active vs Maintenance time).
    *   `ermo_module_saturation_pct` (Current Active / Max Active).

---

## 📜 Execution Order
1.  **Bypass & Resilience Audit** (Fixing the "Blind Spot" and Redis safety).
2.  **Circuit Breaker implementation** (Logic & Database fields).
3.  **Load Shedding Middleware** (The "Brake" system).
4.  **Verification Test Suite** (Pre-Chaos validation).

---
**Ready for execution?**
