# APS DREAM HOME - COMPLETE PROJECT KNOWLEDGE BASE

## Single Source of Truth for Any AI Model (Text/Image/Video/Multimodal)

## Generated from DEEP CODE + DATABASE ANALYSIS (2026-07-15)

## Version: 2.0 | Location: `C:\xampp\htdocs\apsdreamhome`

---

## 🎯 PROJECT IDENTITY

**APS Dream Home** = Full-stack Real Estate + MLM Platform

- **Web**: Custom PHP MVC (NOT Laravel) + 1,720 views + 3,010 routes
- **Mobile**: Flutter 3.x (Riverpod + GoRouter + FCM) - 146 pages, 17 services
- **Database**: MySQL 8.0 (port 3307) - 596 tables, InnoDB, PKs, 23 FKs
- **Server**: XAMPP Apache (port 80) + WebSocket (Ratchet) + Cron (30+ jobs)
- **Location**: `C:\xampp\htdocs\apsdreamhome`

---

## 📊 ACTUAL CODEBASE SCALE (Measured from Code)

| Layer                | Count | Key Files                      |
| -------------------- | ----- | ------------------------------ |
| **Web Routes**       | 3,010 | `routes/web.php` (4,594 lines) |
| **API Routes**       | 387   | `routes/api.php` (658 lines)   |
| **Controllers**      | 230+  | 20 namespaces                  |
| **Services**         | 451   | 50 namespaces                  |
| **Models**           | 109   | `app/Models/`                  |
| **Views**            | 1,720 | `app/views/`                   |
| **DB Tables**        | 596   | `database/apsdreamhome.sql`    |
| **Flutter Pages**    | 146   | `lib/presentation/pages/`      |
| **Flutter Services** | 17    | `lib/data/services/`           |
| **Cron Jobs**        | 30+   | `scripts/cron_*.php`           |

---

## 🏗️ ARCHITECTURE (Actual Code Structure)

### **Backend: Custom PHP MVC**

```
app/
├── Core/                    # Framework (Database, Router, Auth, Security, Config)
├── Http/
│   ├── Controllers/
│   │   ├── Admin/ (80+)     # Admin panel controllers
│   │   ├── Api/ (30+)       # Mobile/API controllers
│   │   ├── Auth/ (10)       # 10 overlapping auth systems
│   │   ├── Front/ (35+)     # Public pages
│   │   ├── Associate/ (5)   # Associate portal
│   │   ├── Agent/ (3)       # Agent portal
│   │   ├── Employee/ (6)    # Employee portal
│   │   ├── MLM/ (3)         # MLM dashboards
│   │   ├── AI/ (5)          # AI features
│   │   └── ...              # Payment, Property, Reports, etc.
│   └── Middleware/          # ApiAuth, RateLimiter, etc.
├── Models/ (109)            # Eloquent-like but custom
├── Services/ (451)          # Business logic (see below)
├── Views/ (1,720)           # PHP templates
└── Helpers/                 # Global functions
```

### **Key Services (Business Logic)**

| Service                  | Lines   | Purpose                                                     |
| ------------------------ | ------- | ----------------------------------------------------------- |
| `HybridCommissionEngine` | 2,548   | 3-track MLM commission (20% cap, slab diff, rollup, escrow) |
| `MoneyWorkflowService`   | 2,593   | Double-entry accounting, bank rec, TDS, GST, cash book      |
| `CRMService`             | 1,915   | Leads, pipeline, scoring, drip, SLA, voice, forms           |
| `MobileApiController`    | 6,421   | **GOD CLASS** - all mobile endpoints in one file            |
| `AssociateController`    | 157,217 | **GOD CLASS** - entire associate portal                     |
| `AdminService`           | 27,449  | Users, roles, menus, settings, cache, MLM, finance          |
| `ReferralService`        | 42,947  | Tiered referrals, leaderboard, share funnel                 |
| `AIGateway`              | ~500    | Multi-engine AI router (Rules→SelfLearn→Intent→Gemini)      |

### **Frontend: Flutter Mobile**

```
lib/
├── core/                    # Config, theme, routing, DI
├── data/
│   ├── services/ (17)       # auth, crm, mlm, property, chat, etc.
│   ├── repositories/        # Data layer
│   └── models/              # DTOs
├── presentation/
│   ├── pages/ (146)         # Customer/Associate/Agent/Employee/Admin
│   ├── widgets/             # Reusable UI
│   └── providers/           # Riverpod state
└── main.dart                # Entry + GoRouter + providers
```

---

