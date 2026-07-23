# APS Dream Home — MLM Payout Distribution Plan

> **Last Updated:** 2026-06-25
> **Status:** Production-Ready (Single Engine: `mlm_commission_ledger`)
> **Tables:** 13 active tables, ~68MB total
> **Ranks:** 7 tiers (Associate → Site Manager)

---

## 1. Executive Summary

APS Dream Home uses a **hybrid unilevel MLM commission system** with rank-based differential payouts, a 20% global cap, escrow-based milestone rewards, and a royalty pool for senior leaders. Every booking payment flows through a single source of truth: `mlm_commission_ledger`.

### Key Numbers

| Metric                    | Value                        |
| ------------------------- | ---------------------------- |
| Total ledger entries      | 118                          |
| Total commissions tracked | ₹36,74,902                   |
| Active ranks              | 7 (Associate → Site Manager) |
| Global commission cap     | 20% of payment               |
| Payout frequency          | Monthly batch                |
| Approved payout batches   | 1 (₹19,92,385.60)            |

---

## 2. User Hierarchy & Rank System

### 2.1 Rank Structure (from `mlm_rank_benefits`)

**Rank Rates (Direct Commission):**

| Rank               | Order | Min Legs | Min Qualifying Volume | Direct Rate |
| ------------------ | ----- | -------- | --------------------- | ----------- |
| **Associate**      | 1     | 0        | ₹0                    | **5%**      |
| **Senior Assoc.**  | 2     | 1        | ₹25,000               | **7%**      |
| **BDM**            | 3     | 2        | ₹1,00,000             | **10%**     |
| **Sr. BDM**        | 4     | 3        | ₹3,00,000             | **12%**     |
| **Vice President** | 5     | 4        | ₹8,00,000             | **15%**     |
| **President**      | 6     | 5        | ₹20,00,000            | **18%**     |
| **Site Manager**   | 7     | 6        | ₹50,00,000            | **20%**     |

> All percentages are of the **booking payment amount**.
> The `direct_sale_pct` column stores the agent's own rank rate.

### 2.1a Differential Commission Model (How Upline Overrides Work)

**The system uses a DIFFERENTIAL model, NOT flat L1/L2/L3 percentages.**

When a sale happens, the **selling agent gets their full rank rate**. Each upline gets the **difference between their rate and the rate of the person directly below them**.

**Formula:** `Upline Override = Upline Rate − Rate of Level Below`

| Selling Agent Rate | Upline Rank           | Upline Rate | Upline Gets | How                   |
| ------------------ | --------------------- | ----------- | ----------- | --------------------- |
| 5% (Associate)     | Senior Associate (L1) | 7%          | **2%**      | 7% − 5% = 2%          |
| 5% (Associate)     | BDM (L2)              | 10%         | **3%**      | 10% − 7% = 3%         |
| 5% (Associate)     | Sr. BDM (L3)          | 12%         | **2%**      | 12% − 10% = 2%        |
| **Total**          |                       |             | **12%**     | Agent 5% + Uplines 7% |

**Example 2:** Senior Associate (7%) makes a sale, BDM (10%) is L1, Sr. BDM (12%) is L2:

| Level     | Rank             | Rate | Gets    | How              |
| --------- | ---------------- | ---- | ------- | ---------------- |
| Agent     | Senior Associate | 7%   | **7%**  | Full direct rate |
| L1        | BDM              | 10%  | **3%**  | 10% − 7% = 3%    |
| L2        | Sr. BDM          | 12%  | **2%**  | 12% − 10% = 2%   |
| **Total** |                  |      | **12%** |                  |

**Key Rules:**

- **Same-rank breakaway:** If upline has same rate as level below → 2% Gen 1, 1% Gen 2, 0% Gen 3+
- **Global cap:** Total commission never exceeds 20% of payment amount
- **L1/L2/L3 columns in DB** store differentials (not flat rates): `l1_pct = upline_rate − direct_rate`, `l2_pct = l2_rate − l1_rate`, `l3_pct = l3_rate − l2_rate`
- **DB stores:** `direct_sale_pct = rank_rate`, `l1_pct = rank_rate − prev_rank_rate`, etc.

