# Commission, Payout & Salary Services — Consolidation Architecture

## Date: 2026-09-05
## Scope: All commission/payout/salary service files in pp/Services/

---

## 1. Complete File Inventory (32 files)

### 1A. Core Commission Engine (pp/Services/MLM/) — 16 files

| File | Lines | Purpose | Written to Ledger? | Primary Tables |
|------|-------|---------|-------------------|----------------|
| CommissionManager.php | ~300 | **Unified entry point** — routes bookings to HybridCommissionEngine (colony projects) or MLMCommissionEngine (generic MLM). Checks for existing commissions before calculating. | Via sub-engines | mlm_commission_ledger |
| CommissionLedgerService.php | ~250 | **Shared ledger writer** — writes to mlm_commission_ledger. Captures plan snapshot, detects missed commissions, sends notifications. | YES (core writer) | mlm_commission_ledger |
| AgentLedgerService.php | ~200 | **Agent GBV tracking** — tracks Gross Booking Value per agent, upline chain traversal, commission reversal. | YES | mlm_commission_ledger, ssociates |
| BrokerCommissionService.php | ~180 | **Freelance broker rates** — reads rates from ssociate_downline_rates, cascading commissions for non-employed brokers. | YES (via LedgerService) | ssociate_downline_rates, mlm_commission_ledger |
| InvestmentCommissionService.php | ~150 | **5% total split** — 3.5% direct + 1% L1 + 0.5% L2. Uses CommissionLedgerService. | YES (via LedgerService) | mlm_commission_ledger, investments |
| TrackACommissionService.php | ~400 | **Slab differential (15% cap)** — rank-based differential between upline and downline rates. Same-level override for breakaway safeguard. Uses RankService + CommissionLedgerService. | YES (via LedgerService) | mlm_commission_ledger |
| TrackBCommissionService.php | ~300 | **Performance rollup (3% cap)** — 0.3% per qualifying month of sustained volume. Monthly rollup accumulation. | YES (via LedgerService) | mlm_commission_ledger |
| TrackCCommissionService.php | ~250 | **Milestone escrow (2% cap)** — Bronze/Silver/Gold/Platinum milestones. Escrowed amount released on milestone hit. | YES (via LedgerService) | mlm_commission_ledger |
| PipelineService.php | ~350 | **Booking payment orchestrator** — routes payment through TrackA + B + C, royalty pool, salary incentive, broker commission, investment commission. Master orchestrator. | Via sub-services | All commission tables |
| RoyaltyPoolService.php | ~280 | **2% royalty pool** — contributions from bookings, distribution to qualified leaders (>=50L GBV). Uses RankService + CommissionLedgerService. | YES (via LedgerService) | mlm_royalty_pool, mlm_royalty_contributions, mlm_commission_ledger |
| RankService.php | ~200 | **Rank slabs + GBV thresholds** — 7 ranks (associate?site_manager), GBV thresholds, rates, plan caps from DB. Central ranking authority. | No (reads only) | mlm_rank_benefits, mlm_settings |
| SalaryIncentiveService.php | ~200 | **Salary grants + career rewards** — 5 tiers (15L?10M), monthly maintenance checks. Writes to ledger as salary_grant. | YES | mlm_commission_ledger |
| TdsConfigService.php | ~150 | **Configurable TDS sections** — 194H/C/I/J/A, PAN validation, annual limits. | No (calculates only) | mlm_settings |
| RankPromotionNotificationService.php | ~120 | **Promotion notifications** — multi-channel (email/SMS/push) on rank promotion events. | No | users, ssociates |
| CommissionReconciliationService.php | ~180 | **Daily audit** — detects orphaned entries, discrepancies in mlm_commission_ledger. | No (reads/audits) | mlm_commission_ledger |
| MLMCommissionEngine.php | ~1225 | **Full MLM lifecycle** — direct sale, override, matching bonus, generation bonus, infinity override, rank bonus, level bonus, team bonus, performance bonus. The "legacy" engine for non-colony bookings. | YES | mlm_commission_ledger, mlm_network_tree |

