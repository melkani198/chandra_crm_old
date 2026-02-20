# World-Class CRM + Contact Center Execution Plan (Repo-Specific)

This is a concrete build plan for **this repository** to evolve into a production-grade CRM + contact center platform.

## 1) Current System Snapshot (based on existing code)

### Backend surfaces already present
- API router and auth middleware: `php-backend/index.php`
- Controllers: Auth, Users, Campaigns, PBX, Dashboard, Contacts, Dialer, Queue, CDR, Settings, Import, Process Master
- Services: Dialer, Predictive, Telephony, CallSession, AgentState, Process
- PBX helper integrations: AMI/PBX clients

### Frontend surfaces already present
- Admin dashboard and campaign pages
- Queue monitor UI (HTML/CSS/JS)
- Contacts and import pages
- Agent dashboard shell

## 2) Target Product Outcome (12 months)

Build a single platform where agents and supervisors can:
1. Handle voice + messaging from one workspace.
2. Route work by skills, priority, SLA, and business rules.
3. Execute outbound campaigns with compliant pacing controls.
4. Use AI for summaries, QA, next-best-action, and intent routing.
5. Operate with enterprise observability, security, and auditability.

## 3) 30/60/90-Day Implementation Plan

## First 30 Days: API hardening + data consistency

### A) Lock API contracts and errors
- Create request/response contract docs for endpoints in `php-backend/index.php`.
- Standardize error shape beyond current `{detail: ...}` and include:
  - `code`
  - `message`
  - `trace_id`
  - `field_errors`

### B) Add validation layer to controllers
- Add per-controller input validators for:
  - `CampaignController`
  - `DialerController`
  - `ContactController`
  - `QueueController`
- Reject invalid payloads before service calls.

### C) Add event + audit schema
Introduce tables:
- `event_log(id, aggregate_type, aggregate_id, event_type, payload_json, created_at)`
- `audit_log(id, actor_user_id, action, target_type, target_id, metadata_json, created_at)`

Emit events for:
- campaign create/update/delete
- lead import
- call dial attempt / connect / end
- agent status changes

### D) Add request metrics middleware
Capture per request:
- `route`
- `method`
- `status_code`
- `duration_ms`
- `user_id`

Write to logs first; external metrics backend can be connected later.

**30-day success criteria**
- All primary write endpoints validate inputs and return consistent errors.
- Audit log entries exist for all admin-mutating actions.
- Dashboard for API p95 and error rate available.

## Days 31–60: Unified agent workspace + queue intelligence

### A) Build unified agent workspace page
Extend from existing frontend admin/agent shells:
- customer profile card
- live call controls
- interaction timeline panel
- disposition + notes panel

### B) Queue management and callbacks
- Add queue callback request state machine:
  - requested → scheduled → attempted → completed/failed
- Expose queue APIs for supervisor controls:
  - queue pause/resume
  - priority overrides
  - overflow rules

### C) Real-time updates
- Introduce a push channel (WebSocket or polling fallback) for:
  - agent state
  - queue depth
  - service level
  - active call card updates

**60-day success criteria**
- Agent can complete full inbound call lifecycle in one screen.
- Supervisor can monitor queue and apply controls in near real-time.

## Days 61–90: Predictive + quality intelligence

### A) Predictive dialer guardrails
Add policy limits:
- max abandon rate threshold
- retry strategy with cooldown
- timezone + consent checks before dial

### B) QA and compliance workflow
- Auto-create QA review task from completed calls.
- Add compliance flags for DNC/consent violations.
- Add secure recording references and access checks.

### C) AI-assisted operations (first increment)
- call summary generation (post-call)
- disposition recommendation
- keyword-based risk flags

**90-day success criteria**
- Predictive dialing operates within configured abandon/compliance limits.
- Supervisors can review QA queues with auto-generated summaries.

## 4) Endpoint Modernization Matrix (based on router)

| Domain | Current route area | Priority | Required upgrade |
|---|---|---:|---|
| Auth | `/auth/login`, `/auth/register`, `/auth/me` | High | token rotation, lockout policy, auth audit |
| Dashboard | `/dashboard/stats`, `/dashboard/agents` | High | consistent SLA/occupancy metrics |
| Users | `/users` | High | role-policy checks + immutable audit |
| Campaigns | `/campaigns` | High | validation + event emission + throttling rules |
| PBX | `/pbx/agent/status`, `/pbx/dial` | Critical | consent checks, trace ids, call lifecycle events |
| Process Master | `/process-master` | Medium | versioning + publish/draft workflow |

## 5) Data Model Priorities (implementation order)

1. `contacts`
2. `campaigns`
3. `call_sessions`
4. `interactions`
5. `queues`
6. `agent_sessions`
7. `consents`
8. `event_log`
9. `audit_log`

Design rule: all customer-impacting state transitions must write to `event_log`.

## 6) Security + Compliance Baseline

- Enforce RBAC checks inside every mutating controller action.
- Add tenant guard on every query path where multi-tenant is expected.
- Encrypt sensitive fields (phone, email, notes containing PII) at rest.
- Enforce DNC + consent checks before any outbound dial.
- Add tamper-evident audit trail for admin actions.

## 7) Execution Backlog (first 15 tickets)

1. API error contract utility + trace-id generation.
2. Request validator for Campaign create/update.
3. Request validator for PBX dial endpoint.
4. Request validator for Contact CRUD + import.
5. `event_log` migration + write helper service.
6. `audit_log` migration + middleware hook.
7. Wrap `CampaignController` mutations with audit + event writes.
8. Wrap `DialerService` lifecycle events.
9. Add API latency logger middleware.
10. Add dashboard endpoint for API health summary.
11. Build unified agent workspace skeleton.
12. Add timeline endpoint (`interactions` by contact).
13. Add queue callback API + state transitions.
14. Add supervisor queue actions endpoint.
15. Add compliance pre-dial policy checks.

## 8) KPI Tree for weekly reviews

### Business
- Conversion rate, revenue per agent, cost per conversion.

### Operations
- Service level, ASA, abandonment, occupancy, AHT, FCR.

### Quality
- QA score, compliance incidents, CSAT, repeat-contact rate.

### Platform
- API p95 latency, 5xx rate, event lag, dialer throughput, uptime.

## 9) Delivery Model

- Weekly architecture + KPI review (60 min).
- Two-week delivery sprints.
- Monthly compliance review with legal/ops.
- Release cadence: weekly backend, biweekly frontend, hotfix anytime.

## 10) Immediate next move

If you want, I can generate the **actual implementation patch set for Phase 1** next:
- API validation utilities
- unified error response contract
- `event_log` + `audit_log` SQL migration files
- controller/service wiring for campaign + dialer events
