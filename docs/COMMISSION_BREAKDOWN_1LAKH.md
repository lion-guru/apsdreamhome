# ₹1 Lakh Payment — Complete Commission Distribution Breakdown

## Executive Summary

When a customer pays **₹1,00,000** for a plot, the commission system distributes it across **4 independent engines** + 1 royalty pool:

| Engine                                   | Type              | Cap             | Max on ₹1L             |
| ---------------------------------------- | ----------------- | --------------- | ---------------------- |
| **HybridCommissionEngine** (Track A+B+C) | Per-transaction   | 20% hard cap    | ₹20,000                |
| **Generation Bonus**                     | Monthly aggregate | Uncapped        | ₹2,000-₹5,000 (varies) |
| **Matching Bonus**                       | Monthly aggregate | Uncapped        | ₹500-₹5,000 (varies)   |
| **Royalty Pool**                         | Monthly pool      | 2% of all sales | ₹2,000 (accumulated)   |
| **Telecaller Incentive**                 | Per-transaction   | Flat/sqft       | ₹1,000-₹10,000         |

---

## PART 1: Per-Transaction Distribution (HybridCommissionEngine)

### Budget Allocation (₹1,00,000 payment)

```
┌─────────────────────────────────────────────────────┐
│ ₹1,00,000 Payment                                  │
│                                                     │
│ 20% GLOBAL CAP = ₹20,000                           │
│ ┌───────────────┬──────────────┬────────────────┐  │
│ │ Track A: 15%  │ Track B: 3%  │ Track C: 2%    │  │
│ │ ₹15,000       │ ₹3,000       │ ₹2,000         │  │
│ │ Slab Diff     │ Performance  │ Milestone      │  │
│ │               │ Rollup       │ Escrow         │  │
│ └───────────────┴──────────────┴────────────────┘  │
│                                                     │
│ ROYALTY POOL: 2% = ₹2,000 (outside 20% cap)        │
│ TELECALLER: ₹1,000 flat (outside 20% cap)          │
└─────────────────────────────────────────────────────┘
```

---

### Track A: Slab Differential (15% = ₹15,000 budget)

**How it works:**

- Agent gets their own rank rate FIRST
- Then each upline gets the DIFFERENCE between their rate and the previous person's rate
- Walks up to 7 levels deep
- Stops when ₹15,000 budget is exhausted

**Rank Rates (EXAMPLE CONFIGURATION ONLY):**
> **[WARNING] - Deprecation Notice**
> The ranks below are no longer hardcoded into the system. This table represents a sample configuration that the Admin can set up via the `mlm_rank_slabs` table. The `HybridCommissionEngine` dynamically pulls these values from the database.

| Rank | GBV Threshold | Rate |
|------|--------------|------|
| Associate | ₹0 - ₹10L | 5% |
| Sr. Associate | ₹10L - ₹35L | 7% |
| BDM | ₹35L - ₹70L | 10% |
| Sr. BDM | ₹70L - ₹1.5Cr | 12% |
| Vice President | ₹1.5Cr - ₹3Cr | 15% |
| President | ₹3Cr - ₹5Cr | 18% |
| Site Manager | ₹5Cr+ | 20% |

**Example Calculation: Associate (5%) sells ₹1 Lakh**

Upline chain (7 levels deep):

```
Level 0: Associate (seller)     → 5% rate
Level 1: Sr. BDM (upline 1)    → 12% rate
Level 2: BDM (upline 2)        → 10% rate
Level 3: President (upline 3)  → 18% rate
Level 4: Site Manager (upline 4)→ 20% rate
Level 5: Sr. Associate (upline 5)→ 7% rate
Level 6: VP (upline 6)         → 15% rate
Level 7: President (upline 7)  → 18% rate
```

**Differential Calculation:**

```
Agent (5%):        ₹1,00,000 × 5%  = ₹5,000  [Agent gets own rate]
Upline 1 (12%):    ₹1,00,000 × (12%-5%)  = ₹7,000  [12% - 5% = 7% differential]
Upline 2 (10%):    ₹1,00,000 × (10%-12%) = ₹0     [10% < 12%, no differential → STOP]

Note: If upline rate < previous rate, no commission flows upward.
      Commission only flows UP when rate INCREASES.

Total Track A distributed: ₹5,000 + ₹7,000 = ₹12,000
Budget remaining: ₹15,000 - ₹12,000 = ₹3,000 (unused)
```