## 🔴 CRITICAL IMPLEMENTATION GAPS (Revenue/Function Breaking)

### **1. Payment → Commission = BROKEN (Revenue Leak)**

```php
// BookingLifecycleService::recordPayment() - processes EMI, sends receipt, broadcasts WS
// MISSING: $this->calculateCommission((int)$inst['booking_id']);
// HybridCommissionEngine::calculateCommission() EXISTS but NEVER CALLED from live flow
```

**Impact**: 100% of payments recorded but 0% commission calculated automatically

### **2. Dual MLM Trees DESYNC**

```sql
-- network_tree (23 rows) - BINARY for DISPLAY (left/right, BV, rank_id)
-- mlm_network_tree (120+ rows) - UNILEVEL for COMMISSION ENGINE (parent_id, level)
-- Registration writes to ONLY ONE → Commission engine sees empty downlines
```

**Code Path**: `UserRegistrationService::createNetworkTreeEntry()` only writes one table

### **3. Mobile API Contract DRIFT (~50% endpoints missing)**

| Flutter Service   | Calls        | Exists in routes/api.php |
| ----------------- | ------------ | ------------------------ |
| `AuthService`     | 12 endpoints | 4 exist                  |
| `CRMService`      | 15 endpoints | 6 exist                  |
| `MLMService`      | 18 endpoints | 7 exist                  |
| `PropertyService` | 10 endpoints | 5 exist                  |

### **4. 10 Overlapping Auth Controllers (All Live)**

| Controller                     | Routes                          | Status |
| ------------------------------ | ------------------------------- | ------ |
| `CustomerAuthController`       | `/login`, `/register`           | Active |
| `AssociateAuthController`      | `/associate/login`, `/register` | Active |
| `AgentAuthController`          | `/agent/login`, `/register`     | Active |
| `CoreAuthController`           | `/auth/login`, `/auth/register` | Active |
| `QuickAuthController`          | `/quick/login`                  | Active |
| `SmartRegistrationController`  | `/smart/register`               | Active |
| `RegistrationWizardController` | `/wizard/step1-4`               | Active |
| `UnifiedAuthController`        | `/unified/login`                | Active |
| `UnifiedRegisterController`    | `/unified/register`             | Active |
| `AuthController` (API)         | `/api/auth/*`                   | Active |

### **5. Router CSRF Bug**

```php
// routes/router.php:107 - strpos($uri, $path) === 0
$excludedPaths = ['/login', '/associate/login'];
// /farmer/login → strpos = 7 (not 0) → NOT EXCLUDED → 403 CSRF error
```

---

## 🟠 HIGH PRIORITY TECHNICAL DEBT

### **God Classes (SRP Violations)**

| Class                    | Lines   | Should Split Into                                                                                                                   |
| ------------------------ | ------- | ----------------------------------------------------------------------------------------------------------------------------------- |
| `MobileApiController`    | 6,421   | 13 domain controllers (Auth, Property, Booking, Lead, Deal, MLM, Notification, User, Loan, Chat, Employee, Support, AdminAnalytics) |
| `AssociateController`    | 157,217 | 8 domain controllers (Dashboard, Leads, Team, Commissions, KYC, Documents, Settings, Shares)                                        |
| `HybridCommissionEngine` | 2,548   | 6 services (Pricing, TrackA/B/C, Salary, Royalty)                                                                                   |
| `CRMService`             | 1,915   | 4 services (Lead, Interaction, Task, Deal)                                                                                          |
| `AdminService`           | 27,449  | 5 services (User, Role, Menu, Settings, Audit)                                                                                      |
| `ReferralService`        | 42,947  | 3 services (Tier, Leaderboard, Share)                                                                                               |

### **Frontend Asset Chaos**

```
public/assets/css/ (14 files, no bundling)
├── style.css (31KB)          ← MAIN
├── frontend.css (5.5KB)      ← DUPLICATES style.css globals (body, hero, btn, footer)
├── header.css (7KB)
├── admin.css (4KB)
├── advanced-features.css (15KB)
├── ai-chat.css (6KB)
├── ai-chat-enhanced.css (7KB)
├── chatbot.css (7KB)
├── notification-system.css (8KB)
├── modern-style.css (531B)
├── admin-login.css
├── employee.css
├── responsive-fixes.css
└── homepage.css (0 bytes - EMPTY)
```

**Issue**: `frontend.css` loads AFTER `style.css` → overrides premium design with simpler styles

### **No Database Migration System**