### 1B. Supporting MLM Services (pp/Services/MLM/) — 8 files

| File | Lines | Purpose | Overlaps With? |
|------|-------|---------|----------------|
| MatchingBonusService.php | ~300 | **Generation matching bonuses** — Gen1 100%, Gen2 50%, Gen3 25%. Self-match skip + per-entry dedup. | Partial overlap with MLMCommissionEngine::matchingBonus() |
| GenerationBonusEngine.php | ~391 | **Rank-unlocked generation bonuses** — BFS tree walk, per-gen volume, monthly batch. 2%/1.5%/1%/0.5% by depth. | Partial overlap with MLMCommissionEngine::generationBonus() |
| InfinityOverrideService.php | ~326 | **VP+ unlimited override** — 1% of ALL downline sales at any depth. BFS traversal. | Partial overlap with MLMCommissionEngine::infinityOverride() |
| MLMNetworkService.php | ~250 | **Network registration + tree building** — creates entries in mlm_network_tree and 
etwork_tree. | No overlap (tree management) |
| CommissionService.php | ~400 | **Multi-tier commission** — general commission calculation with agent rates. | Overlap with CommissionManager + TrackACommissionService |
| PricingService.php | ~300 | **Raghunath Nagri pricing matrix** — plot pricing with PLC, discounts, location-based rates. | Duplicate of parent PricingService.php |
| RERAComplianceService.php | ~200 | **RERA compliance** — calculates RERA deductions from commissions. | No overlap (regulatory) |
| CommissionService.php | ~400 | (See above) | Overlap with CommissionManager |

### 1C. Parent-Level Services (pp/Services/) — 7 files

| File | Lines | Purpose | Duplicates/Overlaps With? |
|------|-------|---------|--------------------------|
| RankEvaluationService.php | ~300 | **Evaluates all users** against mlm_levels — checks teamSize, directReferrals, monthlySales for rank eligibility. | Overlap with MLM\RankService.php (rank data) |
| MLMIncentiveService.php | ~350 | **Monthly business targets + salary-style incentive payouts** — hardcoded monthlyTargets per rank, auto-creates mlm_monthly_incentives table. | **DUPLICATE** of MLM\SalaryIncentiveService.php |
| RankService.php | ~200 | **Simpler rank lookup with rewards** — Car/Bike/Laptop etc. rewards. | **DUPLICATE** of MLM\RankService.php |
| PricingService.php | ~300 | **Raghunath Nagri pricing matrix** — same plot pricing. | **DUPLICATE** of MLM\PricingService.php |
| PayoutBatchService.php | ~553 | **Payout batch lifecycle** — draft?pending_approval?approved?processing?completed. TDS, NEFT/RTGS export. | Complementary to CommissionLedgerService (reads ledger, creates batches) |
| RetroactiveRecalculationService.php | ~495 | **Admin recalc workflow** — request?review diff?approve/reject?new ledger entry (never modifies old). | Complementary (reads/writes ledger) |
| PayrollBatchService.php | ~200 | **Employee payroll batch** — uses SalaryCalculationService for monthly payslips. | Separate domain (employee payroll, not MLM commission) |

### 1D. Accounting Integration (pp/Services/Accounting/) — 1 file

| File | Lines | Purpose | Issue |
|------|-------|---------|-------|
| AccountingIntegrationService.php | ~280 | **Commission ? Wallet ? Accounting** — writes to legacy commissions table, credits wallet_transactions, creates inancial_transactions. | **STALE** — writes to legacy commissions table (9 rows, 2026-02-04), not mlm_commission_ledger (331+ rows). |

### 1E. Payroll Services (pp/Services/Payroll/) — 1 file

| File | Lines | Purpose | Overlaps With? |
|------|-------|---------|----------------|
| SalaryService.php | ~551 | **Employee payroll** — salary structures, monthly processing, attendance, payslips. Auto-creates tables. | No overlap (employee payroll domain) |