### 2.2 Rank Promotion Rules

- Evaluated monthly via `scripts/run_rank_promotion.php`
- Must meet **BOTH** leg count AND qualifying volume thresholds
- Promotions logged to `mlm_rank_history`
- Demotion NOT implemented (ranks only go up)
- Agent rank stored in **both** `associates.level` and `mlm_profiles.current_level`
- `RankEvaluationService::evaluate()` syncs both tables after promotion

### 2.3 Hierarchy Structure

```
users.referred_by → builds the tree
mlm_network_tree → materialized view of hierarchy
  - parent_user_id → who referred them
  - child_user_id → who was referred
  - level_depth → distance from root (0 = root, 1 = direct, 2 = 2nd gen, etc.)
```

---

## 3. Commission Distribution Flow

### 3.1 The Journey of ₹1

When a customer pays ₹1,00,000 for a plot:

```
Customer Payment
      │
      ▼
┌─────────────────────────────────────┐
│  BookingLifecycleService            │
│  recordPayment()                    │
│  → Updates booking status           │
│  → Updates payment schedule         │
└──────────────┬──────────────────────┘
               │
               ▼
┌─────────────────────────────────────┐
│  MLMCommissionEngine                │
│  calculateBookingCommission()       │
│                                     │
│  1. Resolve agent from booking      │
│  2. Look up agent's rank            │
│  3. Walk upline (users.referred_by) │
│  4. Calculate per-level amounts     │
│  5. Write to mlm_commission_ledger  │
└──────────────┬──────────────────────┘
               │
               ▼
┌─────────────────────────────────────┐
│  mlm_commission_ledger              │
│  (Single Source of Truth)           │
│                                     │
│  Entry per beneficiary:             │
│  - direct_sale: Agent gets X%       │
│  - mlm_level_1: Sponsor gets Y%    │
│  - mlm_level_2: Super gets Z%      │
│  - mlm_level_3: Great-grand gets W%│
└─────────────────────────────────────┘
```

### 3.2 Commission Types in Ledger

| Type                | Description                  | Trigger                   |
| ------------------- | ---------------------------- | ------------------------- |
| `direct_sale`       | Agent's own sale commission  | Any booking payment       |
| `mlm_level_1`       | First-level upline override  | Sponsor chain             |
| `mlm_level_2`       | Second-level upline override | Sponsor chain             |
| `mlm_level_3`       | Third-level upline override  | Sponsor chain             |
| `level_bonus`       | Rank-based level bonus       | Monthly evaluation        |
| `team_bonus`        | Team volume bonus            | Monthly evaluation        |
| `performance_bonus` | Performance-based bonus      | Monthly evaluation        |
| `investment_sale`   | Investment commission (3%)   | Any investment made       |
| `royalty_pool`      | Royalty distribution share   | Monthly pool distribution |
| `clawback`          | Negative entry for default   | EMI default 30+ days      |

### 3.3 Commission Calculation Example

**Scenario:** Agent (Associate rank, 1% direct) makes ₹10,00,000 sale

```
Agent (Associate):      1.0% × ₹10L = ₹10,000 (direct_sale)
Sponsor (Sr. Assoc.):   2.0% × ₹10L = ₹20,000 (mlm_level_1)
Super (BDM):            1.5% × ₹10L = ₹15,000 (mlm_level_2)
Great-grand:            0.0% × ₹10L = ₹0      (mlm_level_3 — rank too low)

TOTAL: ₹45,000 (4.5% of payment — within 20% cap)
```

**Scenario:** Agent (Site Manager, 4% direct) makes ₹10,00,000 sale

