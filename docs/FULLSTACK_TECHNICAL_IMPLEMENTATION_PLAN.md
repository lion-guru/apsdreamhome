# 🛠️ Full-Stack Technical Implementation Plan — Frontend, Backend, DB & Mobile
> **Document Type:** Technical Execution Plan & Development Architecture  
> **Target Platform:** APS Dream Home (Real Estate ERP, CRM, MLM & Multi-Tenant White-Label SaaS)  
> **Prepared By:** Senior Lead Software Developer & Chief Architect  

---

## 🏛️ 1. Backend Architecture Plan (Custom PHP 8.3 MVC Engine)

### 📌 Core Principles
- **No Heavy Framework Overhead:** Custom ultra-lightweight MVC engine delivering sub-40ms response latency.
- **Service Layer Pattern:** Logic extracted from controllers into 454 dedicated Domain Services under `app/Services/`.
- **Prepared Statement Security:** 100% parameter binding and integer casting to guarantee zero SQL injection vulnerabilities.

### 🔧 Backend Execution Roadmap
```
┌──────────────────────────────────────────────────────────────────────────────────┐
│                               BACKEND STACK PIPELINE                             │
│                                                                                  │
│  [Request] ──► Router (routes/web.php & api.php)                                 │
│                     │                                                            │
│                     ▼                                                            │
│               CSRF & JWT Auth Middleware                                         │
│                     │                                                            │
│                     ▼                                                            │
│               Controller (App\Http\Controllers\)                                 │
│               • Extends AdminController / BaseApiController                      │
│               • Uses TenantAwareTrait                                            │
│                     │                                                            │
│                     ▼                                                            │
│               Action / Domain Service (App\Services\)                            │
│               • Uses ServiceTenantTrait (Enforces tenant_id on ALL SQL writes)   │
│                     │                                                            │
│                     ▼                                                            │
│               Model / PDO Data Access Layer                                      │
│               • Model::$tenantScoped = true                                      │
│                     │                                                            │
│                     ▼                                                            │
│               Database (MySQL 8.0) & Cache (Redis `t{N}_`)                       │
└──────────────────────────────────────────────────────────────────────────────────┘
```

#### Backend Tasks:
1. **Controller Decoupling:** Refactor heavy controllers (`CRMController`, `AdminController`, `MobileApiController`) by delegating business logic to specific Action Services.
2. **API Payload Validation:** Implement strict payload schema validation middleware for all mobile JSON endpoints in `app/Http/Controllers/Api/`.
3. **Error Logging Standard:** Ensure zero silent exception swallowing — all `catch` blocks must record trace via `error_log()`.

---

## 🎨 2. Frontend Architecture Plan (Web Admin & Portals)

### 📌 Core Principles
- **Modern Unified Design System:** Built on Bootstrap 5, CSS custom properties (tokens), glassmorphism cards, dynamic dark/light themes.
- **Responsive Layout Engine:** Single master layout `app/views/layouts/admin.php` inherited by all admin controllers.
- **Interactive Plot Layout Engine:** Interactive HTML5 Canvas / SVG rendering for Colony → Sector → Plot maps with real-time status color coding (Green = Available, Red = Booked, Yellow = Hold, Blue = Registered).

### 🔧 Frontend Execution Roadmap
```
app/views/
├── layouts/
│   ├── admin.php           → Master Admin Shell (Sidebar, Topbar, Alerts)
│   ├── auth.php            → Modern Auth Layout (Login, Register)
│   └── public.php          → Customer & Landing Pages
├── admin/                  → 1,700 Admin views using dot-notation (`admin.plots.index`)
└── components/             → Reusable UI Widgets (Cards, Tables, Modals, Badges)
```

#### Frontend Tasks:
1. **Inline JS Decoupling:** Extract inline `<script>` blocks from view templates into modular JS assets under `public/assets/js/admin/`.
2. **Interactive Layout Enhancement:** Upgrade SVG plot layout viewer to support touch-pinch zoom, pan, and real-time plot detail tooltips.
3. **Design Token Standardization:** Verify all primary/secondary button colors and badges use unified CSS variables in `public/assets/css/`.

---

## 📱 3. Mobile App Architecture Plan (Flutter v1.2.0)