### 1F. Archived (pp/Services/Archives/) — 1 file

| File | Lines | Purpose | Status |
|------|-------|---------|--------|
| RoyaltyPoolService.php | ~358 | **DEPRECATED** — old royalty pool using dropped tables. | Safe to delete. Active version is MLM\RoyaltyPoolService.php. |

### 1G. Supporting Payroll Services (pp/Services/) — 1 file

| File | Lines | Purpose | Used By |
|------|-------|---------|---------|
| SalaryCalculationService.php | ~200 | **Pure math** — PF/ESI/TDS/PT calculation, no DB writes. | PayrollBatchService only |

---

## 2. Dependency & Call Chain Map

`
BOOKING PAYMENT
    ¦
    ?
CommissionManager -------------------------------------------------------------
    ¦                                                                           ¦
    +- Colony Project --? HybridCommissionEngine (facade)                      ¦
    ¦                       +-- TrackACommissionService ?-- RankService         ¦
    ¦                       ¦       (slab differential, 15% cap)                ¦
    ¦                       +-- TrackBCommissionService                        ¦
    ¦                       ¦       (performance rollup, 3% cap)               ¦
    ¦                       +-- TrackCCommissionService                        ¦
    ¦                       ¦       (milestone escrow, 2% cap)                 ¦
    ¦                       +-- RoyaltyPoolService ?-- RankService             ¦
    ¦                       ¦       (2% pool, 50L GBV threshold)               ¦
    ¦                       +-- CommissionLedgerService ?-- ALL tracks         ¦
    ¦                               (shared writer, plan snapshot)              ¦
    ¦                                                                           ¦
    +- Generic MLM ----? MLMCommissionEngine                                   ¦
                            +-- direct_sale + override                         ¦
                            +-- MatchingBonusService                           ¦
                            +-- GenerationBonusEngine (BFS tree walk)          ¦
                            +-- InfinityOverrideService (VP+, unlimited depth) ¦
                            +-- writes directly to ledger (bypasses LedgerSvc) ¦
                                                                                    ¦
    PipelineService -------- orchestrates ALL of the above                      ¦
    ¦                       + SalaryIncentiveService + BrokerCommissionService  ¦
    ¦                       + InvestmentCommissionService                       ¦
    ¦                                                                           ¦
    ?                                                                           ¦
mlm_commission_ledger ?-- single source of truth ------------------------------+
    ¦
    +-? PayoutBatchService (batch lifecycle: draft?paid)
    ¦       +-- reads pending ledger entries, creates payout_batches + payout_entries
    ¦       +-- TDS deduction, NEFT/RTGS CSV export
    ¦
    +-? RetroactiveRecalculationService (admin recalc: request?approve?new entry)
    ¦       +-- NEVER modifies old entries, always creates new
    ¦
    +-? CommissionReconciliationService (daily audit, orphan detection)
    ¦
    +-? TdsConfigService (configurable TDS sections 194H/C/I/J/A)

EMPLOYEE PAYROLL (separate domain):
    PayrollBatchService --? SalaryCalculationService (PF/ESI/TDS/PT math)
    SalaryService (Payroll/) --? employee_salary_structure, monthly_salary_payments
    SalaryIncentiveService (MLM/) --? mlm_commission_ledger (salary_grant type)
`

---

## 3. Duplicate File Analysis

### DUPLICATE PAIR 1: RankService (2 files)

| | pp/Services/RankService.php | pp/Services/MLM/RankService.php |
|---|---|---|
| **Purpose** | Simpler rank lookup with rewards (Car/Bike/Laptop) | Rank slabs, GBV thresholds, rates, plan caps from DB |
| **Used By** | ReferralService.php (1 caller) | TrackACommissionService, RoyaltyPoolService, CommissionManager (3+ callers) |
| **Key Methods** | getRank(), getRankRewards() | getRankSlabs(), getRankThresholds(), getActivePlanCaps(), loadRankSlabsFromDb() |
| **Verdict** | **ARCHIVE** — refactor ReferralService to use MLM\RankService |