```
Agent (Site Manager):   4.0% × ₹10L = ₹40,000 (direct_sale)
Sponsor (President):    5.0% × ₹10L = ₹50,000 (mlm_level_1)
Super (Vice President): 4.0% × ₹10L = ₹40,000 (mlm_level_2)
Great-grand (BDM):      3.0% × ₹10L = ₹30,000 (mlm_level_3)

TOTAL: ₹1,60,000 (16% of payment — within 20% cap)
```

### 3.4 Breakaway Safeguard

- If sponsor and downline have the **same rank**, the override is reduced:
  - L1 override: 2.0% (instead of full rate)
  - L2 override: 1.0% (instead of full rate)
- This prevents double-dipping at the same rank level
- Implemented in `MLMCommissionEngine::calculateBookingCommission()`

---

## 4. Hybrid Commission Engine (Colony Projects)

For colony projects (Suryoday, Braj Radha, Raghunath, Budh Bihar), an additional engine runs:

### 4.1 Three-Track Architecture

| Track       | Name               | % of Payment | Purpose                                       |
| ----------- | ------------------ | ------------ | --------------------------------------------- |
| **Track A** | Slab Differential  | 15%          | Rank-based commission with differential slabs |
| **Track B** | Performance Rollup | 3%           | Consecutive qualifying month bonus            |
| **Track C** | Milestone Escrow   | 2%           | Savings toward milestone rewards              |
| **TOTAL**   | —                  | **20% cap**  | Global cap per payment                        |

### 4.2 Track A — Slab Differential

- Agent gets their rank's rate from `HybridCommissionEngine::RANK_SLABS`
- Upline gets the **difference** between their rank rate and downline's rate
- Example: Agent at 10%, Sponsor at 15% → Agent gets 10%, Sponsor gets 5% (15%-10%)

### 4.3 Track B — Performance Rollup

- Rewards consecutive months with qualifying sales (≥₹50K/month)
- 0 consecutive months = 0% bonus
- 3+ consecutive months = 0.9% bonus on all payments

### 4.4 Track C — Milestone Escrow

- 2% of every payment held in escrow
- Released on rank promotion or milestone achievement
- Balance tracked in `mlm_commission_ledger` with `type='performance_bonus'`

### 4.5 HybridCommissionEngine Rank Slabs

```php
RANK_SLABS = [
    'associate'      => ['min_gbv' =>  0,        'max_gbv' => 1000000,  'rate' =>  5],
    'sr_associate'   => ['min_gbv' =>  1000000,  'max_gbv' => 3500000,  'rate' =>  7],
    'bdm'            => ['min_gbv' =>  3500000,  'max_gbv' => 7000000,  'rate' => 10],
    'sr_bdm'         => ['min_gbv' =>  7000000,  'max_gbv' => 15000000, 'rate' => 12],
    'vice_president' => ['min_gbv' =>  15000000, 'max_gbv' => 30000000, 'rate' => 15],
    'president'      => ['min_gbv' =>  30000000, 'max_gbv' => 50000000, 'rate' => 18],
    'site_manager'   => ['min_gbv' =>  50000000, 'max_gbv' => 0,        'rate' => 20], // uncapped
];
```

> Note: HybridCommissionEngine rates (5-20%) are higher than MLMCommissionEngine rates (1-4%) because they are specifically for colony plot sales, which have higher margins.

---

## 5. Investment Commission

When a customer invests through any investment plan, the referring agent/associate receives commissions.

### 5.1 Commission Structure

| Level     | Percentage | Recipient            |
| --------- | ---------- | -------------------- |
| Agent     | 2%         | Referring agent      |
| L1        | 0.7%       | First upline         |
| L2        | 0.3%       | Second upline        |
| **Total** | **3%**     | Pool from investment |

### 5.2 Commission Reversal on Cancellation

When an investment is cancelled:

1. `InvestmentService::cancelInvestment()` updates investment status
2. Calls `HybridCommissionEngine::reverseInvestmentCommissions()`
3. Finds all ledger entries with `receipt_id=investmentId` and `commission_type='investment_sale'`
4. Marks them as `status='cancelled'` (creates negative entries to reverse)
5. Returns `commissions_reversed` and `total_reversed` in response