### 📌 Core Principles
- **Multi-Role Single App:** Single Flutter codebase supporting 5 distinct role dashboards: Admin, Associate/Agent, Customer, Employee, and Farmer.
- **Clean Architecture (Presentation / Domain / Data):** 147 Dart screens organized by feature in `mobile/apsdreamhome_app_v2/lib/`.
- **REST & JWT Auth:** Secure JWT token storage, automatic refresh, offline response caching.

### 🔧 Mobile App Execution Roadmap
```
mobile/apsdreamhome_app_v2/
├── lib/
│   ├── core/               → API Client, Theme, Constants, Utils
│   ├── data/               → Repositories, Models, Data Sources
│   ├── domain/             → Business Entities & Use Cases
│   └── presentation/       → 147 Flutter Pages (Pages, Widgets, Controllers)
android/                    → Android Native Wrapper & Build Scripts
public/downloads/           → Output location for compiled APK (`apsdreamhome.apk`)
```

#### Mobile App Tasks:
1. **State Management Audit:** Ensure smooth screen transitions and responsive state updates across all 147 Flutter pages.
2. **APK Compilation & Distribution:** Maintain automated debug APK build pipeline (`cd mobile/apsdreamhome_app_v2 && flutter build apk --debug`) and copy to `public/downloads/apsdreamhome.apk`.

---

## 🗄️ 4. Database & Storage Plan (MySQL 8.0 + Redis)

### 📌 Core Principles
- **Relational Integrity:** ~584 base tables with 263 Foreign Key constraints.
- **Dual Network MLM Tree Architecture:**
  - `network_tree`: Rich binary/matrix tree structure optimized for visual tree rendering.
  - `mlm_network_tree`: Simple parent-chain table optimized for instant recursive commission calculations.
- **Tenant Scoping:** Every business table includes `tenant_id` indexed column.

#### Database Tasks:
1. **Query Performance Indexing:** Add composite indexes on high-volume reporting queries (`tenant_id + created_at`, `tenant_id + status`).
2. **Data Consistency Audit:** Run verification scripts to ensure 100% sync between `network_tree` and `mlm_network_tree`.

---

## 🤖 5. AI, Automation & Telephony Plan

### 📌 Features
- **Gemini AI Integration:** Intelligent lead qualification, automated chat assistant, document OCR extraction.
- **Telephony & SIM Auto-Dialer:** Automated outbound calling queue for telecallers via Twilio / SIM integration.
- **WhatsApp Drip Engine:** Triggered WhatsApp message notifications for payment receipts, booking confirmations, and follow-ups.

---

## 🧪 6. QA, Testing & DevOps Plan

### 📌 Quality Standards
- **Automated E2E Testing:** Master Playwright test suite (`testing/visual_tests/E2E_MASTER_TEST.mjs`) requiring 100% pass rate (153/153 currently passing).
- **PHP Syntax Validation:** Execute `php -l` on all modified PHP files prior to committing.
- **Continuous Integration:** Re-run E2E test suite after every feature batch to prevent regressions.

---

## 📅 7. Execution Timeline & Task Sprints

```
┌───────────────────────────────────────────────────────────────────────────────────┐
│                              DEVELOPMENT SPRINT PHASES                             │
│                                                                                   │
│  Sprint 1 (Backend & Security Hardening):                                         │
│  • Controller Decoupling into Action Services                                     │
│  • API Request Validation Middleware                                              │
│                                                                                   │
│  Sprint 2 (Frontend & Asset Standardization):                                     │
│  • Inline JS extraction to public/assets/js/                                      │
│  • Interactive Plot Map SVG enhancements                                          │
│                                                                                   │
│  Sprint 3 (Mobile Flutter App Optimization):                                      │
│  • Flutter State Audit across 147 pages                                           │
│  • Recompile & publish APK v1.3.0                                                 │
│                                                                                   │
│  Sprint 4 (Database & Query Performance):                                         │
│  • Heavy JOIN Query Optimization & Indexing                                       │
│  • MLM Dual-Tree Consistency Verification                                         │
│                                                                                   │
│  Sprint 5 (QA & E2E Expansion):                                                   │
│  • Expand Playwright E2E test suite from 153 to 200+ assertions                   │
└───────────────────────────────────────────────────────────────────────────────────┘
```