### DUPLICATE PAIR 2: PricingService (2 files)

| | pp/Services/PricingService.php | pp/Services/MLM/PricingService.php |
|---|---|---|
| **Purpose** | Raghunath Nagri pricing matrix | Same project pricing |
| **Used By** | No external callers found | Internal MLM use |
| **Verdict** | **ARCHIVE** — MLM\PricingService is the canonical version |

### DUPLICATE PAIR 3: RoyaltyPoolService (2 files)

| | pp/Services/Archives/RoyaltyPoolService.php | pp/Services/MLM/RoyaltyPoolService.php |
|---|---|---|
| **Purpose** | DEPRECATED — old tables (oyalty_pool_contributions, oyalty_pool_distributions) | Active — uses mlm_royalty_pool + mlm_royalty_contributions |
| **Used By** | Zero callers | PipelineService, CommissionManager |
| **Verdict** | **DELETE** — already in Archives, zero references |

### DUPLICATE PAIR 4: Incentive Services (2 files)

| | pp/Services/MLMIncentiveService.php | pp/Services/MLM/SalaryIncentiveService.php |
|---|---|---|
| **Purpose** | Monthly business targets + salary-style payouts | Salary grants + career rewards |
| **Tables** | mlm_monthly_incentives (auto-created) | mlm_commission_ledger (salary_grant type) |
| **Used By** | Zero callers found | PipelineService, cron un_all_crons.php |
| **Key Methods** | processMonthlyIncentives(), checkQualification() | processMonthlyGrants(), checkQualification() |
| **Verdict** | **MERGE** into MLM\SalaryIncentiveService — combine monthlyTargets with career rewards. Archive MLMIncentiveService. |

### DUPLICATE PAIR 5: CommissionService (potential overlap)

| | pp/Services/MLM/CommissionService.php | CommissionManager + TrackACommissionService |
|---|---|---|
| **Purpose** | Multi-tier commission with agent rates | Structured 3-track engine |
| **Used By** | Zero external callers found (no use imports) | PipelineService, CommissionManager |
| **Verdict** | **ARCHIVE** — appears to be dead code, superseded by the 3-track engine |

---

## 4. Key Overlap & Gap Analysis

### 4A. Ledger Write Paths (3 paths to same table)

All paths write to mlm_commission_ledger:

1. **CommissionLedgerService** — used by TrackA/B/C, RoyaltyPool, Investment, Broker, SalaryIncentive. Standardized, captures plan snapshot.
2. **MLMCommissionEngine** — writes directly to ledger (bypasses CommissionLedgerService). Uses raw PDO INSERT. No plan snapshot.
3. **AccountingIntegrationService** — writes to **legacy** commissions table (NOT mlm_commission_ledger). Stale.

**Gap:** MLMCommissionEngine bypasses CommissionLedgerService, so its entries lack plan snapshots. This should be unified.

### 4B. TDS Calculation (2 services)

| Service | Scope | Used By |
|---------|-------|---------|
| TdsConfigService | Configurable TDS sections (194H/C/I/J/A), PAN validation, annual limits | CommissionSimulationController only |
| PayoutBatchService | Hardcoded 10% TDS in utoPopulateBatch() | Self-contained |

**Gap:** PayoutBatchService hardcodes 10% TDS instead of using TdsConfigService. Should delegate.

### 4C. Rank Lookup (3 services)

| Service | Focus |
|---------|-------|
| MLM\RankService | GBV thresholds, slab rates, plan caps |
| RankService (parent) | Rewards (Car/Bike/Laptop) |
| RankEvaluationService | Evaluates ALL users for rank promotion eligibility |

**Overlap:** All three read rank data but serve different purposes. RankEvaluationService reads mlm_levels (separate from mlm_rank_benefits). MLM\RankService reads mlm_rank_benefits. These are complementary, not duplicates.

