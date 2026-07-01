# APS Dream Home - Agent Rules & Project Status (Updated 2026-06-26)

---

## ⚠️ CRITICAL RULE: File Deletion (NEVER delete without this)

**Full details:** `DELETION_RULE.md`

### 7-Step Pre-Deletion Checklist (MANDATORY)

1. **What does it do?** — Read entire file, write 1-line purpose
2. **Is functionality reimplemented?** — Search for SAME features, not same filename
3. **Is it referenced anywhere?** — Routes, controllers, views, services, sidebar, DB menu
4. **Can it be reached via URL?** — Any route/controller/render maps to it
5. **Does it have DB data?** — Tables it reads/writes — check row counts
6. **What breaks if deleted?** — Trace all downstream effects
7. **Make the call** — ALL 6 pass = safe. ANY fail = DO NOT DELETE

### Safe Deletion

- Cache files, temp scripts, `_archive/` contents, test artifacts, IDE config = YES
- View/controller/service/config/helper files = MUST complete all 7 steps first

### When in doubt

- **MOVE to `_archive/`** — not DELETE. Recoverable vs irreversible.

### Lesson learned

- `commission_plan_manager.php` (769 lines) was deleted as "orphaned dead" — had real CRUD for `mlm_commission_plans` table (5 rows). Had to rebuild entirely as MVC (CommissionPlanController + 4 views + 11 routes + mlm_plan_levels table). **Cost: 1 full session.**

---

## Project Overview

- Custom PHP MVC Framework (NOT Laravel)
- Location: `C:\xampp\htdocs\apsdreamhome`
- Database: MySQL (port 3307), database `apsdreamhome`
- Server: XAMPP Apache (port 80)
- **DB credentials:** Host=127.0.0.1, Port=3307, User=root, Password=(empty)

## MCP Tools Available (API-Key Free)

| Tool                    | Purpose                                     |
| ----------------------- | ------------------------------------------- |
| **MySQL**               | Direct database queries, schema management  |
| **Sequential Thinking** | Step-by-step reasoning for complex problems |
| **Playwright**          | Browser automation, visual testing          |
| **Filesystem**          | File operations                             |
| **Memory**              | Knowledge graph storage                     |

## Architecture

- Custom MVC: Controllers → Views → Layout
- Controllers: `app/Http/Controllers/`
- Models: `app/Models/`
- Views: `app/views/`
- Services: `app/Services/`
- Routes: `routes/web.php`, `routes/api.php`
- Core: `app/Core/`

## Project Scale (2026)

- **Controllers:** 210+ PHP files
- **Models:** 146 PHP files
- **Views:** 668+ PHP files
- **Routes:** 1,481+ (web.php) + 92 (api.php)
- **Database Tables:** ~770 (InnoDB, PKs, 23 FK constraints)
- **Admin sidebar items:** 137 (all active, 100% route coverage)
- **E2E tests:** 164/165 pass (1 expected GodMode 403)

---

## 🧭 Quick Navigation Guide

### Where to Find Things

| Feature                   | Controller                               | View                          |
| ------------------------- | ---------------------------------------- | ----------------------------- |
| **Homepage**              | `Front\PageController::home()`           | `pages/home.php`              |
| **Properties**            | `Front\PageController::properties()`     | `pages/properties.php`        |
| **Property Detail**       | `Front\PageController@propertyDetails()` | `pages/property_detail.php`   |
| **Customer Dashboard**    | `Front\UserController::dashboard()`      | `pages/user_dashboard.php`    |
| **Login/Register**        | `Auth\CustomerAuthController`            | `auth/customer_*.php`         |
| **Admin Dashboard**       | `Admin\AdminController`                  | `admin/layouts/unified.php`   |
| **MLM Commission**        | `Admin\MLMCommissionController`          | `admin/mlm/dashboard.php`     |
| **Finance (Module 3)**    | `Admin\MoneyWorkflowController`          | `admin/finance/*.php`         |
| **Sales (Module 2)**      | `Admin\BookingLifecycleController`       | `admin/sales/*.php`           |
| **Backoffice (Module 5)** | `Admin\BackofficeController`             | `admin/backoffice/*.php`      |
| **Colony Pipeline**       | `Admin\ColonyPipelineController`         | `admin/colony-pipeline/*.php` |
| **AI Chatbot**            | `Front\AIBotController`                  | —                             |

### Folder Structure

```
app/
├── Core/           → Framework (Database, Router, Auth)
├── Http/
│   └── Controllers/
│       ├── Admin/      → Admin panel (30+ controllers)
│       ├── Auth/       → Login/Register (5 controllers)
│       ├── Front/      → Public pages (10+ controllers)
│       ├── Employee/   → Employee portal
│       ├── MLM/        → Network marketing
│       ├── AI/         → AI features
│       └── Api/        → API endpoints
├── Models/         → 146 models
├── Services/       → Business logic (AI, MLM, Finance, Sales)
├── Modules/        → Feature packages
├── Views/          → 668+ view templates
└── Helpers/        → Utility functions
```