**Breakaway Safeguard (Same-Level Override):**
If an upline has the SAME rank as the person below them:

- Gen 1 same-rank: gets 2.0% override
- Gen 2 same-rank: gets 1.0% override
- Gen 3+ same-rank: gets nothing

This prevents a scenario where two people at the same rank "stack" commissions.

---

### Track B: Performance Rollup Chain (3% = ₹3,000 budget)

**How it works:**

- Agent earns **0.3% per consecutive qualifying month**
- A "qualifying month" = agent or any downline generated ≥ ₹50,000 in bookings
- Maximum: 3% (10 consecutive months)

**Example Scenarios:**
| Consecutive Months | Bonus Rate | Amount on ₹1L |
|-------------------|------------|--------------|
| 1 month | 0.3% | ₹300 |
| 5 months | 1.5% | ₹1,500 |
| 10+ months | 3.0% (max) | ₹3,000 |

**First-time seller (1 month):** Gets ₹300
**Consistent seller (10 months):** Gets ₹3,000 (full budget)

---

### Track C: Milestone Reward Escrow (2% = ₹2,000 budget)

**How it works:**

- 2% of every payment goes into an **escrow bucket** for the agent
- When cumulative escrow hits milestones, a lump-sum reward is paid

**Milestones:**
| Threshold | Reward Name |
|-----------|------------|
| ₹50,000 | Bronze Milestone |
| ₹2,00,000 | Silver Milestone |
| ₹5,00,000 | Gold Milestone |
| ₹10,00,000 | Platinum Milestone |

**On ₹1L payment:** ₹2,000 goes to escrow.
After 25 such payments: ₹50,000 → Bronze milestone triggers!

---

### Royalty Pool (2% = ₹2,000, outside 20% cap)

**How it works:**

- 2% of EVERY payment goes into a monthly pool
- Pool accumulates all month
- At month-end, divided equally among **qualified Site Managers** (GBV ≥ ₹50L/month)
- If no one qualifies, pool carries forward

**Example:**

```
Month: June 2026
Total sales: ₹1,00,00,000 (100 × ₹1L payments)
Pool: ₹1,00,00,000 × 2% = ₹2,00,000
Qualified Site Managers: 4
Per manager share: ₹2,00,000 ÷ 4 = ₹50,000 each
```

---

### Telecaller Incentive (Outside 20% cap)

**How it works:**

- **Token payment (receiptId=0):** Flat ₹1,000
- **Subsequent payments:** ₹10/sqft proportional to payment
  - Formula: `(₹10 × plot_area_sqft) × (payment_amount / total_booking_value)`
  - Example: 1000 sqft plot, ₹1L payment on ₹10L total → ₹10 × 1000 × (1L/10L) = ₹1,000

**Telecaller Team Lead Overrides:**

- Gen 1 upline: 2% of payment = ₹2,000
- Gen 2 upline: 1% of payment = ₹1,000

---

## PART 2: Monthly Aggregate Bonuses (NOT per-payment)

These engines run at **month-end** on the ENTIRE team's sales volume, not per individual payment.

### Generation Bonus

**Who qualifies:** Senior Associate and above (level != 'associate')

**Rates by generation depth:**
| Generation | Rate | Example on ₹5L team volume |
|------------|------|---------------------------|
| Gen 1 (direct recruits) | 2.0% | ₹10,000 |
| Gen 2 | 1.5% | ₹7,500 |
| Gen 3 | 1.0% | ₹5,000 |
| Gen 4-7 | 0.5% | ₹2,500 each |

**Max generations by rank:**
| Rank | Can earn from |
|------|--------------|
| Sr. Associate | 2 generations |
| BDM | 3 generations |
| Sr. BDM | 4 generations |
| VP | 5 generations |
| President | 6 generations |
| Site Manager | 7 generations |

**How it works:**