---

## 6. Global Commission Cap (20%)

**Rule:** Total commission from ANY booking payment cannot exceed 20% of that payment amount.

### 6.1 How Cap Works

```php
$totalCommission = $directSale + $l1 + $l2 + $l3 + $levelBonus + $teamBonus;
$cap = $paymentAmount * 0.20;

if ($totalCommission > $cap) {
    // Scale down all entries proportionally
    $scaleFactor = $cap / $totalCommission;
    foreach ($entries as &$entry) {
        $entry['amount'] *= $scaleFactor;
    }
}
```

### 6.2 Cap Enforcement

- Enforced in `MLMCommissionEngine::calculateBookingCommission()`
- After calculating all entries, total is checked against cap
- If exceeded, all entries scaled down proportionally
- Cap status logged but entry is still created (just smaller amounts)

### 6.3 Royalty Pool is Extra

- 2% of every payment contributed to royalty pool (outside the 20% cap)
- Written to `mlm_royalty_pool` table
- Distributed monthly by `HybridCommissionEngine::distributeRoyaltyPool()`

---

## 7. Payout Distribution Process

### 7.1 Monthly Payout Flow

```
Step 1: Cutoff (Last day of month)
  → No more commissions accepted for that month

Step 2: Reconciliation
  → CommissionReconciliationService::reconcile()
  → Check for orphans, discrepancies, negative entries

Step 3: Batch Creation
  → Admin creates batch in /admin/commission/payouts
  → System generates APS-MPB-YYYYMM-NNNN batch number
  → All pending ledger entries for the month are included

Step 4: Approval
  → Admin reviews batch total
  → Approves batch → status changes to 'approved'

Step 5: Payment
  → Commissions paid via bank transfer / UPI / wallet
  → mlm_payouts records each payment
  → mlm_commission_ledger status updated to 'paid'

Step 6: Wallet Update
  → user_wallets balance updated
  → hold_amount decreased
```

### 7.2 Payout Tables

| Table                | Purpose                                                    |
| -------------------- | ---------------------------------------------------------- |
| `mlm_payout_batches` | Monthly batch header (batch number, period, total, status) |
| `mlm_payouts`        | Individual payment records per agent                       |
| `user_wallets`       | Running balance per user (balance, hold, credit, debit)    |

### 7.3 Payout Batch Status Flow

```
draft → pending → approved → paid
         ↓
       rejected
```

---

## 8. EMI Default & Clawback

### 8.1 Penalty Engine (18% p.a.)

- Daily penalty on overdue installments past 5-day grace period
- Applied via `MoneyWorkflowService::applyDailyPenalties()`
- Penalty stored in `booking_payment_schedules.accrued_penalty`
- Audit trail in `penalty_audit` table

### 8.2 Clawback Trigger

- If an EMI is 30+ days overdue, commission clawback is triggered
- Processed by `MLMCommissionEngine::processClawbacks()`
- Creates negative entries in `mlm_commission_ledger` (type=`clawback`)
- Debits the original beneficiary's wallet
- Logged to `mlm_clawback_log`

### 8.3 Interest-Free Period (3 Years)

- Installments due within 3 years of booking date do NOT accrue penalty
- Exception: If customer has 3 consecutive missed EMIs, they lose interest-free status
- Advance payment offset: If customer has paid more than scheduled, they are skipped entirely

---

## 9. Royalty Pool (2% Extra)

### 9.1 Collection

- 2% of every booking payment contributed to royalty pool
- **Excluded from the 20% global cap** (additional 2% on top)
- Tracked in `mlm_royalty_pool` table (active) and `mlm_royalty_contributions` (per-payment log)

### 9.2 Distribution

- Monthly distribution via `HybridCommissionEngine::distributeRoyaltyPool()`
- Distributed equally among qualified agents at rank **Site Manager** (≥₹50L monthly GBV)
- Written to `mlm_commission_ledger` (type=`royalty_pool`)

