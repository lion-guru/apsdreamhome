# 🏢 Real Estate ERP, MLM & Multi-Tenant White-Label SaaS — Master Product & Architecture Blueprint
> **Client Specification & Technical Architecture Document**  
> **Prepared By:** Senior Lead Software Developer & Chief Architect  
> **Target System:** Enterprise Real Estate ERP + Hybrid Matrix MLM Engine + Construction/Builder Suite + Multi-Tenant White-Label SaaS  

---

## 📌 1. Client Business Vision & Core Requirements

### 🎯 Business Model Analysis
The Client operates a multi-faceted Real Estate & Property Development enterprise with a vision to scale both internally and commercially:

1. **Current Operations:** Property Listings, Land Acquisition from Farmers, Colony Planning, Layout & Plotting Management.
2. **Future Expansion:** Builder & Construction ERP (Raw Houses, Farmhouses, Villas, Interior Design & Contracting).
3. **Compensation & Marketing:** Hybrid Matrix MLM Network Plan (Upgradeable Commission Rules, Binary Legs + Matrix Spillover, Rank Bonuses, Automated Payout Engine).
4. **Commercial SaaS Model:** White-Label Multi-Tenant Platform — The software can be used for internal operations AND sold/licensed to external Real Estate companies with custom branding, custom domains, and subscription packages.

---

## 🏛️ 2. Architectural Blueprint & Module Breakdown

```
┌──────────────────────────────────────────────────────────────────────────────────────────────────┐
│                                   WHITE-LABEL MULTI-TENANT SAAS                                  │
│  ┌────────────────────────┐  ┌────────────────────────┐  ┌────────────────────────────────────┐  │
│  │ Tenant 1 (Client Corp) │  │ Tenant 2 (Licensed Co) │  │ Tenant N (Commercial Client)       │  │
│  │ custom.domain1.com     │  │ custom.domain2.com     │  │ tenantN.apsdreamhome.com           │  │
│  └───────────┬────────────┘  └───────────┬────────────┘  └─────────────────┬──────────────────┘  │
└──────────────┼───────────────────────────┼─────────────────────────────────┼─────────────────────┘
               │                           │                                 │
               └───────────────────────────┴─────────────────────────────────┘
                                           │
                                           ▼
┌──────────────────────────────────────────────────────────────────────────────────────────────────┐
│                                      UNIFIED PLATFORM ENGINE                                     │
│  ┌──────────────────────┐  ┌──────────────────────┐  ┌──────────────────────┐  ┌──────────────┐ │
│  │ Plotting & Layouts   │  │ Builder & Interior   │  │ Hybrid Matrix MLM    │  │ CRM & AI     │ │
│  │ ERP                  │  │ Module (Future)      │  │ Commission Engine    │  │ Telephony    │ │
│  └──────────────────────┘  └──────────────────────┘  └──────────────────────┘  └──────────────┘ │
│  ┌──────────────────────┐  ┌──────────────────────┐  ┌──────────────────────┐  ┌──────────────┐ │
│  │ Land & Farmers       │  │ Accounting & EMI     │  │ HR & Payroll         │  │ Mobile App   │ │
│  │ Acquisition          │  │ Khatabook Ledger     │  │ Management           │  │ (Flutter)    │ │
│  └──────────────────────┘  └──────────────────────┘  └──────────────────────┘  └──────────────┘ │
└──────────────────────────────────────────┬───────────────────────────────────────────────────────┘
                                           │
                                           ▼
┌──────────────────────────────────────────────────────────────────────────────────────────────────┐
│                                   7-LAYER TENANT ISOLATION LAYER                                 │
│  1. Domain Routing  2. Controller Trait  3. Service Isolation  4. Model Scoping                   │
│  5. Cache Prefixing (`t{N}_`)  6. Background Cron Context  7. Auth Boundary Isolation            │
└──────────────────────────────────────────────────────────────────────────────────────────────────┘
```

---

## ⚙️ 3. Detailed Functional Modules

### 🗺️ Module 1: Colony, Plotting & Inventory Engine
- **Hierarchical Layout Mapping:** Colony → Sector / Phase → Block → Plot Number.
- **Interactive SVG Plot Map:** Color-coded layout viewer (Green = Available, Red = Booked, Yellow = Hold, Blue = Registered, Purple = Resell).
- **Plot Pricing & Costing Engine:** Corner plot PLC (Preferential Location Charge), wide-road premium, square-foot vs square-yard pricing.
- **Dynamic Lock Engine:** Prevents duplicate bookings; temporary holds release automatically after X hours if token payment is missed.