1. Walk downline tree from leader
2. Sum sales volume per generation
3. Apply rate to each generation's volume
4. Total is the leader's generation bonus

**Example:** BDM with ₹10L total team sales in June

```
Gen 1 (3 direct recruits): ₹5,00,000 × 2.0% = ₹10,000
Gen 2 (5 people): ₹3,00,000 × 1.5% = ₹4,500
Gen 3 (2 people): ₹2,00,000 × 1.0% = ₹2,000
Total Generation Bonus: ₹16,500
```

---

### Matching Bonus

**Who qualifies:** BDM and above

**Match rates:**
| Generation | Match Rate | Example on ₹5K earnings |
|------------|-----------|------------------------|
| Gen 1 leader | 100% | ₹5,000 |
| Gen 2 leader | 50% | ₹2,500 |
| Gen 3 leader | 25% | ₹1,250 |

**How it works:**

1. Find qualified leaders (BDM+) in downline
2. Sum their commission earnings (direct_sale + level_bonus + performance_bonus + team_bonus)
3. Apply match percentage based on generation depth
4. Upline leader gets a MATCHING amount

**Example:** President's downline has 2 BDMs

```
BDM-A (Gen 1): Earned ₹8,000 this month → Match = ₹8,000 × 100% = ₹8,000
BDM-B (Gen 2): Earned ₹4,000 this month → Match = ₹4,000 × 50% = ₹2,000
Total Matching Bonus: ₹10,000
```

---

## PART 3: Complete ₹1 Lakh Breakdown (Per-Transaction)

### Scenario: Associate sells ₹1 Lakh plot, 1st month, no telecaller

```
┌──────────────────────────────────────────────────────────┐
│              ₹1,00,000 PAYMENT DISTRIBUTION              │
├──────────────────────────────────────────────────────────┤
│                                                          │
│  TRACK A — Slab Differential (15% budget)                │
│  ─────────────────────────────────────                   │
│  Agent (Associate, 5%):          ₹5,000                  │
│  Upline 1 (Sr.BDM, 12%):        ₹7,000  (12%-5% = 7%)  │
│  Track A Total:                  ₹12,000                 │
│  Budget used: 80% (₹3,000 remaining)                    │
│                                                          │
│  TRACK B — Performance Rollup (3% budget)                │
│  ─────────────────────────────────────                   │
│  1st month → 0.3%:               ₹300                    │
│                                                          │
│  TRACK C — Milestone Escrow (2% budget)                  │
│  ─────────────────────────────────────                   │
│  Escrow credit:                  ₹2,000                  │
│  (Cumulative: ₹2,000 toward ₹50K Bronze milestone)      │
│                                                          │
│  ROYALTY POOL (2% outside cap)                           │
│  ─────────────────────────────────────                   │
│  Pool contribution:              ₹2,000                  │
│  (Accumulated, distributed month-end to Site Managers)   │
│                                                          │
│  ═══════════════════════════════════════                 │
│  PER-TRANSACTION TOTAL:          ₹16,300                 │
│  Company retains:                ₹83,700 (83.7%)         │
│  ═══════════════════════════════════════                 │
│                                                          │
│  MONTHLY BONUSES (calculated at month-end on team vol)   │
│  ─────────────────────────────────────                   │
│  Generation Bonus (if leader):   ₹0-₹16,500 (varies)    │
│  Matching Bonus (if BDM+):       ₹0-₹10,000 (varies)    │
│                                                          │
│  GRAND TOTAL POTENTIAL:          ₹16,300 - ₹42,800      │
│  Company retains:                ₹57,200 - ₹83,700      │
└──────────────────────────────────────────────────────────┘
```

---

## PART 4: Per-Person Distribution (7-Level Upline Example)

### Setup

- **Sale:** ₹1,00,000
- **Seller:** Associate (5% rate)
- **Upline chain:** 7 levels with varying ranks

### Actual Ledger Entries Created

