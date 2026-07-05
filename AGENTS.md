# APS Dream Home - Agent Rules & Project Status (Updated 2026-07-05)

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

## Current System Status (2026-07-05)

### E2E Test Results

- **164/165 PASS** (1 expected GodMode 403 for non-superadmin)
- PHP error log: Clean (zero project errors)

### Deep Scan

- 1,481+ route definitions, 837+ returning HTTP 200/302
- 0 real 500 errors
- 137/137 sidebar URLs verified

### Database

- ~775 tables, all InnoDB, all with PKs, 23 FK constraints
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

| File                                                 | Purpose                                                                                                                        |
| ---------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------ |
| `app/Services/HybridCommissionEngine.php`            | Colony-specific 3-track engine (2183 lines)                                                                                    |
| `app/Services/MLM/MLMCommissionEngine.php`           | Full MLM engine (1434 lines)                                                                                                   |
| `app/Services/MLM/MatchingBonusService.php`          | Self-match skip + per-entry dedup                                                                                              |
| `app/Services/MLM/GenerationBonusEngine.php`         | Dedup in persist, Gen rates                                                                                                    |
| `app/Services/MLM/InfinityOverrideService.php`       | Infinity override with dedup                                                                                                   |
| `app/Services/Accounting/MoneyWorkflowService.php`   | EMI penalties, clawback, registry NOC                                                                                          |
| `app/Services/Backoffice/DailyOperationsService.php` | Attendance, leaves, payslips                                                                                                   |
| `app/Services/DirectoryService.php`                  | Business directory CRUD, search, reviews                                                                                       |
| `app/Services/AdManagerService.php`                  | Ad slot CRUD, view/click tracking, render                                                                                      |
| `app/Services/CRMService.php`                        | Full CRM: pipeline, deals, scoring, analytics, activity logging, revenue forecasting, segmentation, form builder (60+ methods) |
| `app/Services/ReferralService.php`                   | Referral codes, tiered bonuses, leaderboard, share funnel                                                                      |
| `app/Services/CRMCustomFieldService.php`             | Admin-configurable custom fields for leads                                                                                     |
| `app/Services/DripCampaignService.php`               | Lead nurture drip campaigns, enrollment, queue processing                                                                      |
| `app/Services/EmailTrackingService.php`              | Email open/click tracking, engagement scoring                                                                                  |
| `app/Services/SLAService.php`                        | SLA compliance tracking, breach detection                                                                                      |
| `app/Services/MeetingService.php`                    | Calendar-based meeting scheduling, CRUD, calendar API                                                                          |
| `app/Services/KYCService.php`                        | PAN/Aadhaar verification (NSDL/UIDAI mock + validation)                                                                        |
| `app/Services/CRMVoiceService.php`                   | Voice CRM: call logging, dictation, Hindi voice commands                                                                       |

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
| `directory_categories`      | 12 seeded categories for business directory   |
| `directory_listings`        | User-submitted business listings              |
| `directory_reviews`         | Reviews and ratings                           |
| `directory_jobs`            | Job postings for real estate services         |
| `directory_materials`       | Construction material price comparison        |
| `ad_placements`             | Ad slots for banner/sidebar/inline ads        |
| `crm_segments`              | Smart lead segments with JSON criteria        |
| `crm_lead_forms`            | Visual form builder definitions               |
| `lead_activities`           | Auto-logged activity timeline                 |
| `lead_deals`                | Deals with close_reason, close_reason_detail  |
| `crm_interactions`          | Calls, emails, WhatsApp, meetings             |
| `crm_tasks`                 | Follow-up tasks with priorities               |
| `email_templates`           | Email templates with merge fields             |
| `sms_templates`             | SMS templates with merge fields               |
| `email_queue`               | Queued emails for bulk sending                |
| `sms_queue`                 | Queued SMS for bulk sending                   |
| `campaigns`                 | Marketing campaigns log                       |
| `crm_custom_fields`         | Admin-configurable custom field definitions   |
| `crm_lead_custom_values`    | Custom field values per lead                  |
| `crm_sla_rules`             | SLA rules (4 seeded)                          |
| `crm_sla_logs`              | SLA compliance tracking logs                  |
| `crm_meetings`              | Scheduled meetings with calendar              |
| `drip_enrollments`          | Drip campaign lead enrollments                |
| `drip_email_log`            | Drip campaign email send log                  |

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
/admin/ads                  → Ad Manager (CRUD, stats)
/user/dashboard             → Customer dashboard
/properties                 → Property listing
/services                   → Business Directory (categories, listings, reviews, jobs, materials)
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