### 9.3 Royalty Pool Tables

| Table                        | Status | Purpose                      |
| ---------------------------- | ------ | ---------------------------- |
| `mlm_royalty_pool`           | ACTIVE | Monthly pool accumulation    |
| `mlm_royalty_contributions`  | ACTIVE | Per-payment contribution log |
| `royalty_pool_contributions` | DEAD   | Legacy — never populated     |
| `royalty_pool_distributions` | DEAD   | Legacy — never populated     |

---

## 10. Rank Promotion & Auto-Evaluation

### 10.1 Monthly Cron: `scripts/run_rank_promotion.php`

- Loops through all active associates
- Checks leg count (from `mlm_network_tree`)
- Checks qualifying volume (from `mlm_profiles.lifetime_sales`)
- Promotes if thresholds met
- Logs to `mlm_rank_history`
- Syncs `associates.level` ↔ `mlm_profiles.current_level`

### 10.2 Rank Benefits from DB

All rank benefits stored in `mlm_rank_benefits` table — not hardcoded.

- `loadRankSlabsFromDb()` reads from DB
- `getCanonicalRates()` returns rates for calculation
- Admin can modify rates via `/admin/commission-plans`

### 10.3 Rank Naming Convention

All internal rank names use **lowercase snake_case**:

```
associate → senior_associate → bdm → sr_bdm → vice_president → president → site_manager
```

DB ENUM values in `mlm_rank_benefits.rank_name` and `associates.level` use these exact values.
`mlm_levels.level_name` uses **Title Case** for display (Associate, Senior Associate, etc.).

---

## 11. Ledger Schema

### `mlm_commission_ledger` — Single Source of Truth

| Column                | Type          | Purpose                                                                                                           |
| --------------------- | ------------- | ----------------------------------------------------------------------------------------------------------------- |
| `id`                  | BIGINT PK     | Auto-increment                                                                                                    |
| `beneficiary_user_id` | BIGINT FK     | Who receives the commission                                                                                       |
| `source_user_id`      | BIGINT FK     | Who generated the sale                                                                                            |
| `source_user_name`    | VARCHAR       | Denormalized name for display                                                                                     |
| `commission_type`     | ENUM          | direct_sale, mlm_level_1/2/3, level_bonus, team_bonus, performance_bonus, investment_sale, royalty_pool, clawback |
| `amount`              | DECIMAL(12,2) | Commission amount (positive = credit, negative = clawback)                                                        |
| `status`              | ENUM          | pending → paid/processed/reversed/cancelled                                                                       |
| `payment_amount`      | DECIMAL(12,2) | Original payment that triggered this commission                                                                   |
| `property_id`         | BIGINT FK     | Plot ID (nullable for non-property commissions)                                                                   |
| `booking_id`          | BIGINT FK     | Booking reference (nullable for investment commissions)                                                           |
| `receipt_id`          | BIGINT FK     | Payment receipt or investment ID (nullable)                                                                       |
| `rank_at_time`        | VARCHAR       | Agent's rank when commission was calculated                                                                       |
| `period`              | VARCHAR       | Month/Year for batching (e.g., "6/2026")                                                                          |
| `notes`               | TEXT          | Free-form notes                                                                                                   |
| `hold_until`          | DATE          | Escrow hold date (nullable)                                                                                       |
| `created_at`          | TIMESTAMP     | When calculated                                                                                                   |
| `updated_at`          | TIMESTAMP     | Last modification                                                                                                 |

### Indexes on Ledger

- `idx_ledger_beneficiary` on `(beneficiary_user_id)`
- `idx_ledger_source` on `(source_user_id)`
- `idx_ledger_status` on `(status)`
- `idx_ledger_type` on `(commission_type)`
- `idx_ledger_booking` on `(booking_id)`
- `idx_ledger_period` on `(period)`
- `idx_ledger_created` on `(created_at)`