| #   | Person       | Rank      | Rate | Commission Type   | Amount      | Notes               |
| --- | ------------ | --------- | ---- | ----------------- | ----------- | ------------------- |
| 1   | You (seller) | Associate | 5%   | direct_sale       | ₹5,000      | Own slab            |
| 2   | Upline 1     | Sr. BDM   | 12%  | level_bonus       | ₹7,000      | 12%-5% differential |
| 3   | You          | —         | 0.3% | performance_bonus | ₹300        | 1st month           |
| 4   | You          | —         | 2%   | team_bonus        | ₹2,000      | Milestone escrow    |
| —   | Royalty Pool | —         | 2%   | —                 | ₹2,000      | Monthly pool        |
|     |              |           |      | **TOTAL**         | **₹16,300** |                     |

### Monthly Bonuses (additional, at month-end)

If your upline is a **President** with 3 qualifying BDMs in downline:

| #   | Leader    | Their Rank | Earnings Matched         | Match % | Bonus       |
| --- | --------- | ---------- | ------------------------ | ------- | ----------- |
| 5   | President | President  | ₹8,000 (BDM-A earnings)  | 100%    | ₹8,000      |
| 6   | President | President  | ₹4,000 (BDM-B earnings)  | 50%     | ₹2,000      |
| 7   | President | President  | ₹2,000 (BDM-C earnings)  | 25%     | ₹500        |
|     |           |            | **Matching Bonus Total** |         | **₹10,500** |

### Grand Total (with monthly bonuses)

```
Per-transaction:        ₹16,300
Matching bonus:        +₹10,500
Generation bonus:      +₹0-₹16,500 (depends on team volume)
                       ─────────
TOTAL POTENTIAL:       ₹26,800 - ₹42,800
COMPANY RETAINS:       ₹57,200 - ₹73,200
```

---

## PART 5: Is Distribution Too High?

### Analysis

| Metric                        | Value      | Assessment                 |
| ----------------------------- | ---------- | -------------------------- |
| Max per-transaction (20% cap) | ₹20,000    | ✅ Standard MLM cap        |
| Actual per-transaction        | ₹16,300    | ✅ 16.3% — below cap       |
| Monthly bonuses (additional)  | ₹0-₹27,000 | ⚠️ Can push total to 42.8% |
| Company minimum retain        | ₹57,200    | ✅ 57.2% minimum           |
| Industry standard retain      | 50-60%     | ✅ Within range            |

### Verdict

**Per-transaction (16.3%)** — HEALTHY, well within the 20% cap.

**With monthly bonuses (up to 42.8%)** — This is where it gets aggressive. However:

1. **Generation Bonus is on TOTAL team volume**, not per-transaction. If your team does ₹10L in sales, the generation bonus is ₹2L (2%) — not 2% of each payment.

2. **Matching Bonus requires qualified leaders** (BDM+). Not everyone in the chain qualifies.

3. **Royalty Pool requires ₹50L monthly GBV** per Site Manager. Most won't qualify.

4. **Real-world scenario:** An Associate selling ₹1L gets ₹16,300. No monthly bonuses apply because they don't have a downline. **Company retains ₹83,700 (83.7%).**

5. **Only at President/Site Manager level** with large teams do the monthly bonuses add up. At that point, the team volume justifies the payout.

### Recommendation

The system is **well-balanced** because:

- 20% hard cap on per-transaction commissions
- Monthly bonuses require qualifying volume thresholds
- Most sellers (Associates) only get 16.3%
- Only senior leaders with large teams get the full 42.8%
- Company always retains minimum 57.2%

---

## Quick Reference Card

```
ON ₹1,00,000 PAYMENT:
━━━━━━━━━━━━━━━━━━━━━

Track A (Slab Diff):     ₹5,000 - ₹15,000  (rank dependent)
Track B (Performance):   ₹300 - ₹3,000     (month dependent)
Track C (Escrow):        ₹2,000            (always)
Royalty Pool:            ₹2,000            (always, month-end dist)
Telecaller:              ₹1,000 - ₹10,000  (if assigned)

PER-TRANSACTION TOTAL:   ₹10,300 - ₹32,000
COMPANY RETAINS:         ₹68,000 - ₹89,700

MONTHLY (additional):
Generation Bonus:        2% to 0.5% of team volume
Matching Bonus:          100%/50%/25% of downline earnings
```