---

## Current System Status (2026-06-26)

### E2E Test Results

- **164/165 PASS** (1 expected GodMode 403 for non-superadmin)
- PHP error log: Clean (zero project errors)

### Deep Scan

- 1,481+ route definitions, 837+ returning HTTP 200/302
- 0 real 500 errors
- 137/137 sidebar URLs verified

### Database

- ~770 tables, all InnoDB, all with PKs, 23 FK constraints
- 4 active colonies: Suryoday (id=2), Braj Radha (id=3), Raghunath (id=4), Budh Bihar (id=5)
- 204 plots with actual dimensions
- Unified `role` column in `users`
- 56 active associates, 15 active MLM network tree nodes
- Ledger: 311 entries totaling ₹1,05,60,320

---

## Key Business Modules

### Module 1: Colony Development Pipeline

- Land → Colony → Plot Cutting → Pricing → Sales Ready
- Services: `PlotCutterService`, `ColonyPricingService`, `PlottingService`
- 14 routes under `/admin/colony-pipeline/*`

### Module 2: Customer Sales + Allotment + Registry

- 10 tables, 14-method service, 12 admin views
- EMI schedule, payments, demand letters, commissions, refunds, RERA compliance
- 20 routes under `/admin/sales/*`

### Module 3: Money Workflow + Accounting

- 15 tables, 50+ method service, 22 views
- Bank accounts, cash book, petty cash, cheques, TDS, GST, vendors, expenses, reconciliation
- 38+ routes under `/admin/finance/*`

### Module 4: MLM Commission Engine (HybridCommissionEngine)

- **4 Revenue Streams:** Plot Sale Commission (20% cap), Investment Plan Commission (3%), Salary & Incentive, Telecaller Commission
- **3 Tracks per Plot Sale:** Track A (Slab Differential 15%), Track B (Performance Rollup 3%), Track C (Milestone Escrow 2%)
- **Royalty Pool:** 2% outside 20% cap, distributed to Site Managers with ≥₹50L GBV
- **Monthly Bonuses:** Generation Bonus (2%/1.5%/1%/0.5% Gen1-7), Matching Bonus (100%/50%/25% Gen1-3)
- Full breakdown: `docs/COMMISSION_BREAKDOWN_1LAKH.md`

### Module 5: Backoffice + Daily Operations

- 8 tables, 17 views, 30 routes
- Attendance, leaves, payslips, lead pipeline, operations log, reports

---

## MLM Commission Engine — Complete Reference

### Rank System (RANK_SLABS)

| Rank           | GBV Threshold | Rate |
| -------------- | ------------- | ---- |
| associate      | ₹0 - ₹10L     | 5%   |
| sr_associate   | ₹10L - ₹35L   | 7%   |
| bdm            | ₹35L - ₹70L   | 10%  |
| sr_bdm         | ₹70L - ₹1.5Cr | 12%  |
| vice_president | ₹1.5Cr - ₹3Cr | 15%  |
| president      | ₹3Cr - ₹5Cr   | 18%  |
| site_manager   | ₹5Cr+         | 20%  |

### Network Tree Convention

- `mlm_network_tree.parent_id` stores **user_id** values (NOT associate PKs, NOT tree row IDs)
- `mlm_network_tree.level` stores **numeric depth** (1,2,3...), NOT rank name strings
- `mlm_network_tree.associate_id` is UNIQUE — one row per person

### Same-Level Override (Breakaway Safeguard)

- Gen 1 same-rank: 2.0%, Gen 2 same-rank: 1.0%

### Generation Bonus Rates (GenerationBonusEngine)

| Gen | Rate |
| --- | ---- |
| 1   | 2.0% |
| 2   | 1.5% |
| 3   | 1.0% |
| 4-7 | 0.5% |

### Matching Bonus Rates (MatchingBonusService)

| Gen | Rate |
| --- | ---- |
| 1   | 100% |
| 2   | 50%  |
| 3   | 25%  |

### Commission Ledger State

| Type              | Count   | Total            |
| ----------------- | ------- | ---------------- |
| direct_sale       | 64      | ₹22,36,010       |
| override          | 85      | ₹21,05,748       |
| matching_bonus    | 15      | ₹18,00,000       |
| royalty_pool      | 3       | ₹11,64,160       |
| level_bonus       | 21      | ₹11,50,500       |
| generation_bonus  | 4       | ₹6,25,000        |
| rank_bonus        | 8       | ₹6,20,000        |
| infinity_override | 2       | ₹2,50,000        |
| team_bonus        | 47      | ₹89,860          |
| performance_bonus | 47      | ₹59,637          |
| investment_sale   | 3       | ₹30              |
| **TOTAL**         | **311** | **₹1,05,60,320** |