---

## 12. Current System State

### 12.1 Ledger Entries by Type

| Type              | Count   | Total          |
| ----------------- | ------- | -------------- |
| direct_sale       | 48      | ₹17,32,502     |
| mlm_level_1       | 18      | ₹5,52,500      |
| mlm_level_2       | 12      | ₹3,05,072      |
| mlm_level_3       | 8       | ₹1,02,000      |
| level_bonus       | 6       | ₹1,10,000      |
| team_bonus        | 9       | ₹1,61,000      |
| performance_bonus | 15      | ₹2,59,000      |
| **TOTAL**         | **118** | **₹36,74,902** |

### 12.2 Approved Payouts

| Batch               | Period | Total         | Status   |
| ------------------- | ------ | ------------- | -------- |
| APS-MPB-202601-3724 | 1/2026 | ₹19,92,385.60 | Approved |

### 12.3 Royalty Pool

| Month    | Accumulated | Qualified Managers |
| -------- | ----------- | ------------------ |
| Jun 2026 | ₹11,20,000  | 0                  |

---

## 13. Code Architecture

### 13.1 Core Services

| Service                           | File                                                   | Responsibility                                                          |
| --------------------------------- | ------------------------------------------------------ | ----------------------------------------------------------------------- |
| `MLMCommissionEngine`             | `app/Services/MLM/MLMCommissionEngine.php`             | Primary commission calculator — rank lookup, upline walk, ledger writes |
| `HybridCommissionEngine`          | `app/Services/HybridCommissionEngine.php`              | Colony-specific 3-track engine with pricing matrix                      |
| `CommissionManager`               | `app/Services/MLM/CommissionManager.php`               | Gateway — routes to correct engine based on booking type                |
| `RankEvaluationService`           | `app/Services/RankEvaluationService.php`               | Rank evaluation + promotion + associates.level sync                     |
| `CommissionReconciliationService` | `app/Services/MLM/CommissionReconciliationService.php` | Daily audit — orphans, discrepancies, integrity checks                  |
| `BookingLifecycleService`         | `app/Services/Sales/BookingLifecycleService.php`       | Booking lifecycle — delegates commission to engine                      |
| `MoneyWorkflowService`            | `app/Services/Accounting/MoneyWorkflowService.php`     | EMI penalties, clawback triggers, registry eligibility                  |
| `InvestmentService`               | `app/Services/InvestmentService.php`                   | Investment CRUD + commission reversal on cancellation                   |

### 13.2 Admin Controllers

| Controller                   | Routes                      | Purpose                                                  |
| ---------------------------- | --------------------------- | -------------------------------------------------------- |
| `CommissionAdminController`  | `/admin/commission/*`       | CRUD for commission config, ledger view, rank management |
| `CommissionController`       | `/admin/commission-plans/*` | Plan editor, simulation, payout processing               |
| `BookingLifecycleController` | `/admin/sales/*`            | Booking management, commission display, RERA             |
| `MoneyWorkflowController`    | `/admin/finance/*`          | EMI penalties, bank reconciliation, TDS                  |

### 13.3 Cron Scripts

| Script                            | Schedule | Purpose                                       |
| --------------------------------- | -------- | --------------------------------------------- |
| `scripts/run_daily_penalties.php` | Daily    | Apply 18% p.a. penalty to overdue EMIs        |
| `scripts/run_clawback.php`        | Daily    | Clawback commissions for 30+ day defaulters   |
| `scripts/run_rank_promotion.php`  | Monthly  | Evaluate and promote associate ranks          |
| `scripts/run_royalty_pool.php`    | Monthly  | Distribute royalty pool to qualified managers |

### 13.4 Gamification Service

| Service               | File                                                | Responsibility                                                  |
| --------------------- | --------------------------------------------------- | --------------------------------------------------------------- |
| `GamificationService` | `app/Services/GamificationService.php`              | Role-based tier progression (Customer/Associate/Agent/Employee) |
| `GamificationService` | `app/Services/Gamification/GamificationService.php` | Points-based system (different from MLM ranks)                  |