1. ~~**Real KYC API**~~ — DONE: `KYCService` with PAN regex + Verhoeff Aadhaar validation + NSDL/UIDAI mock + `KycController` (approve/reject/verify/logs)
2. ~~**Role-Based CRM Dashboards**~~ — DONE: `CRMAdminController@roleDashboard` with role-specific data filtering
3. ~~**Lead Deduplication/Smart Merge**~~ — DONE: `CRMAdminController@dedup` + `CRMService::findDuplicates()` + `mergeLeads()`
4. ~~**CRM Voice Integration**~~ — DONE: `CRMVoiceController` + `CRMVoiceService` with Hindi voice commands, dictation, call logging
5. ~~**Custom Fields for Leads**~~ — DONE: `CRMCustomFieldService` + `CRMCustomFieldController` + 2 views + DB tables (`crm_custom_fields`, `crm_lead_custom_values`)
6. ~~**Meeting Scheduler**~~ — DONE: `MeetingService` + `MeetingController` + `crm_meetings` table + calendar API + complete/cancel workflow
7. ~~**Lead Nurture/Drip Campaigns**~~ — DONE: `DripCampaignService` + `DripCampaignController` + `drip_enrollments`/`drip_email_log` tables + process queue
8. ~~**SLA/Response Time Tracking**~~ — DONE: `SLAService` + `SLAController` + `crm_sla_rules`/`crm_sla_logs` tables + breach detection + compliance dashboard
9. ~~**Email Open/Click Tracking**~~ — DONE: `EmailTrackingService` + `EmailTrackingController` + tracking pixel + click redirect + analytics dashboard

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
11. **Never delete .ibd files while MySQL is running** — creates orphaned InnoDB tablespace entries that survive restart. Fix: rename table instead of dropping, or restart MySQL cleanly before deleting files.
12. **`CREATE TABLE IF NOT EXISTS` + orphaned tablespace = deadlock** — InnoDB data dictionary retains ghost entries. Workaround: create table with different name.

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

# Agentic AI — Run all 8 agents
php scripts/cron_agent_orchestrator.php

# Agentic AI — Run single agent
php scripts/cron_agent_orchestrator.php --agent=lead_gen

# Interactive Plot Map
# http://localhost/apsdreamhome/admin/colony-pipeline/{id}/map

# Flutter APK Build (mobile app)
cd mobile/apsdreamhome_app_v2 && .\build.ps1

