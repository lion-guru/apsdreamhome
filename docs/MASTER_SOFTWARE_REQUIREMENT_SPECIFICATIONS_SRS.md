# 📜 Master Software Requirement Specifications (SRS) — Comprehensive Unified Document
> **Document Type:** Complete Unified Enterprise SRS (Parts 1 to 9 Merged)  
> **Platform:** APS Dream Home (Real Estate ERP, CRM, MLM & Multi-Tenant White-Label SaaS)  
> **Prepared By:** Senior Lead Software Developer & Chief Architect  

---

## 📑 TABLE OF CONTENTS
1. Company Analysis & Business Scope
2. Functional & Non-Functional Requirements
3. System Architecture & 7-Layer Tenant Model
4. Database Schema & Tables Specification (~584 Tables)
5. System Modules & Workflows
6. REST API & Mobile Endpoints Specifications
7. Automated Testing Strategy (Playwright E2E)
8. Production Deployment & Infrastructure
9. Project Management & Release Roadmap

---

## 🏢 1. Company Analysis & Business Scope

APS Dream Home is an enterprise-grade **Real Estate ERP, CRM, MLM/Affiliate Management, and Multi-Tenant SaaS Platform**.

### Core Business Objectives:
- **Land Acquisition:** Farmer payments, Khasra/Khatauni tracking, Title Search Reports, legal NOCs.
- **Plotting & Layouts:** Colony → Sector → Block → Plot hierarchy with live SVG color-coded layout map.
- **MLM Network:** Binary + Matrix Dual Tree (`network_tree` + `mlm_network_tree`) providing multi-tier commissions.
- **Customer EMI & Accounting:** Khatabook digital ledger, instant WhatsApp receipts, GST invoicing, bank reconciliation.
- **White-Label SaaS:** 7-layer tenant isolation allowing third-party real estate companies to use the platform on custom domains (`client.com`) with custom branding.

---

## 📋 2. Functional & Non-Functional Requirements

### Functional Requirements (FR):
- **FR-1 (Property Management):** Plot status locking (Available, Hold, Booked, Registered, Resell).
- **FR-2 (MLM Engine):** Binary leg spillover + Matrix level commissions + Performance rank advancements.
- **FR-3 (CRM & AI):** Omnichannel lead capture, Gemini AI lead scoring (0-100), AI voice auto-dialer.
- **FR-4 (Financials):** 5% Commission TDS (Section 194-H) + Form 16A generation, 1% Property TDS (Section 194-IA), GST E-Invoicing.

### Non-Functional Requirements (NFR):
- **NFR-1 (Performance):** Page load response latency under 40ms.
- **NFR-2 (Security):** 100% Prepared Statements (Zero SQL Injection), CSRF protection, JWT stateless auth.
- **NFR-3 (Availability):** 99.9% Uptime SLA with Redis caching (`t{N}_` key prefixing).

---

## 🏛️ 3. System Architecture & 7-Layer Tenant Model

```
┌─────────────────────────────────────────────────────────────────────────────────────────┐
│                                7-LAYER TENANT ISOLATION MODEL                           │
│                                                                                         │
│  1. Global Layer     ──► BaseController::enforceTenantStatus() blocks suspended tenants │
│  2. Controller Layer ──► TenantAwareTrait fetches tenant_id from session/JWT            │
│  3. Service Layer    ──► ServiceTenantTrait injects tenant_id on ALL SQL write queries  │
│  4. Model Layer      ──► Model::$tenantScoped = true on 39 business models              │
│  5. Cache Layer      ──► CacheService::tenantKey() prefixes cache keys with `t{N}_`     │
│  6. Cron Layer       ──► TenantContext::setById() loops tenants in isolated crons       │
│  7. Auth Layer       ──► Strict tenant_id filtering on login/register/password-resets  │
└─────────────────────────────────────────────────────────────────────────────────────────┘
```

---

## 🗄️ 4. Database Schema & Tables Specification (~584 Tables)

### Key Entity Relationships:
- `users` (1:1) ➔ `associates` (1:N) ➔ `commissions`
- `colonies` (1:N) ➔ `sectors` (1:N) ➔ `plots` (1:1) ➔ `bookings` (1:N) ➔ `installments`
- `farmers` (1:N) ➔ `land_parcels`
- `users` (1:1) ➔ `network_tree` (Visual Binary) & `mlm_network_tree` (Calculation Chain)

---

## ⚙️ 5. System Modules & Workflows

1. **Admin Control Panel:** Multi-tenant dashboard, user roles, permission management.
2. **Land Acquisition:** Farmer milestone payments, NOC pipeline.
3. **Plotting ERP:** Interactive SVG map, PLC charges, dynamic locking.
4. **Sales & EMI Khatabook:** Booking wizard, installment scheduler, WhatsApp receipts.
5. **MLM Commission Engine:** Dual-tree payouts, TDS deduction, bank CMS file export.
6. **CRM Telephony & AI:** Gemini lead scoring, auto-dialer queue, WhatsApp drip campaigns.

---

## 🔌 6. REST API & Mobile Endpoints Specifications

- **Auth Endpoints:** `POST /api/v1/auth/login`, `POST /api/v1/auth/register`
- **Lead Endpoints:** `GET /api/v1/leads`, `POST /api/v1/leads/create`
- **Plot Endpoints:** `GET /api/v1/plots`, `GET /api/v1/plots/layout/{colony_id}`
- **MLM Endpoints:** `GET /api/v1/mlm/tree`, `GET /api/v1/mlm/commissions`

---

## 🧪 7. Automated Testing Strategy (Playwright E2E)

- **Test Suite:** `testing/visual_tests/E2E_MASTER_TEST.mjs`
- **Baseline:** **153/153 E2E Integration Tests Passing**.
- **Coverage:** Public pages, Admin routes, Associate login, Plot booking, EMI receipts, Role permissions.

---

## 🚀 8. Production Deployment & Infrastructure

- **Web Server:** Apache / Nginx with PHP 8.3 FPM.
- **Database Server:** MySQL 8.0 (Port 3307), InnoDB Engine, Foreign Key Constraints.
- **Caching:** Redis / File Cache.
- **Mobile Build:** Flutter v1.2.0 (147 screens) APK at `public/downloads/apsdreamhome.apk`.

---

## 📅 9. Project Management & Release Roadmap

- **Phase 1 (Completed):** Core MVC, Plotting ERP, MLM Dual Tree, Multi-Tenant Security, Playwright E2E Tests.
- **Phase 2 (Completed):** CRM AI Telephony, WhatsApp Drip, GST/TDS 194-H Tax Engine, Flutter Mobile App v1.2.0.
- **Phase 3 (Active):** Construction & Builder Module, White-Label SaaS Self-Service Onboarding.