> `GamificationService` tracks investment levels (Bronze→Diamond) which is separate from MLM ranks (Associate→Site Manager). The MLM rank system is the authoritative ranking for commission calculations.

---

## 14. Data Flow Diagram

```
┌──────────────────────────────────────────────────────────────┐
│                    BOOKING PAYMENT FLOW                       │
├──────────────────────────────────────────────────────────────┤
│                                                              │
│  Customer pays ₹10L                                          │
│       │                                                      │
│       ▼                                                      │
│  BookingLifecycleController::processPayment()                │
│       │                                                      │
│       ├──→ Update plot_bookings.status                       │
│       ├──→ Insert booking_payment_schedules (installment)    │
│       ├──→ Insert booking_payment_receipts                   │
│       │                                                      │
│       ▼                                                      │
│  BookingLifecycleService::recordPayment()                    │
│       │                                                      │
│       ├──→ Update installment paid_amount                    │
│       ├──→ Auto-advance booking status                       │
│       │                                                      │
│       ▼                                                      │
│  MLMCommissionEngine::calculateBookingCommission()           │
│       │                                                      │
│       ├──→ Resolve agent (booking.associate_id)              │
│       ├──→ Look up rank (associates.level)                   │
│       ├──→ Get rates (mlm_rank_benefits)                     │
│       ├──→ Walk upline (users.referred_by → 3 levels)        │
│       ├──→ Calculate per-level amounts                       │
│       ├──→ Apply 20% global cap                              │
│       │                                                      │
│       ▼                                                      │
│  Write to mlm_commission_ledger                              │
│       │  (one row per beneficiary)                           │
│       │                                                      │
│       ├──→ Agent:    ₹10,000 (direct_sale, 1%)              │
│       ├──→ Sponsor:  ₹20,000 (mlm_level_1, 2%)             │
│       └──→ Super:    ₹15,000 (mlm_level_2, 1.5%)           │
│                                                              │
│  Monthly: mlm_payout_batches created → approved → paid       │
│  Daily:   penalties applied → clawbacks triggered            │
│  Monthly: rank promotion evaluated                           │
└──────────────────────────────────────────────────────────────┘
```

```
┌──────────────────────────────────────────────────────────────┐
│                    INVESTMENT FLOW                            │
├──────────────────────────────────────────────────────────────┤
│                                                              │
│  Customer invests ₹50,000 via SIP                            │
│       │                                                      │
│       ▼                                                      │
│  InvestmentService::invest()                                 │
│       │                                                      │
│       ├──→ Create investment record                          │
│       ├──→ Update investor_level                             │
│       │                                                      │
│       ▼                                                      │
│  HybridCommissionEngine::investmentSale()                    │
│       │                                                      │
│       ├──→ Agent (referring associate): 2% = ₹1,000         │
│       ├──→ L1 upline: 0.7% = ₹350                          │
│       ├──→ L2 upline: 0.3% = ₹150                          │
│       │                                                      │
│       ▼                                                      │
│  Write to mlm_commission_ledger (type='investment_sale')     │
│                                                              │
│  On cancellation:                                            │
│  InvestmentService::cancelInvestment()                       │
│       │                                                      │
│       ├──→ Update investment status to 'cancelled'           │
│       ├──→ Reverse commissions via                           │
│       │   HybridCommissionEngine::reverseInvestmentCommissions()│
│       │   (marks ledger entries as 'cancelled')              │
│       └──→ Calculate refund (10% early / full after lock-in) │
└──────────────────────────────────────────────────────────────┘
```

---

## 15. Migration History