- 596 tables created via raw SQL in `/scripts/`
- No versioning, no rollback, no CI/CD integration
- `CREATE TABLE IF NOT EXISTS` + orphaned `.ibd` = InnoDB ghost tables
- Recent incident: `commission_plan_manager.php` deleted → had to rebuild entire MVC

### **Zero Automated Testing**

- `composer.json` has PHPUnit but **no `/tests` directory**
- `package.json` has Playwright but only visual regression tests
- No unit tests for commission engine, finance, CRM, auth

---

## ✅ WHAT'S ACTUALLY WORKING (Code Verified)

### **Commission Engine** - PRODUCTION READY

- 3-track: Track A (15% slab diff), Track B (3% rollup), Track C (2% escrow)
- 20% global cap with pro-rata scaling
- Breakaway safeguard (same-rank override: Gen1 2%, Gen2 1%)
- Diwali Dhamaka salary tiers
- Royalty pool (2% outside cap → Site Managers ≥₹5Cr GBV)
- Monthly generation (2%/1.5%/1%/0.5% Gen1-7) + matching (100%/50%/25% Gen1-3)

### **Finance Module** - PRODUCTION READY

- Double-entry journal with auto-balancing
- Bank reconciliation (statement vs book)
- TDS register (auto-calc, deposit, Form 16A)
- GST transactions (ITC reconciliation)
- Demand letter templates with variable substitution
- Cash flow forecasting with probability weighting

### **CRM System** - FUNCTIONAL

- 10-stage pipeline (New → Won/Lost)
- Lead scoring (engagement + demographic + behavioral)
- Drip campaigns with enrollment queue
- SLA tracking (4 seeded rules) with breach detection
- Email open/click tracking (pixel + redirect)
- Voice CRM (Hindi commands + Web Speech API dictation)
- Form builder (7 field types, embed code)

### **AI System** - OPERATIONAL

- `AIGateway`: Rules → SelfLearning → Intent → Gemini Flash (free tier)
- `SmartLeadQualifierAgent`: 24/7 auto-qualification
- `PropertyMatchmakerAgent`: Budget/location/behavior matching
- `HindiConversationalBot`: Real estate advisor persona
- `SmartSchedulerAgent`: Site visit optimization
- `MarketIntelligenceAgent`: Price trends, demand, ROI

### **Mobile App** - 60% FEATURE PARITY

| Role      | Pages | API Coverage                              |
| --------- | ----- | ----------------------------------------- |
| Customer  | 40    | Property, booking, EMI, wallet, referrals |
| Associate | 35    | Dashboard, leads, team, commissions, KYC  |
| Agent     | 25    | CRM, leads, visits, commissions           |
| Employee  | 20    | Attendance, tasks, leaves, HR             |
| Admin     | 26    | Analytics, users, content                 |

---

## 📱 MOBILE APP REALITY CHECK

### **Flutter Stack**

- **State**: Riverpod (providers in `lib/core/providers/`)
- **Routing**: GoRouter (public + protected routes)
- **Auth**: JWT + FCM tokens + deep links
- **API**: 17 services calling REST endpoints

### **Key Services (`lib/data/services/`)**

| Service                         | Responsibility                      | Endpoints Called |
| ------------------------------- | ----------------------------------- | ---------------- |
| `auth_service.dart`             | Login, register, OTP, password, FCM | 12               |
| `crm_service.dart`              | Leads, interactions, tasks, deals   | 15               |
| `mlm_service.dart`              | Dashboard, payouts, team, rank      | 18               |
| `property_listing_service.dart` | Browse, detail, favorites, submit   | 10               |
| `chat_service.dart`             | WebSocket + REST polling            | 4                |
| `notification_service.dart`     | FCM + local notifications           | 5                |

### **Pages by Role (`lib/presentation/pages/`)**

```
common/           # Public: home, login, register, blog, tools, calculators
customer/         # Dashboard, properties, bookings, profile, wallet, referrals
associate/        # Dashboard, leads, team, commissions, KYC, site visits
agent/            # CRM, leads, visits, commissions, dashboard
employee/         # Attendance, tasks, leaves, dashboard
admin/            # Analytics, users, content (limited)
```

---

## 🗄️ DATABASE SCHEMA (Key Tables)

### **Core Business**

| Table                       | Rows   | Purpose                                                                            |
| --------------------------- | ------ | ---------------------------------------------------------------------------------- |
| `users`                     | ~5,000 | All roles (role column: admin/customer/associate/agent/employee/telecaller/farmer) |
| `sites`                     | 4      | Colonies (Suryoday, Braj Radha, Raghunath, Budh Bihar)                             |
| `plots`                     | 204    | Plot inventory with dimensions                                                     |
| `plot_bookings`             | ~500   | Active bookings with payment schedules                                             |
| `booking_payment_schedules` | ~5,000 | EMI installments with penalties                                                    |