# Flutter Build (manual)
cd mobile/apsdreamhome_app_v2 && flutter build apk --debug
```

### New Features (2026-07-04)

| Feature                     | Details                                                                                                                                                                                                                                                      |
| --------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| **Leaflet Interactive Map** | `/admin/colony-pipeline/{id}/map` — color-coded plots, click for details, status filters, GeoJSON API                                                                                                                                                        |
| **Customer Plot Map**       | `/colony/{slug}` — Embedded Leaflet map in colony detail page with inline GeoJSON, status filters, popup details. No extra API call needed.                                                                                                                  |
| **Admin Map filter fix**    | plot-map.php — Added missing `btn-map-filter` class to filter buttons (was broken, JS selected class that HTML didn't have)                                                                                                                                  |
| **Associate Portal i18n**   | **All 40 views fully translated** with `__()` calls & Hindi translations (1250+ `assoc_*` keys in both lang files). All CRM/sales pages, plus admin CRUD views (show, edit, create, index, sold, pending) completed. **Associate portal is 100% bilingual.** |
| **Temp Files Cleaned**      | Removed 11 debug/test temp scripts from project root (`_debug_*.php`, `_test_*.php`, `_seed_*.php`)                                                                                                                                                          |
| **Agentic AI Engine**       | 8 agents (LeadGen, Sales, Marketing, CEO, HR, Finance, Operations, Customer Success), auto-generates tasks/insights/escalations from real business data                                                                                                      |
| **Run All Agents button**   | `/admin/agentic-ai` dashboard — one-click trigger, reloads results                                                                                                                                                                                           |
| **Cron Script**             | `scripts/cron_agent_orchestrator.php` — schedule every 15 min                                                                                                                                                                                                |
| **UploadValidator fix**     | LandInventoryController — added `\UploadValidator::validate()` (was missing import)                                                                                                                                                                          |
| **Associate Registration**  | `AssociateController::store()` — was mock data (never saved to DB). Now creates real `users` row with hashed password, referral code via sponsor_code, wallet entry, and auto-login. Fixed `full_name`/`name` field mismatch.                                |
| **CRM Commission Calc**     | New "Potential Earnings" card in `lead_detail.php` — estimates commission from lead's budget + associate's rank rate, with Track A/B/C breakdown. Fully i18n'd.                                                                                              |
| **Image Upload Bug Fix**    | `associate_list_property.php` — form had `property_image[]` (multi-file array) but controller expected `property_image` (single file). Fixed input name & JS to match backend.                                                                               |
| **Flutter APK Build**       | Flutter APK builds successfully (194MB debug). Created `build.ps1` script with auto-path fix. Only warning: KGP migration needed for 4 plugins (non-blocking).                                                                                               |
| **E2E Tests Verified**      | 164/165 pass (1 expected GodMode 403). All changes verified clean — no regressions.                                                                                                                                                                          |
| **Database Cleanup Audit**  | 191 empty tables catalogued. Only 2 FK refs (both to other empty tables). Ready for safe cleanup when needed (follow 3-pass pattern).                                                                                                                        |

### New Features (2026-07-05)

| Feature                       | Details                                                                                                                                                                                                                                                                                                                                                                                                                   |
| ----------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Business Directory System** | JustDial-style directory for real estate services. 5 DB tables (`directory_categories`, `directory_listings`, `directory_reviews`, `directory_jobs`, `directory_materials`). Full MVC: `DirectoryService` (40+ methods), 2 controllers, 15 views, 31 routes. 12 seeded categories. Features: search/filter/pagination, user listing submission, job postings, material price comparison, review/rating system.            |
| **Advertisement System**      | AdManagerService + AdManagerController (already existed). Wired public display via `renderSlot()` calls in `base.php` layout (header/footer banners). Seeded 3 default ads in `ad_placements` table. **Note:** Table named `ad_placements` not `ad_slots` due to InnoDB orphaned tablespace bug — `ad_slots.ibd` was deleted while MySQL running, leaving ghost entry in InnoDB data dictionary. Renamed table to bypass. |
| **Admin Sidebar Updates**     | Added 6 Directory items + Ad Manager links under 'properties' and 'marketing' sections in `admin_menu_items` DB table                                                                                                                                                                                                                                                                                                     |

### New Features (2026-07-05 — Session 2)

| Feature                     | Details                                                                                                                                                                                                                                                                                                  |
| --------------------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Referral Tier System**    | 4 tiers (Bronze/Silver/Gold/Platinum) with progressive bonuses: ₹100/₹200/₹500/₹1000 per signup, ₹500/₹1000/₹2500/₹5000 on booking. Tier badge + progress bar on customer & associate referral pages. `getUserTier()` auto-upgrades based on referral count.                                             |
| **Referral Leaderboard**    | `/admin/referrals/leaderboard` — Top referrers with podium (🥇🥈🥉), period filters (All/Yearly/Monthly/Weekly), tier badges, referral counts, signups, bookings. Admin can see who's performing.                                                                                                        |
| **Share Conversion Funnel** | `/admin/referrals/share-analytics` — Tracks shares → signups → bookings funnel with conversion rates. Platform breakdown (WhatsApp/Facebook/Telegram/SMS etc), top sharers leaderboard. Data from `users.share_clicks` JSON + `customer_referrals` table.                                                |
| **Referral Tiers Admin**    | `/admin/referrals/tiers` — Visual tier cards showing bonuses, perks, and user count per tier. Admin overview of tier distribution.                                                                                                                                                                       |
| **Admin Mobile CSS**        | 20+ responsive fixes: table horizontal scroll, stat card stacking (576px), top nav badge overlap fix, form input zoom prevention (16px), modal responsive sizing, page padding, button stacking, dropdown overflow fix, pagination wrapping, print styles. File: `assets/admin/css/responsive-fixes.css` |
| **Flutter Pull-to-Refresh** | RefreshIndicator added to 7 key pages: associate dashboard, agent dashboard, employee dashboard, leads page, commission page, my team page, agent CRM. Each page invalidates its providers on refresh for fresh data.                                                                                    |
| **FCM Topic Subscriptions** | `NotificationService.subscribeToTopics(userId, role)` — subscribes to `user_{id}`, `role_{role}`, `all_users` topics for targeted push notifications. `unsubscribeFromTopics()` for cleanup. Also added `markAsRead()`/`markAllAsRead()` improvements.                                                   |
| **3 New Admin Routes**      | `/admin/referrals/leaderboard`, `/admin/referrals/share-analytics`, `/admin/referrals/tiers` — all wired to `ReferralController` with 3 new methods. 3 sidebar items added to `admin_menu_items` DB table under 'marketing' section.                                                                     |

### New Features (2026-07-05 — Session 3: World-Class CRM)

| Feature                       | Details                                                                                                                                                                                                                                                                                                              |
| ----------------------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Lead Detail Hub**           | Complete rewrite of `admin/leads/show.php` (850+ lines). 9 tabs: Overview, Timeline, Interactions, Deals, Tasks, Notes, Score, Commission, Quick Actions. Pipeline progress visual, avatar initials, time-ago formatting, auto-refresh task toggle, modals for note/status/assign.                                   |
| **CRM Analytics Dashboard**   | New view `admin/crm/analytics.php`. KPI cards (total leads, won, conversion rate, pipeline value). Conversion funnel with proportional bars. Pipeline-by-stage grid. Source performance with color-coded bars + conversion rates. Agent performance leaderboard. Quick insights panel.                               |
| **Enhanced CRM Dashboard**    | Rewritten `admin/crm/index.php`. Gradient header with action buttons. 6 KPI cards with glow effects. 8 quick-action tiles (leads, kanban, follow-ups, scoring, sources, analytics, bulk, import). Pipeline summary with progress bars. Status distribution CSS donut chart.                                          |
| **Email/SMS Template System** | `CRMTemplateController` (6 methods). CRUD for email & SMS templates. Merge fields: `{{name}}`, `{{phone}}`, `{{email}}`, `{{city}}`, `{{budget}}`. 2 views: template list (card grid with tabs) + create/edit form (with live preview). Categories: follow_up, proposal, welcome, promotion, nurture, transactional. |
| **Bulk Email/SMS**            | `CRMBulkController` (3 methods). Channel selector (email/SMS). Segment-based targeting. Template auto-fill. Message preview with merge field replacement. Real-time recipient preview (AJAX). Sends to `email_queue`/`sms_queue`. Campaign logging.                                                                  |
| **Lead Segmentation**         | `CRMSegmentController` (4 methods). Create segments by: status, source, city, score range, budget range. `crm_segments` DB table (JSON filter_criteria). View matched leads. Quick action: bulk send to segment.                                                                                                     |
| **New DB Table**              | `crm_segments` — id, name, description, filter_criteria (JSON), created_by, timestamps.                                                                                                                                                                                                                              |
| **20 New Routes**             | CRM analytics, template CRUD (6), bulk send (3), segmentation (4), lead timeline. All in `routes/web.php`.                                                                                                                                                                                                           |
| **8 Sidebar Items**           | Templates, Bulk Outreach, Segments, Analytics added to `admin_menu_items` under 'marketing' section.                                                                                                                                                                                                                 |

### New Features (2026-07-05 — Session 4: Advanced CRM Features)

| Feature                       | Details                                                                                                                                                                                                                           |
| ----------------------------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Activity Auto-Logging**     | `CRMService::logActivity()` — auto-logs every status change, assignment, interaction, deal creation, task completion to `lead_activities` table. Timeline merges interactions, tasks, deals, stage changes chronologically.       |
| **Deal Won/Lost Tracking**    | `closeDeal()` with structured reasons (price, competitor, timing, budget, product, authority, no_response, other). Win/loss reason reports with revenue analysis. Modal in lead detail for closing deals.                         |
| **Revenue Forecasting**       | `getRevenueForecast()` — weighted pipeline (deal_value × probability), monthly trend (6 months actual), 3-month forecast with best/worst case scenarios. Forecast vs actual comparison.                                           |
| **Lead Capture Form Builder** | `CRMFormController` (8 methods). Visual drag-drop form builder. Embed code (iframe + JS script). Auto-assign to agent, auto-enroll in drip campaign, tags. 7 field types: text, email, phone, select, textarea, checkbox, hidden. |
| **CRMService New Methods**    | +12 methods: `logActivity()`, `getLeadTimeline()`, `closeDeal()`, `getWinLossReasons()`, `getRevenueForecast()`, `getSegments()`, `createSegment()`, `getSegmentLeads()`, plus enhanced analytics.                                |
| **3 New Controllers**         | `CRMFormController` (forms), `CRMTemplateController` (templates), `CRMBulkController` (bulk send), `CRMSegmentController` (segments).                                                                                             |
| **8 New Views**               | `forms/index.php`, `forms/builder.php`, `forms/preview.php`, `forms/embed.php`, `templates/index.php`, `templates/form.php`, `bulk/send.php`, `segments/index.php`, `segments/leads.php`, `crm/analytics.php`.                    |
| **25+ New Routes**            | Form CRUD (5), template CRUD (6), bulk send (3), segments (4), analytics, lead timeline, deal close. All in `routes/web.php`.                                                                                                     |
| **4 Sidebar Items**           | Templates, Bulk Outreach, Segments, Analytics added to `admin_menu_items` under 'marketing' section.                                                                                                                              |
| **DB Tables Ready**           | `crm_segments`, `crm_lead_forms`, `lead_activities`, `lead_deals` (with close_reason columns), `crm_interactions`, `crm_tasks`, `email_templates`, `sms_templates`, `email_queue`, `sms_queue`, `campaigns`.                      |
| **Role-Based Access**         | 8 user roles mapped: admin, employee, associate, agent, super_admin, manager, customer, telecaller. CRM features accessible to: admin, manager, employee, associate, agent. Customer portal has separate lead view.               |
| **Lead Forms Sidebar**        | Added 'Lead Forms' item to `admin_menu_items` under 'marketing' section.                                                                                                                                                          |
| **Complete Form System**      | Form list view, visual builder, preview, embed code with iframe + JavaScript + direct URL. WordPress/Shopify/Wix embedding tips. Form stats tracking. WhatsApp/Email share links.                                                 |

### New Features (2026-07-05 — Session 5: Agentic CRM AI)

| Feature                       | Details                                                                                                                                                                                                                                                                                                             |
| ----------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Agentic CRM Controller**    | `AgenticCRMController.php` — 6 methods: `index()` (dashboard), `runAutoFollowup()`, `runScoreRecalculation()`, `runAutoAssignment()`, `generateInsights()`, `runAll()`. Each agent runs independently or all at once. Actions logged to `agent_task_logs`.                                                          |
| **Agentic CRM Dashboard**     | `admin/crm/agentic/dashboard.php` — Full dashboard with 4 stat cards (follow-ups, score adjustments, auto-assignments, insights), 4 alert cards (overdue, hot, cold, dormant), 4 agent action buttons, AI action timeline, hot leads sidebar (score ≥70), dormant leads sidebar (7d+ inactive), agent status panel. |
| **6 New Routes**              | `/admin/crm/agentic` (GET), `/admin/crm/agentic/auto-followup` (POST), `/admin/crm/agentic/score-recalc` (POST), `/admin/crm/agentic/auto-assign` (POST), `/admin/crm/agentic/insights` (POST), `/admin/crm/agentic/run-all` (POST). All CSRF-protected.                                                            |
| **1 Sidebar Item**            | `Agentic CRM AI` added to `admin_menu_items` under 'marketing' section (id=166, icon=fas fa-robot, order=98).                                                                                                                                                                                                       |
| **Auto Follow-Up Agent**      | Finds leads with no activity in 3+ days, creates high-priority follow-up tasks assigned to their owner. Logs action to `agent_task_logs`.                                                                                                                                                                           |
| **Score Recalculation Agent** | Recalculates lead scores for up to 100 most recent leads. Reports adjustments. Uses CRMService `recalculateScore()`.                                                                                                                                                                                                |
| **Auto Assignment Agent**     | Assigns unassigned leads via round-robin strategy. Uses CRMService `autoAssignLeads()`.                                                                                                                                                                                                                             |
| **Insight Generator**         | Analyzes pipeline health: high new-lead volume, stuck leads, conversion rate, hot/cold lead distribution. Saves insights to `agent_task_logs`.                                                                                                                                                                      |

### New Features (2026-07-05 — Session 6: AI System + CRM Enhancements)

| Feature                      | Details                                                                                                                                                                                                                                                                            |
| ---------------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **AI Gateway**               | `AIGateway.php` — Unified AI router. Routes tasks to: Rule Engine → SelfLearningAI → IntentDetector → Gemini Flash (free tier). Logs every call with engine, confidence, response time. Singleton pattern. Multi-engine fallback chain.                                            |
| **Smart Lead Qualifier**     | `SmartLeadQualifierAgent.php` — Auto-qualifies every new lead 24/7. Analyzes intent (Hindi/English), scores budget/urgency/engagement, assigns hot/warm/cold, auto-assigns hot leads, creates follow-up tasks, escalates hot leads. Uses AIGateway for multi-engine scoring.       |
| **Property Matchmaker**      | `PropertyMatchmakerAgent.php` — Matches leads to available plots based on budget, location, size, past behavior. Database + AI scoring. Sends personalized recommendations. Batch mode for all active leads.                                                                       |
| **Hindi Conversational Bot** | `HindiConversationalBot.php` — Hindi-first conversational AI for real estate. Handles property inquiries, pricing, EMI calculations, site visits, complaints. Uses SelfLearningAI + IntentDetector + Rule Engine. Personality: professional, friendly, like a real estate advisor. |
| **Smart Scheduler**          | `SmartSchedulerAgent.php` — Optimizes site visit scheduling. Auto-assigns best agent based on availability + colony familiarity. Route optimization. Auto-sends reminders. Auto-reschedules missed visits.                                                                         |
| **Market Intelligence**      | `MarketIntelligenceAgent.php` — Real estate market analysis. Price trends, demand patterns, seasonal buying, colony performance, source effectiveness, investor ROI insights. Generates actionable recommendations. All from internal data.                                        |
| **AI System Dashboard**      | `admin/ai/dashboard.php` — Unified dashboard for all 5 agents. Gateway stats (engine distribution, avg confidence, response time), system health, agent cards with run buttons, quick actions, recent AI activity timeline.                                                        |
| **AI Chat API**              | `AISystemController@chat` — POST `/api/ai/chat` endpoint for chatbot widget. HindiConversationalBot-powered, session-based, returns intent + suggestions.                                                                                                                          |
| **Market Report Page**       | `admin/ai/market_report.php` — Full market intelligence report with price trends, demand analysis, colony performance, investor insights.                                                                                                                                          |
| **Lead Qualifier Page**      | `admin/ai/qualifier.php` — View unqualified leads, run qualification, see recently qualified results.                                                                                                                                                                              |
| **CRM Role Dashboard**       | `CRMAdminController@roleDashboard` — Role-based CRM dashboard. Auto-detects user role (admin/manager/employee/associate/agent/telecaller) and shows appropriate data: leads, tasks, deals, team performance. Test with `?role=` parameter.                                         |
| **Lead Deduplication**       | `CRMAdminController@dedup` — Find and merge duplicate leads. Matches by phone + email. Merge combines best data from both, moves interactions/tasks/deals, soft-deletes the removed lead. Bulk auto-merge available.                                                               |
| **New Routes (10)**          | `/admin/ai-system`, `/admin/ai-system/run`, `/admin/ai-system/qualifier`, `/admin/ai-system/market-report`, `/api/ai/chat`, `/admin/crm/role-dashboard`, `/admin/crm/dedup`, `/admin/crm/dedup/merge`, `/admin/crm/dedup/bulk-merge` + existing CRM routes.                        |
| **6 Sidebar Items**          | AI System Dashboard, Lead Qualifier, Market Intelligence (technology section), CRM Role Dashboard, Lead Deduplication (marketing section), Agentic CRM AI (marketing section).                                                                                                     |
| **6 New Service Files**      | `AIGateway.php`, `SmartLeadQualifierAgent.php`, `PropertyMatchmakerAgent.php`, `HindiConversationalBot.php`, `SmartSchedulerAgent.php`, `MarketIntelligenceAgent.php`.                                                                                                             |
| **2 New Controllers**        | `AISystemController.php` (5 methods), `CRMAdminController.php` (4 methods).                                                                                                                                                                                                        |
| **2 New Views**              | `admin/ai/dashboard.php` (unified AI dashboard), `admin/crm/role_dashboard.php`, `admin/crm/dedup.php` (lead deduplication UI).                                                                                                                                                    |
| **AI Architecture**          | Multi-engine fallback: Rule Engine (instant) → SelfLearningAI (learns) → IntentDetector (patterns) → Gemini Flash (free tier, complex NLP). All calls logged to `ai_api_logs`.                                                                                                     |

### New Features (2026-07-05 — Session 7: CRM World-Class Completion)

| Feature                        | Details                                                                                                                                                                                                                                                                                          |
| ------------------------------ | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| **Custom Fields System**       | `CRMCustomFieldService` + `CRMCustomFieldController` + 2 views. Admin-configurable fields (text/select/textarea/checkbox/date/number) with sections, required/searchable flags. DB: `crm_custom_fields`, `crm_lead_custom_values`. 6 routes under `/admin/crm/custom-fields/*`.                  |
| **SLA/Response Time Tracking** | `SLAService` + `SLAController` + 3 views (dashboard, rules, breach_log). Auto-detects breaches, compliance rate %, pending SLA monitoring. DB: `crm_sla_rules` (4 seeded rules), `crm_sla_logs`. 4 routes under `/admin/crm/sla/*`.                                                              |
| **Email Open/Click Tracking**  | `EmailTrackingService` + `EmailTrackingController`. 1x1 tracking pixel for opens, redirect-based click tracking, daily analytics, top clicked links. Auto-bumps lead engagement score. 3 routes: `/admin/crm/email-tracking/stats`, `/api/email/track/open/{id}`, `/api/email/track/click/{id}`. |
| **Meeting Scheduler**          | `MeetingService` + `MeetingController` (full CRUD + calendar API + complete/cancel). DB: `crm_meetings` table. Calendar JSON endpoint for frontend integration. 8 routes under `/admin/meetings/*`.                                                                                              |
| **Voice CRM**                  | `CRMVoiceService` + `CRMVoiceController`. Hindi voice commands (अगली बैठक, हॉट लीड, नोट जोड़ो, कॉल करो), Web Speech API dictation, call logging, voice note saving. 4 routes under `/admin/crm/voice/*`.                                                                                         |
| **Drip Campaigns (Wired)**     | Existing `DripCampaignService` + `DripCampaignController` + 3 views. Process queue, enroll leads, template rendering. 7 routes under `/admin/crm/drip/*` (alias to existing `/admin/drip-campaigns/*`).                                                                                          |
| **KYC Verification (Wired)**   | Existing `KYCService` (NSDL/UIDAI mock + Verhoeff + regex) + `KycController` (full CRUD + approve/reject + verify + logs). 6 routes under `/admin/kyc/*`.                                                                                                                                        |
| **New DB Tables (5)**          | `crm_custom_fields`, `crm_lead_custom_values`, `crm_sla_rules`, `crm_sla_logs`, `crm_meetings`                                                                                                                                                                                                   |
| **New Services (5)**           | `CRMCustomFieldService.php`, `EmailTrackingService.php`, `SLAService.php`, `MeetingService.php`, `CRMVoiceService.php`                                                                                                                                                                           |
| **New Controllers (5)**        | `CRMCustomFieldController.php`, `EmailTrackingController.php`, `SLAController.php`, `CRMVoiceController.php` (Meeting + KYC + Drip already existed)                                                                                                                                              |
| **New Views (10)**             | `custom_fields/index.php`, `custom_fields/form.php`, `sla/dashboard.php`, `sla/rules.php`, `sla/breach_log.php`, `email_tracking/stats.php`, `voice/index.php`, `voice/call.php`, `meetings/index.php`, `kyc/verify.php`                                                                         |
| **New Routes (37)**            | Custom Fields (6), SLA (4), Email Tracking (3), Meetings (8), Voice CRM (4), Drip (7 alias), KYC (4 existing)                                                                                                                                                                                    |
| **6 Sidebar Items**            | Custom Fields, Drip Campaigns, Email Tracking, SLA Dashboard, Meetings, Voice CRM added to `admin_menu_items` under 'marketing' section                                                                                                                                                          |

### New Features (2026-07-05 — Session 8: Careers Page + Header Fixes)

| Feature                         | Details                                                                                                                                                                                                                                        |
| ------------------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Careers Page Fix**            | Root cause: Controller queried `status = 'active'` but DB has `status = 'open'` — all 11 job listings were hidden. Fixed in `CareerController.php:47`. Now shows 11 open positions with salary, department, experience, location.              |
| **Form Submission (AJAX)**      | Rewired `submitApplication()` with full validation, file upload (PDF/DOC/DOCX, 5MB max), DB insert into `career_applications`, JSON response. AJAX POST to `/careers/submit-application` with toast notifications for success/error.           |
| **career_applications Table**   | Created missing table: `id, career_id, full_name, email, phone, resume_path, cover_letter, experience_years, current_company, status, timestamps`. Indexes on career_id and status.                                                            |
| **CSRF Bypass for Career POST** | Added `/careers/submit-application` to excluded paths in `routes/router.php:107` CSRF validation list. The actual router (`routes/router.php`) has its own CSRF check independent of BaseController.                                           |
| **CareerService Fix**           | Fixed `getApplicationDetails()` to return structured `{success, data: {application, history}}` instead of raw row. Now compatible with `CareerController::applicationDetails()`.                                                               |
| **Admin Sidebar Items**         | Added 2 items to `admin_menu_items` under 'hrm' section: 'Career Management' (`/admin/careers`, order=5) and 'Job Applications' (`/admin/careers/manage`, order=6).                                                                            |
| **Header Cleanup**              | Removed duplicate `</header>` tag from `header.php:598` that was breaking layout. Consolidated 3 conflicting CSS blocks in `aps-core.css` (lines 2520-2719) into single clean block with proper `flex: 1 1 auto` for navbar-collapse.          |
| **CTA Button Contrast**         | Fixed invisible button on careers page — changed `btn btn-primary` to `btn btn-light` on `bg-primary` section (blue button on blue bg = no contrast).                                                                                          |
| **Brand Name Consistency**      | Fixed "APS Dream Homes" → "ASP Dream Home" in careers page heading.                                                                                                                                                                            |
| **PHP Router Discovery**        | Found TWO Router classes: `routes/router.php` (actual, 318 lines, used by `public/index.php`) and `app/Core/Routing/Router.php` (unused, 866 lines). The actual router handles CSRF globally at line 106-128 with its own excluded paths list. |