| Date       | Change                                                                         |
| ---------- | ------------------------------------------------------------------------------ |
| 2026-06-07 | Created `mlm_rank_benefits` with 6 ranks                                       |
| 2026-06-07 | Rebuilt `mlm_network_tree` from `users.referred_by`                            |
| 2026-06-07 | Extended `mlm_commission_ledger` with `booking_id`, `receipt_id`, `hold_until` |
| 2026-06-07 | Migrated 9 entries from `booking_commissions` → `mlm_commission_ledger`        |
| 2026-06-07 | Dropped `booking_commissions` table                                            |
| 2026-06-13 | Fixed `MLMCommissionEngine` associate lookup (user_id vs id)                   |
| 2026-06-13 | Fixed `property_id` FK violation (NULL for plot bookings)                      |
| 2026-06-21 | Unified rank naming: all lowercase (associate→site_manager)                    |
| 2026-06-21 | Dropped redundant `mlm_commission_levels` table                                |
| 2026-06-21 | Fixed `MLMCommissionEngine::RANK_ORDER` to 7 clean ranks                       |
| 2026-06-21 | Fixed `RankEvaluationService::evaluate()` to sync `associates.level`           |
| 2026-06-21 | Fixed `AssociateAuthController` default `current_level` to 'associate'         |
| 2026-06-21 | Fixed `MobileApiController` rank thresholds to use new names                   |
| 2026-06-21 | Fixed `DifferentialCommissionCalculator` name-to-number mapping                |
| 2026-06-21 | Updated `GamificationService` thresholds to 7 actual MLM ranks                 |
| 2026-06-21 | Added `total_points` and `current_level` columns to `users` table              |
| 2026-06-21 | Deprecated `RoyaltyPoolService` (replaced by `HybridCommissionEngine`)         |
| 2026-06-21 | Added `reverseInvestmentCommissions()` to `HybridCommissionEngine`             |
| 2026-06-21 | Wired `InvestmentService::cancelInvestment()` to reverse commissions           |
| 2026-06-21 | Created `mlm_settings` table with global config                                |

---

## 16. Remaining Work

### High Priority

1. **Process pending commissions** — ₹36,74,902 in ledger (status=pending)
2. **Create payout batch for 6/2026** — Period 6/2026 not yet created
3. **Test rank promotion** — First evaluation cycle hasn't run yet

### Medium Priority

4. **Flutter admin stubs** — 7 empty pages need API wiring (BookingApprovals, PlotManagement, etc.)
5. **Consolidate duplicate controllers** — PlotManagementController + PlotsAdminController
6. **Wire real KYC API** — NSDL PAN + UIDAI Aadhaar integration

### Low Priority

7. **Clean remaining dead table refs** — `commission_bonuses`, etc. (all in try/catch)
8. **Add wallet balance display** — Show current wallet balance per agent in commission pages
9. **Commission statement PDF** — Generate printable statements for agents
10. **Automate royalty pool cron** — Currently manual; needs scheduled monthly run

---

## 17. Troubleshooting

### "Commission not calculated"

- Check `mlm_commission_ledger` for entries with `status='pending'`
- Verify agent has `associates` extension record (not just `users` record)
- Check agent rank in `associates.level` (must be lowercase: associate, senior_associate, etc.)

### "Payout shows ₹0"

- Check `mlm_payout_batches` for approved batch in the period
- Verify `mlm_payouts` has records linked to the batch
- Check `user_wallets.hold_amount` — commissions in hold

### "Rank not promoted"

- Check `mlm_rank_benefits` thresholds for target rank
- Verify leg count from `mlm_network_tree` (must match `min_leg_count`)
- Verify qualifying volume from `mlm_profiles.lifetime_sales` (must match `min_qualifying_volume`)

### "Clawback not triggered"

- Check `booking_payment_schedules` for overdue installments (30+ days)
- Verify `penalty_audit` has penalty entries
- Check `mlm_clawback_log` for clawback records

### "Investment commission not reversed"

- Check `mlm_commission_ledger` for entries with `receipt_id=investmentId` and `commission_type='investment_sale'`
- Verify `status='cancelled'` after cancellation
- Check `investments.status='cancelled'`