### 4D. Salary/Incentive (3 services across 2 domains)

| Service | Domain | Tables |
|---------|--------|--------|
| MLM\SalaryIncentiveService | MLM commissions | mlm_commission_ledger (salary_grant) |
| MLMIncentiveService (parent) | MLM commissions | mlm_monthly_incentives (separate table) |
| Payroll\SalaryService | Employee payroll | employee_salary_structure, monthly_salary_payments |

**Gap:** MLMIncentiveService and MLM\SalaryIncentiveService operate in the same domain but write to different tables. Should be merged.

---

## 5. Consolidation Recommendations

### Priority 1: ARCHIVE dead/unused files (zero risk)

| File | Action | Reason |
|------|--------|--------|
| pp/Services/RankService.php | ARCHIVE | 1 caller (ReferralService), refactor to use MLM\RankService |
| pp/Services/PricingService.php | ARCHIVE | Zero external callers, duplicate of MLM\PricingService |
| pp/Services/Archives/RoyaltyPoolService.php | DELETE | Already archived, zero references, deprecated |
| pp/Services/MLM/CommissionService.php | ARCHIVE | Zero external callers, superseded by 3-track engine |
| pp/Services/MLMIncentiveService.php | ARCHIVE | Zero callers, duplicate of MLM\SalaryIncentiveService |

**Refactor required before archiving:**
- ReferralService.php: change use App\Services\RankService ? use App\Services\MLM\RankService

### Priority 2: FIX stale AccountingIntegrationService

| File | Action | Reason |
|------|--------|--------|
| AccountingIntegrationService.php | REFACTOR | processCommissionPayout() writes to legacy commissions table. Should write to mlm_commission_ledger via CommissionLedgerService. |

### Priority 3: UNIFY ledger write paths

| Gap | Fix |
|-----|-----|
| MLMCommissionEngine writes directly to ledger | Refactor to use CommissionLedgerService::writeEntry() for plan snapshot consistency |
| PayoutBatchService hardcodes 10% TDS | Refactor to use TdsConfigService::calculateTds() |

### Priority 4: MERGE duplicate incentive services

| Files | Action |
|-------|--------|
| MLMIncentiveService.php ? MLM\SalaryIncentiveService.php | Merge monthlyTargets config and mlm_monthly_incentives table logic into SalaryIncentiveService. Archive parent. |

---

## 6. Recommended Final File Structure

After consolidation (32 files ? ~24 files):

`
app/Services/
+-- MLM/
¦   +-- CommissionManager.php           # Unified entry point
¦   +-- CommissionLedgerService.php     # Shared ledger writer (single source)
¦   +-- AgentLedgerService.php          # Agent GBV tracking
¦   +-- BrokerCommissionService.php     # Freelance broker rates
¦   +-- InvestmentCommissionService.php # Investment commission
¦   +-- PipelineService.php             # Booking orchestrator
¦   +-- TrackACommissionService.php     # Slab differential
¦   +-- TrackBCommissionService.php     # Performance rollup
¦   +-- TrackCCommissionService.php     # Milestone escrow
¦   +-- RoyaltyPoolService.php          # 2% royalty pool
¦   +-- RankService.php                 # Rank slabs + GBV + plan caps
¦   +-- SalaryIncentiveService.php      # Salary grants + incentives (MERGED)
¦   +-- TdsConfigService.php            # Configurable TDS
¦   +-- RankPromotionNotificationService.php
¦   +-- CommissionReconciliationService.php
¦   +-- GenerationBonusEngine.php
¦   +-- InfinityOverrideService.php
¦   +-- MatchingBonusService.php
¦   +-- MLMCommissionEngine.php         # Full MLM lifecycle (fix: use LedgerService)
¦   +-- MLMNetworkService.php           # Tree management
¦   +-- PricingService.php              # Project pricing
¦   +-- RERAComplianceService.php       # RERA compliance
+-- PayoutBatchService.php              # Payout batch lifecycle
+-- RetroactiveRecalculationService.php # Admin recalc workflow
+-- Accounting/
¦   +-- AccountingIntegrationService.php # FIX: write to mlm_commission_ledger
¦   +-- ... (other accounting files)
+-- Payroll/
¦   +-- SalaryService.php               # Employee payroll
¦   +-- ... (other payroll files)
+-- SalaryCalculationService.php        # Pure math (PF/ESI/TDS/PT)

ARCHIVED (safe to delete):
+-- Services/RankService.php            ? MLM\RankService
+-- Services/PricingService.php         ? MLM\PricingService
+-- Services/MLMIncentiveService.php    ? MLM\SalaryIncentiveService
+-- Services/MLM/CommissionService.php  ? superseded by 3-track engine
+-- Services/Archives/RoyaltyPoolService.php ? MLM\RoyaltyPoolService
`