### **MLM Commission**

| Table                   | Rows | Purpose                             |
| ----------------------- | ---- | ----------------------------------- |
| `mlm_commission_ledger` | 311  | All commissions (14 types)          |
| `mlm_network_tree`      | 120  | Unilevel tree for commission engine |
| `network_tree`          | 23   | Binary tree for display             |
| `mlm_rank_slabs`        | 7    | Rank thresholds & rates             |
| `mlm_rank_benefits`     | 7    | Direct sale % per rank              |
| `mlm_settings`          | 18   | All rates & thresholds              |

### **Finance**

| Table                  | Purpose                         |
| ---------------------- | ------------------------------- |
| `bank_accounts_master` | Bank accounts with KYC          |
| `daily_cash_book`      | Double-entry cash transactions  |
| `petty_cash`           | Petty cash with running balance |
| `cheque_register`      | Issue/clear/bounce tracking     |
| `journal_entries`      | Double-entry GL                 |
| `vendor_payments`      | TDS + GST aware                 |
| `expense_approvals`    | Multi-level workflow            |

### **CRM**

| Table                       | Purpose                           |
| --------------------------- | --------------------------------- |
| `leads`                     | Main lead table (all roles)       |
| `crm_interactions`          | Calls, emails, WhatsApp, meetings |
| `crm_tasks`                 | Follow-ups with priorities        |
| `lead_deals`                | Pipeline deals with close reasons |
| `crm_segments`              | Smart segments (JSON criteria)    |
| `crm_lead_forms`            | Visual form builder definitions   |
| `drip_enrollments`          | Campaign enrollments              |
| `email_queue` / `sms_queue` | Bulk sending                      |

---

## 🛣️ ROUTE MAP (Actual)

### **Web Routes (3,010)**

```
/                                    → PageController@home
/admin/login                          → AdminAuthController (test_login=1 bypass)
/admin/erp                            → ERP Dashboard
/admin/mlm                            → MLM Commission Dashboard
/admin/sales/*                        → Sales Module (20 routes)
/admin/finance/*                      → Finance Module (38 routes)
/admin/backoffice/*                   → Backoffice (30 routes)
/admin/colony-pipeline/*              → Colony Pipeline (14 routes)
/admin/ads                            → Ad Manager
/properties                           → PropertyController@properties
/property-detail/{id}                 → PropertyController@propertyDetails
/user/dashboard                       → UserController@dashboard
/services                             → Business Directory
```

### **API Routes (387)**

```
/api/v2/mobile/auth/*                 → Mobile auth (login, register, OTP, password)
/api/v2/mobile/properties/*           → Browse, search, detail, favorites
/api/v2/mobile/mlm/*                  → MLM dashboard, payouts, team, genealogy
/api/v2/mobile/bookings/*             → Bookings, EMI, payments, site visits
/api/v2/mobile/leads/*                → Lead CRUD, status, followups
/api/v2/mobile/user/*                 → Profile, documents, bank, preferences
/api/v2/mobile/notifications/*        → Notifications, preferences
/api/v2/mobile/referral/*             → Dashboard, share tracking
/api/v2/mobile/support/*              → Tickets
/api/analytics/*                      → Admin analytics (7 endpoints)
/api/ai/*                             → AI Assistant, Gemini, valuation
```

---

## 👥 ROLE SYSTEM (9 Roles in `users.role`)

| Role          | Portal       | Key Permissions             |
| ------------- | ------------ | --------------------------- |
| `super_admin` | `/admin`     | GodMode, all access         |
| `admin`       | `/admin`     | Module access via RBAC      |
| `manager`     | `/admin`     | Department-scoped admin     |
| `employee`    | `/admin`     | Assigned modules only       |
| `telecaller`  | `/admin`     | Leads, calls, followups     |
| `associate`   | `/associate` | MLM dashboard, leads, team  |
| `agent`       | `/agent`     | CRM, leads, visits          |
| `customer`    | `/user`      | Bookings, payments, profile |
| `farmer`      | `/farmer`    | Land submissions            |

---

## ⚙️ CRON ORCHESTRATION (30+ Jobs)

