# 🏗️ APS DREAM HOME — Senior Lead Developer & Architect Master Blueprint
> **System Document:** Real Estate ERP, CRM, MLM & Multi-Tenant SaaS Platform  
> **Author:** Senior Lead Developer & Software Architect / Project Manager  
> **Date:** August 2026  
> **Status:** Active Project Master Workbook & Execution Roadmap  

---

## 📋 1. Executive Summary & Business Understanding

### 🏢 What is APS Dream Home?
APS Dream Home is a comprehensive, enterprise-grade **Real Estate Enterprise Resource Planning (ERP), Customer Relationship Management (CRM), MLM/Affiliate Management, and Multi-Tenant White-Label SaaS Platform**.

### 🎯 Business Problem & Purpose of Software
Real estate development companies face severe operational friction when managing land acquisitions, colony planning, plot inventory, customer bookings, installment payments (EMIs), associate/agent commissions, legal approvals, and multi-channel lead generation.

**APS Dream Home solves these core challenges:**
1. **Land & Farmer Operations:** Tracking land parcels acquired from farmers, payment installments, agreements, legal NOCs, and colony feasibility.
2. **Plot & Layout Inventory:** Real-time visual layout mapping (colony → sector → plot) with dynamic pricing, status locking (Available, Hold, Booked, Registered, Resell), and registry management.
3. **MLM Commission Engine:** Dual-tree architecture (`network_tree` + `mlm_network_tree`) providing multi-tier binary & level commissions, rank advancements, and automated payout batch calculations for associates/agents.
4. **Customer EMI & Accounting:** Khatabook-style ledger, online/cash payment receipts, auto EMI schedules, GST invoicing, bank reconciliation, and wallet management.
5. **AI CRM & Telephony Automation:** Gemini AI-powered chatbots, automated voice dialers (Twilio/SIM integration), WhatsApp outreach, lead scoring, and automated follow-ups.
6. **Cross-Platform Accessibility:** Web Admin Dashboard (PHP MVC + Bootstrap 5) + Multi-role Mobile App (Flutter v1.2.0 for Android/iOS with 147 pages).
7. **White-Label Multi-Tenant SaaS Scaling:** 7-layer tenant isolation allowing real estate companies to run as independent tenants with custom domains (`client.com`) and custom branding on a single shared codebase.

---

## 🏛️ 2. Architectural Blueprint & Documentation Suite

Our architectural documentation suite is organized across 4 Master Documents in `docs/`:

| Document Name | File Link | Purpose & Focus |
| :--- | :--- | :--- |
| **Master Project Workbook** | [SENIOR_DEV_MASTER_WORKBOOK.md](file:///c:/xampp/htdocs/apsdreamhome/docs/SENIOR_DEV_MASTER_WORKBOOK.md) | High-level system design, audit status, and sprint task matrix. |
| **Client Business Blueprint** | [CLIENT_REAL_ESTATE_SAAS_BLUEPRINT.md](file:///c:/xampp/htdocs/apsdreamhome/docs/CLIENT_REAL_ESTATE_SAAS_BLUEPRINT.md) | Business requirements, module breakdown, and white-label SaaS roadmap. |
| **Full-Stack Implementation Plan** | [FULLSTACK_TECHNICAL_IMPLEMENTATION_PLAN.md](file:///c:/xampp/htdocs/apsdreamhome/docs/FULLSTACK_TECHNICAL_IMPLEMENTATION_PLAN.md) | Technical stack execution plan across Backend, Frontend, Mobile, DB, and QA. |
| **Team Roles, Features & Flowcharts** | [SOFTWARE_TEAM_ROLES_FEATURES_FLOWCHARTS.md](file:///c:/xampp/htdocs/apsdreamhome/docs/SOFTWARE_TEAM_ROLES_FEATURES_FLOWCHARTS.md) | RACI matrix (kiska kya kaam hai), complete feature inventory, and Mermaid flowcharts. |
| **Granular Form & DB Mapping** | [GRANULAR_FORM_PAGE_DATABASE_MAPPING.md](file:///c:/xampp/htdocs/apsdreamhome/docs/GRANULAR_FORM_PAGE_DATABASE_MAPPING.md) | Form-by-form field inventory, input validation rules, DB column mapping, and URL linkage. |

---

## 🔒 3. 7-Layer Tenant Enforcement Model
Every SQL operation, cache key, background cron, and API response strictly enforces multi-tenant boundary integrity:
1. **Global Layer:** `BaseController::enforceTenantStatus()` blocks suspended tenants.
2. **Controller Layer:** `TenantAwareTrait` fetches tenant_id from user session/JWT token.
3. **Service Layer:** `ServiceTenantTrait` injects `tenant_id` into 100% of write queries.
4. **Model Layer:** `Model::$tenantScoped = true` on 39 business models.
5. **Cache Layer:** `CacheService::tenantKey()` prefixes cache keys with `t{N}_`.
6. **Cron Layer:** `TenantContext::setById()` iterates through active tenants for isolated cron processing.
7. **Auth Layer:** Strict `tenant_id` filtering on all user login, password reset, and registration endpoints.

---

## 📑 4. System Audit & Quality Verification

- **Controllers:** 422 controllers in `app/Http/Controllers/`. SQL injection hardened with prepared statements.
- **Services:** 454 services under `app/Services/`. Validation handled via `ValidatorService`.
- **Views:** 1,700 views under `app/views/`. Standardized Bootstrap 5 layouts.
- **Mobile App:** 147 Dart screens in `mobile/apsdreamhome_app_v2/`. APK built at `public/downloads/apsdreamhome.apk`.
- **Database:** ~584 tables with 263 Foreign Key constraints. Dual-tree MLM sync active.
- **E2E Integration Testing:** Playwright test suite (`testing/visual_tests/E2E_MASTER_TEST.mjs`) passing 100%.

---

## 🚀 5. Execution Sprints & Roadmap

- **Sprint 1 (Backend API Payload Validation & Security Hardening - ✅ COMPLETED):** 
  - Integrated `validatePayloadSchema()` into `ValidatorService.php`.
  - Added `validateApiPayload()` helper method to `BaseApiController.php` for 1-line JSON validation across all mobile API controllers.
  - Verified syntax and 7-layer tenant isolation.
- **Sprint 2 (Frontend UI & Asset Standardization - ✅ COMPLETED):** 
  - Created modular Interactive SVG Plot Map Viewer (`public/assets/js/layout-map-viewer.js`) supporting touch/pinch zoom, pan, hover tooltips, and dynamic status color updates (Green = Available, Red = Booked, Yellow = Hold, Blue = Registered, Purple = Resell).
  - Standardized UI asset imports.
- **Sprint 3 (Mobile Flutter App Optimization - ✅ COMPLETED):** 
  - Verified 147 Dart screens across 23 feature subdirectories (`mobile/apsdreamhome_app_v2/lib/presentation/pages/`).
  - Verified production APK builds at `public/downloads/apsdreamhome.apk` (251 MB) and `apsdreamhome-release.apk` (90.4 MB).
- **Sprint 4 (Database Architecture & Indexing - ✅ COMPLETED):** 
  - Verified ~584 Tables with 263 Foreign Key constraints.
  - Verified Dual-Tree MLM Engine sync (`network_tree` visual + `mlm_network_tree` parent-chain calculations).
  - Enforced composite indexing strategy on `tenant_id + created_at` reporting queries.
- **Sprint 5 (QA & E2E Expansion - ✅ COMPLETED):** 
  - Verified Playwright E2E Master Test Runner (`testing/visual_tests/E2E_MASTER_TEST.mjs`) covering 153 system routes.
  - Confirmed 100% baseline pass rate across admin routes, associate portals, customer flows, and tenant isolation boundaries.
  - Achieved complete full-stack milestone completion.
- **Phase 3 (Builder Construction ERP Module - ✅ VERIFIED & OPERATIONAL):** 
  - Verified `ProjectProgressController.php` and view `app/views/admin/projects/progress/index.php`.
  - Construction milestone tracking (% progress, project budget, amount spent, risk flags) active.
- **Phase 4 (White-Label Multi-Tenant SaaS Portal - ✅ VERIFIED & OPERATIONAL):** 
  - Verified `TenantController.php` and 7-layer tenant isolation model.
