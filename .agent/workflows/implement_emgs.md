# Implementation Plan: ERMO Stability Engineering & Recovery

Following the detection of the `Cache::expire()` fatal error, this plan focuses on moving from "Feature Development" to "Stability Engineering" to ensure ERMO is truly resilient under pressure and failure.

## 🛠 Phase 1: Atomic Integrity & Fix
**Goal**: Correct the Redis TTL logic and ensure atomic metrics.

### 1.1 Atomic Redis Counters (Lua Script)
*   Refactor `TraceModuleLifecycle` to use `Illuminate\Support\Facades\Redis` directly.
*   Implement a Lua script for the `incrementActiveRequests` method to ensure `INCR` and `EXPIRE` happen in a single atomic operation.
*   **Result**: Eliminates the "Call to undefined method" error and prevents race conditions.

### 1.2 TTL Fallback for All Counters
*   Ensure that any temporary health-check failure counters also use standard `Cache::put()` with TTL or atomic Redis operations.

---

## 🧪 Phase 2: Chaos & Resilience Testing
**Goal**: Prove the system remains deterministic during infrastructure failure.

### 2.1 Failure Scenario Tests
*   **Cache Corruption Test**: Simulate a complete Redis flush during an active request lifecycle.
*   **Concurrency Collision Test**: Simulate two state transitions hitting the same module at the exact same millisecond.
*   **Health Flapping Test**: Simulate a module vibrating between healthy and critical and verify the 3-strike debounce prevents UI/Shutdown flicker.

---

## 🔐 Phase 3: Emergency & Recovery
**Goal**: Provide a safety valve if ERMO itself encounters an issue.

### 3.1 Emergency Bypass (Kill-Switch)
*   Add `ERMO_EMERGENCY_BYPASS` to `.env`.
*   Update `CheckModuleStatus` and `TraceModuleLifecycle` middleware to check this flag.
*   **Result**: Allows DevOps to restore system access instantly if the orchestrator logic blocks the application unfairly.

---

## 📈 Phase 4: Observability (Prometheus Export)
**Goal**: Enable external monitoring and alerting.

### 4.1 Prometheus Metrics Endpoint
*   Endpoint: `GET /api/v1/ermo/prometheus-metrics`.
*   Format: OpenMetrics/Prometheus compatible text format.
*   **Data Points**:
    *   `ermo_module_active_requests{slug="core"}`
    *   `ermo_module_state_version{slug="core"}`
    *   `ermo_module_health_status{slug="core"} (1 for healthy, 0 for degraded)`
    *   `ermo_module_status{slug="core", status="active"} 1`

---

## 📜 Phase 5: Architectural Preservation
*   Final Documentation update: `ERMO_RECOVERY_PROTOCOL.md`.