---

## 7. Consolidation Risk Matrix

| Change | Risk | Mitigation |
|--------|------|------------|
| Archive RankService | LOW | 1 caller, simple refactor |
| Archive PricingService | NONE | 0 callers |
| Archive MLM\CommissionService | LOW | 0 callers, verify no dynamic instantiation |
| Archive MLMIncentiveService | NONE | 0 callers |
| Delete archived RoyaltyPoolService | NONE | Already in Archives, 0 refs |
| Fix AccountingIntegrationService | MEDIUM | Changes commission write path. Test with existing 9 rows in commissions table. |
| Unify MLMCommissionEngine ledger writes | HIGH | Core engine changes. Requires thorough testing of all MLM commission types. |
| Merge incentive services | LOW | Combine 2 small services, verify cron job still works. |
| Fix PayoutBatchService TDS | LOW | Replace hardcoded 10% with TdsConfigService delegation. |

---

## 8. Cross-Cutting Concerns

### 8A. Tenant Scoping
- All 25 MLM\ files have ServiceTenantTrait applied
- PayoutBatchService has ServiceTenantTrait
- RetroactiveRecalculationService has ServiceTenantTrait
- AccountingIntegrationService has raw TenantContext::getId() calls (not trait-based)

### 8B. Plan Snapshot
- CommissionLedgerService captures plan snapshot on every write
- MLMCommissionEngine does NOT capture plan snapshot (raw INSERT)
- RetroactiveRecalculationService captures snapshot via HybridCommissionEngine::getActivePlanSnapshot()

### 8C. Idempotency
- CommissionManager checks for existing commissions before calculating
- GenerationBonusEngine and InfinityOverrideService dedup by period_start/period_end
- PayoutBatchService prevents duplicate ledger entries in batches

---

## 9. Cron Integration Points

| Cron Task | Services Used | Notes |
|-----------|---------------|-------|
| un_all_crons.php Task 12 | SalaryIncentiveService::processMonthlyGrants() | Monthly |
| un_all_crons.php Task 13 | LeadershipSalaryService::processMonthlyPayouts() | Monthly |
| scripts/run_royalty_pool.php | HybridCommissionEngine::distributeRoyaltyPool() | Monthly |
| scripts/cron_agent_orchestrator.php | Various AI services | Every 15 min |

---

## 10. Summary Statistics

| Metric | Value |
|--------|-------|
| Total commission/payout/salary service files | **32** |
| Files with confirmed duplicate pairs | **5** pairs (10 files) |
| Dead/unused files (safe to archive) | **5** |
| Files needing refactoring | **3** (AccountingIntegration, MLMCommissionEngine, PayoutBatchService) |
| Ledger write paths | **3** (should be 1) |
| TDS calculation paths | **2** (should be 1) |
| Rank lookup services | **3** (should be 2 — RankService + RankEvaluationService) |
| Tables written to | **12** (ledger, batches, payouts, pool, generation_commissions, infinity_overrides, monthly_incentives, etc.) |
| Estimated reduction after consolidation | **~8 files** removed/merged |
