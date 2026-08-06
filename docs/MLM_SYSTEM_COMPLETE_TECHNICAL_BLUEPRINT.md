# 🌳 MLM Dynamic Commission Engine — Architecture & Configuration Blueprint
> **Document Type:** Corrected Technical Architecture & Dynamic Plan Configuration Blueprint  
> **Platform:** APS Dream Home (Real Estate ERP, CRM, MLM & Multi-Tenant White-Label SaaS)  
> **Prepared By:** Senior Lead Software Developer & Chief Architect  

---

## 📌 1. Clarification & Architectural Truth

> **IMPORTANT ARCHITECTURAL CORRECTION:**  
> The MLM System in APS Dream Home is **NOT locked into static hardcoded ranks or fixed binary legs**.  
> Instead, it is built as a **100% Dynamic, Versioned, and Configurable Compensation Engine** (`mlm_commission_plans`, `mlm_plan_levels`, `commission_plan_audit`).  
> 
> The Admin / Tenant has full authority in the Admin Panel (`/admin/commission-plans`) to define **any commission structure**, create custom ranks, set dynamic slab percentages, configure direct/team level bonuses, and change payout rules without writing code.

---

## 🏛️ 2. Dynamic Versioned Commission Engine (`CommissionPlanService.php`)

```
┌─────────────────────────────────────────────────────────────────────────────────────────────┐
│                           DYNAMIC VERSIONED MLM ENGINE ARCHITECTURE                         │
│                                                                                             │
│  1. Versioned Plans (`mlm_commission_plans`)  ──► Plan Name, Version #, Status (Active/Draft)│
│  2. Dynamic Levels (`mlm_plan_levels`)       ──► Level 1 to Level N (Direct/Team/Level Bonus)│
│  3. Customizable Slabs & Overrides            ──► Slab % per Rank, Performance Bonuses      │
│  4. Solvency Safeguard Hard Cap               ──► Admin-configurable total payout ceiling    │
│  5. Audit Trail (`commission_plan_audit`)     ──► Complete version history & change logs    │
└─────────────────────────────────────────────────────────────────────────────────────────────┘
```

---

## 📑 3. How Dynamic Commission Plans Work

Admin can configure the following dynamic components for any tenant or project:

### ⚙️ Configurable Components (Admin Panel):
1. **Direct Commission %:** Percentage paid to the direct sponsor on plot booking.
2. **Team / Level Bonuses:** Percentage paid to upline levels (Level 1, Level 2, Level 3... Level N).
3. **Differential Slab Ranks:** Dynamic rank slabs configured by the company (e.g. 5%, 8%, 11%, 14%, 15%).
4. **Matching & Leadership Bonuses:** Optional performance bonuses based on monthly team turnover.
5. **Statutory 194-H TDS Auto-Deduction:** 5% Income Tax TDS auto-deducted per Indian tax laws on every payout item, regardless of plan configuration.

---

## 🗄️ 4. Network Tree Architecture

```
┌─────────────────────────────────────────────────────────────────────────────────────────────┐
│                                FLEXIBLE NETWORK TREE ENGINE                                 │
│                                                                                             │
│  1. `network_tree` (Visual Hierarchy)                                                       │
│     • Flexible parent-child network visualization for Associate & Admin dashboards.         │
│     • Adapts dynamically to Matrix, Unilevel, or Direct Sponsor hierarchies.                │
│                                                                                             │
│  2. `mlm_network_tree` (Parent-Chain Ancestor Lookup)                                       │
│     • Fast single-query ancestor chain table (`user_id`, `parent_id`, `ancestor_path`).    │
│     • Calculates multi-level upline commissions in sub-40ms speed.                          │
└─────────────────────────────────────────────────────────────────────────────────────────────┘
```

---

## 💳 5. Financial Payout & E-Wallet Integration

Regardless of how the Admin configures the commission plan:
- **E-Wallet Ledger:** Net commission is credited to the associate's E-Wallet (`wallets` & `wallet_transactions`).
- **5% TDS Auto-Deduction:** Section 194-H TDS + Admin fee automatically applied.
- **1-Click Corporate Bank CMS Export:** Admin approves payout batch ➔ Exports ICICI / HDFC Corporate Bank CMS File for direct bank transfer.

---

## 💡 Summary & Correction Verdict

The software **does not enforce fixed static rank titles or forced binary leg constraints**. The system gives 100% control to the Real Estate Company / Admin to configure their own custom compensation plan, level commissions, and rank criteria dynamically in the Admin Dashboard!