### Key Service Files

| File                                                 | Purpose                                     |
| ---------------------------------------------------- | ------------------------------------------- |
| `app/Services/HybridCommissionEngine.php`            | Colony-specific 3-track engine (2183 lines) |
| `app/Services/MLM/MLMCommissionEngine.php`           | Full MLM engine (1434 lines)                |
| `app/Services/MLM/MatchingBonusService.php`          | Self-match skip + per-entry dedup           |
| `app/Services/MLM/GenerationBonusEngine.php`         | Dedup in persist, Gen rates                 |
| `app/Services/MLM/InfinityOverrideService.php`       | Infinity override with dedup                |
| `app/Services/Accounting/MoneyWorkflowService.php`   | EMI penalties, clawback, registry NOC       |
| `app/Services/Backoffice/DailyOperationsService.php` | Attendance, leaves, payslips                |

### Key DB Tables

| Table                       | Purpose                                       |
| --------------------------- | --------------------------------------------- |
| `mlm_settings`              | 18 rows — all rates and thresholds            |
| `mlm_rank_benefits`         | 7 rows — rank names, direct_sale_pct (5%-20%) |
| `mlm_levels`                | 7 rows — level_number, level_name             |
| `mlm_commission_ledger`     | 311 entries, 14 commission types              |
| `mlm_network_tree`          | 10 rows — user hierarchy                      |
| `associates`                | 40 active rows                                |
| `plot_bookings`             | Active bookings                               |
| `booking_payment_schedules` | EMI installments with accrued_penalty         |
| `penalty_audit`             | Daily penalty accrual audit trail             |

---

## Key Technical Details

### Layout System

- `BaseController::render($view, $data)` captures view via ob_start, passes as `$content` to layout
- Admin layout: `app/views/layouts/admin.php` — full HTML, echoes `$content` at line 256
- `AdminController` extends `BaseController`, sets `$this->layout = 'layouts/admin'`
- All admin controllers MUST extend `AdminController` (not `BaseController`)

### CSRF Protection

- `BaseController::__construct()` enforces CSRF on POST
- `validateCsrfOrFail()` — throws 403 on invalid token
- Skip pattern: `skipCsrfProtection()` in controller constructor for public POST endpoints
- All forms: `$_SESSION['csrf_token']` + `<meta name="csrf-token">` + `<input name="csrf_token">`

### Auth Flow

- `requireAdmin()` — checks `$_SESSION['admin_id']` or `$_SESSION['role'] === 'admin'`
- `requireLogin()` — checks `$_SESSION['user_id']`
- Test bypass: `/admin/login?test_login=1` auto-logs in as admin

### Cache System

- `CacheService` — Redis + file fallback, 5-min TTL for hot keys
- `RedisCache` — lazy-connecting Redis client, auto-fallback to file
- Admin cache management: `/admin/cache` (stats, flush, test connection)

### Key Routes

```
/                           → Homepage
/admin/login                → Admin login (test_login=1 bypass)
/admin/erp                  → Unified ERP Dashboard
/admin/mlm                  → MLM Commission Dashboard
/admin/sales/*              → Sales module (bookings, payments, etc.)
/admin/finance/*            → Finance module (cash, bank, TDS, GST, etc.)
/admin/backoffice/*         → Backoffice (attendance, leaves, etc.)
/admin/colony-pipeline/*    → Colony development pipeline
/user/dashboard             → Customer dashboard
/properties                 → Property listing
```

---

## Completed Features (Milestone Summary)

### Phase 1: Foundation (May 2026)

- Custom MVC framework, user auth, property CRUD, admin panel, header system, 7 user types

### Phase 2: Core Business (May 2026)

- Plot management, colony pipeline, land parcels, registries, booking lifecycle, EMI automation, commission engine (3 tracks), MLM network tree

### Phase 3: Finance & Operations (Jun 2026)

- Money workflow (15 tables, 50+ methods), TDS/GST/expense/vendor management, bank reconciliation, petty cash, cheque register

### Phase 4: AI & Automation (Jun 2026)

- Self-learning AI (12 tables), intent detection, price prediction, lead scoring, voice agents (3), OLN lead nurturing, drip campaigns

### Phase 5: Enterprise Features (Jun 2026)

- WebSocket (Ratchet), Redis cache, Razorpay checkout, Twilio gateway, AWS S3 storage, Docker production, CI/CD, SSL/HTTPS

### Phase 6: Polish (Jun 2026)

- i18n (815 keys EN/HI), saved searches + email alerts, NPS surveys, live chat, property auctions, 2FA/TOTP, API key management, system health monitoring

### Phase 7: Modules (Jun 2026)

