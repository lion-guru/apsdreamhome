# CommissionEngine — Draft (Phase 4.12) — For Abhaay Confirmation

## Goal
19 duplicate-purpose service files → 1 unified `CommissionEngine` with Strategy pattern by track type.
Current files stay untouched until Abhaay confirms. This doc is the draft for review.

## Current 19 Files Identified
```
CommissionService.php
CommissionPlanService.php
HybridCommissionEngine.php
CommissionSimulator.php
MLM/ (10 files):
  - CommissionLedgerService.php
  - CommissionManager.php
  - CommissionReconciliationService.php
  - InvestmentCommissionService.php
  - MLMCommissionEngine.php
  - TrackACommissionService.php
  - TrackBCommissionService.php
  - TrackCCommissionService.php
  - (plus 2 more in MLM cluster: DailyCappingService, GenerationBonusEngine etc)
PayoutBatchService.php
Payroll/SalaryService.php
SalaryCalculationService.php
```

## Proposed Unified Design
```php
interface CommissionStrategy {
    public function calculate(Booking $booking, User $user): CommissionResult;
    public function supports(string $track): bool; // 'direct_sale'|'investment'|'salary'|'payout'
}

class CommissionEngine {
    private array $strategies; // Strategy[]
    public function __construct(iterable $strategies) { $this->strategies = $strategies; }
    public function calculateForBooking(int $bookingId): array {
        $booking = $this->loadBooking($bookingId);
        $results = [];
        foreach ($this->strategies as $s) {
            if ($s->supports($booking->track)) {
                $results[] = $s->calculate($booking, $booking->user);
            }
        }
        return $this->ledger->persist($results); // single ledger write
    }
}

// Strategies:
// - DirectSaleStrategy (Track A/B/C) -> replaces HybridCommissionEngine + TrackA/B/C
// - InvestmentStrategy -> replaces InvestmentCommissionService
// - SalaryStrategy -> replaces SalaryService + SalaryCalculationService
// - PayoutStrategy -> replaces PayoutBatchService
// - PlanSimulator (read-only) -> wraps CommissionSimulator for dry-run
```

## Migration Steps (after confirmation)
1. Create `app/Services/Commission/CommissionEngine.php` + `CommissionStrategy.php` interface + 4 strategy classes (copy logic from existing files, no new logic).
2. Add `CommissionLedgerService` as single writer (already exists as ledger, keep).
3. Keep `CommissionPlanService` as plan store (read-only), inject into strategies.
4. Write 15 PHPUnit tests for `CommissionEngine` covering: direct 5%/7%/10% slab, salary 15L/60d, payout batch, idempotency, clawback.
5. Route `POST /api/commission/calculate` to `CommissionEngine` (feature-flag, old services still work).
6. After 7 days of parallel run (both old and new write to ledger, compare), archive old 19 files to `_archive/services_20260904/` (not delete).

## Risks & Mitigations
* **Money-critical:** Run parallel for 7 days, compare ledger totals daily (old vs new). Rollback = flip flag off.
* **No schema change:** `mlm_commission_ledger` stays same (plan_snapshot already added).
* **No downtime:** Old services stay until new is proven.

## Confirmation Needed
Abhaay, please confirm:
- [ ] Strategy list (4 tracks) correct?
- [ ] File archive (not delete) OK?
- [ ] 7-day parallel run OK?

If OK, I will implement in next session (Day 6-7).