### 🏗️ Module 2: Builder, Raw House, Farmhouse & Interior Suite (Future Ready)
- **Construction Project Tracking:** Milestone-based construction tracking (Foundation → Structure → Roofing → Finishing → Possession).
- **Farmhouse & Villa Catalog:** Floor plans, 3D renderings, material customization selection.
- **Interior Design Portal:** Package selection (Basic, Premium, Luxury), vendor assignment, bill of quantities (BOQ), material estimation.
- **Contractor & Supplier ERP:** Material requests, vendor purchase orders, contractor bill processing.

### ──────── Hybrid Matrix MLM Commission Engine
- **Hybrid Plan Architecture:** Combines Matrix (e.g. 3x10 or 5x7 width/depth control) with Binary leg spillover and Unilevel direct sales bonus.
- **Dual Network Tree Synchronization:**
  - `network_tree`: Rich visual binary/matrix rendering for UI dashboards.
  - `mlm_network_tree`: Parent-chain ancestor lookup for instant commission calculations.
- **Dynamic Upgradeable Rules:** Ability to alter commission percentages, level overrides, and performance rank qualifications without code rewrites.
- **Automated Payout Engine:** Wallet system with TDS calculation, admin fee deduction, payout batch generation, and direct bank transfer export.

### 📱 Module 4: Enterprise CRM & AI Automation
- **Lead Pipeline:** Automatic capture from Meta/Facebook Ads, Google Ads, Portal Web Forms, and Call Center.
- **AI Voice & Telephony:** Automated outbound calling for lead verification, WhatsApp Drip campaigns, lead scoring.
- **Site Visit Engine:** Driver assignment, customer pickup scheduling, associate site visit logging with GPS location tagging.

### 💰 Module 5: Financial Accounting, EMI & Khatabook Ledger
- **Flexible EMI Scheduler:** Down payment + monthly/quarterly EMIs with custom interest rates and late fee penalties.
- **Khatabook Digital Ledger:** Instant SMS/WhatsApp receipts for cash, cheque, UPI, and net banking payments.
- **GST & Compliance:** Automated GST invoice generation, GSTR-1 export, legal registry agreement documents.

### 🚜 Module 6: Land Acquisition & Farmer Management
- **Land Parcel Registry:** Khasra/Khatauni tracking, total acreage, land rate negotiations.
- **Farmer Installments Ledger:** Milestone-based payments to land owners, legal NOC approvals, title search reports.

### 🌐 Module 7: White-Label Multi-Tenant SaaS Engine
- **Domain & Branding Customization:** Dynamic logo, favicon, color palette, custom domain support (`tenantname.com`).
- **Subscription & License Manager:** SaaS billing tier management (Basic, Pro, Enterprise), feature toggle matrix per tenant.
- **7-Layer Data Isolation:** Guarantees complete data privacy and security between competing real estate companies.

---

## 📈 4. Technical Stack & Scalability Guidelines

- **Backend:** Custom PHP 8.3 MVC Engine (Lightweight, <40ms response latency, 0 external framework bloat).
- **Database:** MySQL 8.0 with InnoDB engine, full Foreign Key constraints, composite indexing for multi-tenant queries.
- **Frontend Web:** Bootstrap 5, Vanilla JavaScript ES6+, HTML5 Canvas / SVG for plot layout rendering.
- **Mobile Application:** Flutter Multi-Platform (Android & iOS) with offline state cache and Push Notifications.
- **Testing Standard:** E2E Automated Integration Testing (Playwright / Node.js).

---

## 🚀 5. Roadmap & Implementation Phases

1. **Phase 1 (Core Foundation - Ready):** Multi-Tenant Engine, Plotting ERP, Booking & EMI Ledger, Hybrid Matrix MLM Engine, Web Admin & Flutter Mobile App.
2. **Phase 2 (CRM & AI Integration - Ready):** Gemini AI chatbot, Telephony auto-dialer, WhatsApp automation, Site visit tracking.
3. **Phase 3 (Builder & Interior Expansion - In Progress):** Construction milestone tracking, Material BOQ, Villa/Farmhouse catalog, Contractor billing.
4. **Phase 4 (SaaS Monetization - Ready):** White-label custom domain manager, tenant subscription billing portal, automated tenant onboarding wizard.