| Script                             | Frequency   | Purpose                       |
| ---------------------------------- | ----------- | ----------------------------- |
| `cron_agent_orchestrator.php`      | 15 min      | Runs 8 AI agents              |
| `cron_booking_emi_scheduler.php`   | Daily 6 AM  | Generate EMI schedules        |
| `cron_penalty_accrual.php`         | Daily 12 AM | Accrue late penalties         |
| `cron_commission_calculator.php`   | Daily 2 AM  | Calculate pending commissions |
| `cron_drip_campaign_processor.php` | Hourly      | Send drip emails              |
| `cron_sla_monitor.php`             | 30 min      | Check SLA breaches            |
| `cron_auto_dialer.php`             | 5 min       | AI voice calls (9AM-8PM)      |
| `cron_emi_reminders.php`           | Daily 9 AM  | Call/WhatsApp/SMS reminders   |
| `cron_rank_evaluation.php`         | Monthly 1st | Evaluate rank upgrades        |
| `cron_payout_batch_processor.php`  | Weekly      | Process commission payouts    |

---

## 🔌 EXTERNAL INTEGRATIONS

| Service                | Purpose            | Status                   |
| ---------------------- | ------------------ | ------------------------ |
| **Razorpay**           | Payment gateway    | Configured (test mode)   |
| **Twilio**             | Voice/SMS          | Configured               |
| **WhatsApp Cloud API** | Business messaging | Webhook ready            |
| **Firebase (FCM)**     | Push notifications | Token registration works |
| **Google OAuth**       | Social login       | Configured               |
| **Gemini API**         | AI features        | Free tier configured     |

---

## 🎯 SENIOR DEV PRIORITY ROADMAP

### **Week 1: Fix Revenue Leaks (CRITICAL)**

```php
// 1. BookingLifecycleService::recordPayment()
public function recordPayment($data) {
    // ... existing payment logic ...
    // ADD THIS LINE:
    $this->calculateCommission((int)$inst['booking_id']);  // MISSING!
}

// 2. UserRegistrationService::createNetworkTreeEntry()
public function createNetworkTreeEntry($userId, $parentId = null) {
    // Write to BOTH tables in same transaction
    $this->db->insert('network_tree', [...]);
    $this->db->insert('mlm_network_tree', [...]);
}
```

### **Week 2: Mobile API Contract**

1. Extract `MobileApiController` → 13 domain controllers (Auth, Property, Booking, Lead, Deal, MLM, Notification, User, Loan, Chat, Employee, Support, AdminAnalytics)
2. Add missing routes to `routes/api.php` matching Flutter calls
3. Add contract tests

### **Week 3: Auth Consolidation**

- Keep: `CoreAuthController` (web), `AuthController` (API), `UnifiedRegisterController`
- Archive: 7 others with proper route migration
- Fix router CSRF exclusion for `/farmer/login`, `/employee/login`, etc.

### **Week 4: Testing Foundation**

- PHPUnit + Pest setup
- Unit tests for `HybridCommissionEngine` (critical financial code)
- Unit tests for `MoneyWorkflowService`
- Contract tests for Mobile API endpoints

---

## 🗑️ MD FILES TO IGNORE (Outdated)

Most `.md` files in root are **historical snapshots** - they document what _was planned_ or _partially built_ at that time. Trust **only**:

- `AGENTS.md` (current rules)
- `database/apsdreamhome.sql` (actual schema)
- Actual PHP/Dart code

---

## 📋 QUICK WINS (Do Today)

| Task                                                                               | Effort | Impact                       |
| ---------------------------------------------------------------------------------- | ------ | ---------------------------- |
| Delete `public/assets/css/homepage.css` (0 bytes)                                  | 1 min  | Removes useless HTTP request |
| Remove `RequestService` import from `BookingController.php:10`                     | 1 min  | Prevents latent fatal error  |
| Archive `app/views/admin/modules/properties/residential.php` (556 lines hardcoded) | 5 min  | Removes dead code            |
| Fix `header.php` duplicate CSS blocks (lines 2520-2719)                            | 10 min | Fixes specificity wars       |
| Add `Content-Security-Policy` nonce for inline scripts                             | 30 min | Security compliance          |

---

## 📝 MODEL-AGNOSTIC UNDERSTANDING

This project is a **feature-complete real estate + MLM platform** with:

- **Business logic**: Senior-level (commission engine, finance, CRM)
- **Engineering foundation**: Junior-level (God classes, no tests, broken contracts, 10 auth systems)

**Fix the foundation first** - the features are already there. The commission engine alone is worth protecting; it's a sophisticated 3-track system with proper caps, safeguards, and audit trails that would take months to rebuild.

---

_Generated from deep code + database analysis | 2026-07-15 | For any AI model (text/image/video/multimodal)_
