# Identity FK-Restructure Plan — Remaining Legacy JOIN Services
# Generated: 2026-11-08, Session 374 (read-only audit, zero DB writes)

## Current State (Verified This Session)
- `users 137, plots 756, leads 38687, colonies 7` — baseline intact.
- Profile tables exist with `legacy_id` bridge: `employee_profiles.legacy_id -> employees.id`,
  `associate_profiles.legacy_id -> associates.id`, both `user_id BIGINT UNSIGNED -> users.id`.
- Live JOIN audit: **33 unique service files** still reference legacy `employees`/`associates` (recursive grep).
  Tracking doc's "30" and "36" counts are stale — true count is 33 files, of which 6 already have
  partial IdentityService migration (simple lookups) but complex JOINs remain.
- Profile coverage gap: `employees 17 vs employee_profiles 15` (2 without profile),
  `associates 38 vs associate_profiles 37` (1 without profile).
  These 3 must get profiles (via `IdentityService` + migration) before backfill can be complete.
- Dependent-table data (live counts this session):
  - EMPTY (0 rows, lowest risk): `salary_structures, course_assignments, helpdesk_tickets,
    expense_claims, employee_attendance, employee_grievances, overtime_requests,
    performance_reviews, salary_advances, survey_responses`
  - Sparse: `salary_payments 9 rows, plot_bookings 3 rows (all associate_id NULL)`
  - Several tables in old tracking doc do not exist live (`fnf_settlements, gratuity_records,
    pf_esi_records, org_hierarchy, task_assignments, shift_swaps, shift_rosters, commission_ledger,
    royalty_pool, policy_violations`) — services for those read empty/absent tables, so JOIN
    rewrite is code-only, no data backfill needed.
  - Type note: most `employee_id/associate_id` columns are `BIGINT UNSIGNED` (matches `users.id`);
    a few `INT(11)` (`plot_bookings.associate_id, commission_advances.associate_id,
    mlm_payouts.associate_id, mlm_rank_history.associate_id, salary_payments.associate_id`)
    still point at legacy `associates.id` (INT PK) — these need `ALTER to BIGINT UNSIGNED` + FK repoint.

## Key Insight (overturns "NOT SAFE" blanket)
Old doc said migration requires FK restructuring everywhere and is 60-120h. Live audit shows
most dependent HR tables are EMPTY — restructuring those is schema + code, zero data risk.
Only money tables (`salary_payments 9 rows, bookings 21, payments 25, leads 40k`) have live data.
So per-service risk is HIGHLY uneven — sequence matters more than total effort.

## Migration Order (lowest risk first, money last)
### Wave 0 — Blockers (idempotent migrations, no JOIN rewrite)
1. Create missing profiles: 2 employee + 1 associate (backfill via `legacy_id` + `users` lookup).
   Insert-only, reversible (DELETE by legacy_id).
2. Fix `INT -> BIGINT UNSIGNED` on: `plot_bookings.associate_id, commission_advances.associate_id,
   mlm_payouts.associate_id, mlm_rank_history.associate_id, salary_payments.associate_id`
   (value-preserving ALTER; verify 0 truncation with `MAX()` before/after).

### Wave 1 — Empty-table services (code-only, zero data risk)
3. `TrainingService` (course_assignments 0) → JOIN `employee_profiles ep ON ep.user_id = ca.employee_id`
4. `EngagementSurveyService` (survey_responses 0) → same pattern
5. `OrgChartService` (org_hierarchy absent) → same pattern
6. `HelpdeskService` + `EmployeeGrievanceService` (helpdesk_tickets 0, grievances 0)
7. `ExpenseReimbursementService` (expense_claims 0)
Capacities: each 2-4h, full test after each. Rollback = revert service file (no schema to roll back).

### Wave 2 — Sparse-table services (backfill + code)
8. `ShiftRosterService, ShiftSwapService, SalaryAdvanceService, LeaveEncashmentService,
   OvertimeService, EmployeeAssetService, PerformanceService, EmployeePerformanceService,
   EmployeeWorkloadService` (attendance/overtime/advances 0 rows; only shift_roster/swap live counts TBD)
   Steps per service: `ADD user_id BIGINT UNSIGNED` → backfill
   `UPDATE t SET user_id=(SELECT user_id FROM employees e JOIN employee_profiles ep ON ep.legacy_id=e.id WHERE e.id=t.employee_id)`
   → verify `user_id IS NULL = 0` → rewrite JOINs → drop `employee_id` FK only AFTER 1 week dual-read.

### Wave 3 — Money tables (owner approval required, last)
9. `PayrollService, HR\PayrollService, StatutoryComplianceService, GratuityService,
   FullAndFinalSettlementService` (salary_payments 9 rows)
10. `Sales\BookingLifecycleService, Business\AssociateService, Business\ReportService, MlmPolicyGuard`
    (bookings 21 / payments 25 / leads 40k)
11. `MLM\{CommissionManager, LeadershipSalaryService, RoyaltyPoolService}, PDF\PdfService`
    (commission/payout — idempotency risk, dedicated money probe required)
Steps: safety dump → dual-write period (write both legacy_id + user_id) → read-dual → cutover → drop legacy PK FK.
Each needs byte-identical restore test + withdrawal/commission lifecycle probe re-green.

### Wave 4 — Routers / misc JOINs (code-only)
12. `SmartRoutingService, WorkDistributionService, CRM\LeadAssignmentService, Backoffice\DailyOperationsService`
    (lead routing — no FK change, only profile JOIN rewrite; least-burdened counts must match users vs associates).

## Per-Service Standard Steps
1. `mysqldump` safety dump (Rule 1) → verify size >1MB.
2. `ADD user_id` (idempotent `IF NOT EXISTS` guard via `INFORMATION_SCHEMA` check) + index.
3. Backfill via `legacy_id` bridge; assert 0 NULLs (except genuinely anonymous rows).
4. Rewrite JOINs `employees e ON x.employee_id=e.id` → `employee_profiles ep ON ep.user_id=x.employee_id`
   (or `users u ON u.id=x.employee_id` when only name/email needed).
5. Controllers: resolve via `IdentityService::resolveEmployeeUserId()/resolveAssociateUserId()` at entry.
6. `php testing/master_test_runner.php` 10/10 + affected HR/payroll probe + `flutter analyze` unchanged.
7. Keep legacy `employee_id/associate_id` columns for 1 release; drop FK only in follow-up migration.

## Owner Decision Required Before Wave 3
- Approve dual-write window length and cutover dates for payroll/booking/commission.
- Confirm 3 missing profiles' correct `users` mapping (2 employees + 1 associate).
- Confirm `plot_bookings.associate_id INT` rows (3 NULLs) need no manual reassignment.

## Recommendation
Do NOT batch-migrate all 33. Do Wave 0 + Wave 1 next (4-6 services, ~16-24h, zero data risk),
then pause for review before Wave 2/3. Legacy tables stay DEPRECATED-but-functional throughout —
no downtime, no big-bang.