- Module 2 (Sales), Module 3 (Finance), Module 4 (MLM with HybridCommissionEngine), Module 5 (Backoffice), Colony Pipeline, ERP Dashboard

### Phase 8: Recent (Jun 2026)

- Dashboard stat cards fixed (route conflict), commission breakdown document, full pipeline E2E verification (52/52), 3 daily cron scripts, i18n for 5 high-traffic pages, sales module i18n (12/12 views), Flutter APK build (debug + release)

### Phase 9: Associate Portal + CRM (Jun 2026)

- **Sidebar overhaul:** Removed dead code (120 lines), reorganized into 6 sections (Main/Earnings/Network/Properties/Account/Settings), collapsible with localStorage, role-specific items appear first
- **Layout fixes:** Dynamic layout for shared pages (address/insurance/KYC/investments/notifications) — associates see associate layout, not customer layout
- **Commission data:** Wallet + dashboard + commissions page now query `mlm_commission_ledger` (not legacy `commissions` table)
- **Header links:** Bell/Envelope/Profile icons in top header now link to notifications/messages/profile pages
- **Referral code banner:** Fixed white-on-white issue (removed `bg-gradient` class override)
- **CRM System (NEW):**
  - `leads` table used for all associate leads (not `inquiries`)
  - Pipeline stages: New → Contacted → Qualified → Proposal → Negotiation → Closed Won
  - Lead detail page with activity timeline, status updates, notes, follow-ups
  - Search + filter by status + pagination on leads list
  - Quick actions: Call/WhatsApp/Email directly from lead detail
  - Activity logging for every status change and note
- **Promote page:** `/become-associate` now renders inside portal layout (not standalone) for logged-in users
- **Share system (NEW):** QR code (real canvas-based), Copy Code/Link buttons, share to WhatsApp/Facebook/Telegram/Twitter/LinkedIn/Email/SMS + native share API
- **Auto-fill referral:** Registration forms auto-fill referral code from `?ref=` URL parameter
- **Document locker:** New route + controller method + view for associates
- **Messages route:** `/user/messages` redirects to notifications (no separate messages page yet)

---

## Pending Tasks

1. **Backoffice views i18n** — 14 files to wrap with `__()` calls
2. **Colony-Pipeline views i18n** — 6 files to wrap with `__()` calls
3. **Real KYC API** — NSDL PAN verification, UIDAI Aadhaar e-KYC integration
4. **Mobile responsiveness** — Admin portal mobile fixes
5. **Wire Flutter stub pages** — 4 admin stubs (BookingApprovals, CommissionApprovals, PlotManagement, UserManagement) + CRM + Employee + Customer bookings
6. **Add pull-to-refresh** across Flutter pages
7. **Wire FCM** — Push notifications for all Flutter roles
8. **Flutter mobile API endpoints** — 4 new JSON endpoints in `routes/api.php` for admin pages (bookings, commissions, plots, users)
9. **CRM Enhancements:**
   - Lead scoring AI (auto-score based on budget, engagement, source)
   - Bulk WhatsApp/SMS outreach from leads list
   - Lead assignment: associate can share leads with team members
   - Follow-up reminders (email/SMS alerts)
   - Lead import from CSV
   - Deal pipeline with revenue tracking
   - Commission calculator in lead detail
10. **Referral system improvements:**

- Track share analytics (who shared, which platform, click count)
- Referral leaderboard
  tiered referral bonuses

---

## Key Lessons Learned

1. **Always verify with real DB before dropping tables** — AGENTS.md estimates can be wrong
2. **E2E tests are the safety net** — caught 4 over-dropped MLM tables within seconds
3. **"0 code refs" insufficient** — must check FK incoming + view definitions + try/catch status
4. **Restoration is cheap** — `restore_mlm_tables.php` enabled safe experimentation
5. **3-pass safety pattern** (zero → 1 → 2 refs) is gold standard for cleanup
6. **Route conflicts cause silent failures** — `/admin/mlm` was pointing to wrong controller for months
7. **parent_id convention matters** — mlm_network_tree.parent_id stores user_id, NOT associate PK or tree row ID
8. **Same-level override prevents gaming** — upline can't earn more than downline by staying at same rank
9. **20% hard cap + monthly bonus separation** — per-transaction is capped, monthly bonuses are uncapped but limited by downline volume
10. **Differential model is correct** — upline gets the DIFFERENCE between their rate and downline's rate, not the full rate

---

## Quick Commands

```bash
# Start server
http://localhost/apsdreamhome/

# Admin panel
http://localhost/apsdreamhome/admin/login

# Test login (bypass CAPTCHA)
http://localhost/apsdreamhome/admin/login?test_login=1

# E2E test
node testing/visual_tests/E2E_MASTER_TEST.mjs

# PHP syntax check
php -l <file.php>

# Commission breakdown doc
docs/COMMISSION_BREAKDOWN_1LAKH.md
```
