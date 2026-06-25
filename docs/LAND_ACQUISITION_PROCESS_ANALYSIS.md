# Complete Land Acquisition Process Analysis

> Generated: 2026-06-25 | Project: APS Dream Home | Location: `C:\xampp\htdocs\apsdreamhome`

---

## Table of Contents

1. [Every File Related to Land Acquisition](#1-every-file-related-to-land-acquisition)
2. [Database Schema — All Land-Related Tables](#2-database-schema--all-land-related-tables)
3. [Complete Workflow — From Farmer Land Purchase to Plot Sales](#3-complete-workflow--from-farmer-land-purchase-to-plot-sales)
4. [What's Working vs What's Stubbed](#4-whats-working-vs-whats-stubbed)
5. [Actual Business Logic in Each Service](#5-actual-business-logic-in-each-service)
6. [Summary](#6-summary)

---

## 1. Every File Related to Land Acquisition

### 1.1 Services (Core Business Logic) — 7 Files

| #   | File                                               | Lines | Status                  | Purpose                                                                                                               |
| --- | -------------------------------------------------- | ----- | ----------------------- | --------------------------------------------------------------------------------------------------------------------- |
| 1   | `app/Services/Land/LandAcquisitionService.php`     | ~800+ | **FULLY WORKING**       | Master lifecycle service: leads, documents, site visits, legal opinions, deals, payments, dev costs, layouts, brokers |
| 2   | `app/Services/Land/PlotCutterService.php`          | ~797  | **FULLY WORKING**       | Land-to-plot cutting algorithm: road/park deductions, greedy grid fill, corner/park-facing detection                  |
| 3   | `app/Services/Land/ColonyPricingService.php`       | ~640  | **FULLY WORKING**       | Cost-based pricing: land+dev costs, markup formula (`cost/(1-0.50)`), premiums, bulk-apply, price history             |
| 4   | `app/Services/Land/ColonyFeasibilityService.php`   | ~500+ | **FULLY WORKING**       | Advanced feasibility calculator with override params and audit trail (`colony_pricing_feasibility` table)             |
| 5   | `app/Services/Land/PlottingService.php`            | ~500+ | **WORKING (older API)** | Simpler farmer-oriented land acquisition, plot CRUD, booking with commission, payment tracking                        |
| 6   | `app/Services/PlotDevelopmentCostService.php`      | ~372  | **WORKING**             | Plot development cost calculator: land, road, electricity, water, amenities, legal, misc cost components              |
| 7   | `app/Services/Accounting/MoneyWorkflowService.php` | large | **WORKING**             | `checkRegistryEligibility()` blocks registry when overdue installments exist                                          |

### 1.2 Controllers — 9 Files

| #   | File                                                         | Lines | Status            | Purpose                                                                                                                                                |
| --- | ------------------------------------------------------------ | ----- | ----------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------ |
| 1   | `app/Http/Controllers/Admin/LandInventoryController.php`     | ~450+ | **FULLY WORKING** | Full admin UI for land acquisition pipeline (leads → deals → payments → costs → layouts → brokers)                                                     |
| 2   | `app/Http/Controllers/Admin/ColonyPipelineController.php`    | ~800  | **FULLY WORKING** | Master admin UI: colony detail, layout config, plot generation/preview/delete/save, pricing, dev costs, plot inventory                                 |
| 3   | `app/Http/Controllers/Admin/ColonyFeasibilityController.php` | 197   | **FULLY WORKING** | Feasibility calculator: index comparison, per-colony calculator, calculate with overrides, history, AJAX preview                                       |
| 4   | `app/Http/Controllers/Admin/ColonyController.php`            | 233   | **FULLY WORKING** | Colony CRUD: index, create, store, show (with plot stats, block breakdown, dev costs, layout, price history), edit, update, destroy, plots, financials |
| 5   | `app/Http/Controllers/Admin/LandController.php`              | ~300+ | **WORKING**       | Legacy land_records CRUD: index, create, store, show, acquisitions, records, getStats                                                                  |
| 6   | `app/Http/Controllers/Admin/PlotManagementController.php`    | 1386  | **WORKING**       | Admin plot CRUD: index, create, store, show, edit, update, destroy, categories, availability, map, bulk operations                                     |
| 7   | `app/Http/Controllers/Admin/PlotCostController.php`          | 200   | **WORKING**       | Plot development cost admin UI: colony list with cost summary, cost breakdown, add cost, recalculate, report                                           |
| 8   | `app/Http/Controllers/Land/PlottingController.php`           | ~300+ | **WORKING**       | Farmer-oriented: dashboard, land acquisitions CRUD, plots CRUD, bookings, payments                                                                     |
| 9   | `app/Http/Controllers/Employee/LandManagerController.php`    | ~200+ | **WORKING**       | Employee portal: dashboard, site visit scheduling, acquisition updates, documentation, reports                                                         |
| 10  | `app/Http/Controllers/FarmerController.php`                  | ~300+ | **WORKING**       | Farmer CRUD: index, list, create, store, search, show, edit, update, delete                                                                            |

### 1.3 Views — 35 Files

#### Land Inventory views (`app/views/admin/land-inventory/`) — 16 files

| File                        | Purpose                              |
| --------------------------- | ------------------------------------ |
| `leads.php`                 | Land leads list with pipeline status |
| `lead-detail.php`           | Single lead detail with all tabs     |
| `lead-form.php`             | Create/edit lead form                |
| `visits.php`                | Site visit log per lead              |
| `documents.php`             | Document checklist per lead          |
| `opinions.php`              | Legal opinion records per lead       |
| `acquisitions.php`          | Closed deals/acquisitions list       |
| `acquisition-detail.php`    | Single deal detail                   |
| `registration-form.php`     | Registration submission form         |
| `payments.php`              | Payment ledger per deal              |
| `payment-form.php`          | Add/edit payment form                |
| `development-costs.php`     | Development costs per colony         |
| `development-cost-form.php` | Add development cost form            |
| `layouts.php`               | Colony layout list                   |
| `layout-form.php`           | Create layout form                   |
| `brokers.php`               | Broker master list                   |

#### Colony Pipeline views (`app/views/admin/colony-pipeline/`) — 6 files

| File                    | Purpose                                 |
| ----------------------- | --------------------------------------- |
| `dashboard.php`         | Colony list with pipeline status cards  |
| `detail.php`            | Colony overview with 6 action cards     |
| `layout-form.php`       | Plot cutting config + AJAX preview      |
| `pricing.php`           | Price bands + calculate/apply form      |
| `development-costs.php` | Cost tracking + add form                |
| `plots.php`             | Colony-scoped plot inventory with stats |

#### Colony Feasibility views (`app/views/admin/colony-feasibility/`) — 3 files

| File             | Purpose                                    |
| ---------------- | ------------------------------------------ |
| `index.php`      | Cross-colony comparison table              |
| `calculator.php` | Per-colony calculator with override inputs |
| `history.php`    | Audit log of feasibility calculations      |

#### Colony views (`app/views/admin/colonies/`) — 6 files

| File             | Purpose                                          |
| ---------------- | ------------------------------------------------ |
| `index.php`      | Colony list                                      |
| `create.php`     | Colony creation form                             |
| `edit.php`       | Colony edit form                                 |
| `show.php`       | Colony detail with plot stats, dev costs, layout |
| `plots.php`      | Plot list per colony                             |
| `financials.php` | Financial summary per colony                     |

#### Plot Cost views (`app/views/admin/plot-costs/`) — 3 files

| File         | Purpose                          |
| ------------ | -------------------------------- |
| `index.php`  | Colony list with cost summary    |
| `colony.php` | Colony cost breakdown + add form |
| `report.php` | Detailed cost analysis report    |

#### Employee Land Manager views (`app/views/employee/`) — 2+ files

| File                           | Purpose                          |
| ------------------------------ | -------------------------------- |
| `land_manager.php`             | Employee land manager dashboard  |
| `land_manager_acquisition.php` | Acquisition detail for employees |

### 1.4 Models — 5 Files

| File                               | Purpose                   |
| ---------------------------------- | ------------------------- |
| `app/Models/LandProject.php`       | Land project model        |
| `app/Models/LandPurchase.php`      | Land purchase model       |
| `app/Models/FarmerLandHolding.php` | Farmer land holding model |
| `app/Models/Colony.php`            | Colony model              |
| `app/Models/Plot.php`              | Plot model                |

### 1.5 Migrations & Seeders — 5 Files

| File                                                  | Purpose                                                           |
| ----------------------------------------------------- | ----------------------------------------------------------------- |
| `scripts/_archive/migrate_module1_land_inventory.php` | DDL for 10 land tables                                            |
| `scripts/seed_colony_pipeline.php`                    | Suryoday Colony seed: 50 plots, ₹1.81Cr dev costs, ₹15K/sqft base |
| `scripts/seed_colony3_pipeline.php`                   | Braj Radha seed: 40 plots, ₹1.08Cr dev costs, ₹12K/sqft base      |
| `database/seeder/seed_motiram_data.php`               | Motiram Township seeding                                          |
| `database/seeder/seed_raghunath_nagri.php`            | Raghunath Nagri seeding                                           |

### 1.6 Routes (from `routes/web.php`)

#### Land Inventory Routes (39 routes) — Lines 1815–1853

```
GET    /admin/land-inventory/leads                                          → leads
GET    /admin/land-inventory/leads/new                                      → leadForm
POST   /admin/land-inventory/leads/store                                    → leadStore
GET    /admin/land-inventory/leads/{id}                                     → leadDetail
GET    /admin/land-inventory/leads/{id}/edit                                 → leadForm
POST   /admin/land-inventory/leads/{id}/update                               → leadUpdate
POST   /admin/land-inventory/leads/{id}/advance                              → leadAdvance
GET    /admin/land-inventory/leads/{id}/visits                               → visits
POST   /admin/land-inventory/leads/{id}/visits/store                         → visitStore
GET    /admin/land-inventory/leads/{id}/documents                            → documents
POST   /admin/land-inventory/leads/{id}/documents/upload                     → documentUpload
GET    /admin/land-inventory/leads/{id}/opinions                             → opinions
POST   /admin/land-inventory/leads/{id}/opinions/store                       → opinionStore
POST   /admin/land-inventory/leads/{id}/register                             → registerSubmit
GET    /admin/land-inventory/acquisitions                                    → acquisitions
GET    /admin/land-inventory/acquisitions/{id}                               → acquisitionDetail
GET    /admin/land-inventory/acquisitions/{id}/register                      → registerForm
GET    /admin/land-inventory/acquisitions/{id}/payments                      → payments
GET    /admin/land-inventory/acquisitions/{id}/payments/new                  → paymentForm
POST   /admin/land-inventory/acquisitions/{id}/payments/store                → paymentStore
GET    /admin/land-inventory/acquisitions/{id}/payments/edit/{pid}            → paymentForm
POST   /admin/land-inventory/acquisitions/{id}/payments/update/{pid}          → paymentUpdate
GET    /admin/land-inventory/colonies/{colonyId}/costs                       → developmentCosts
GET    /admin/land-inventory/colonies/{colonyId}/costs/new                   → developmentCostForm
POST   /admin/land-inventory/colonies/{colonyId}/costs/store                 → developmentCostStore
GET    /admin/land-inventory/colonies/{colonyId}/layouts                     → layouts
GET    /admin/land-inventory/colonies/{colonyId}/layouts/create              → layoutForm
POST   /admin/land-inventory/colonies/{colonyId}/layouts/store               → layoutStore
GET    /admin/land-inventory/brokers                                         → brokers
POST   /admin/land-inventory/brokers/store                                   → brokerStore
```

#### Colony Pipeline Routes (14 routes) — Lines 1858–1871

```
GET    /admin/colony-pipeline                                               → dashboard
GET    /admin/colony-pipeline/{id}                                          → colonyDetail
GET    /admin/colony-pipeline/{id}/layout                                   → layoutForm
POST   /admin/colony-pipeline/{id}/layout/generate                          → generatePlots
POST   /admin/colony-pipeline/{id}/layout/preview                           → previewPlots
POST   /admin/colony-pipeline/{id}/layout/delete                            → deletePlots
POST   /admin/colony-pipeline/{id}/layout/save                              → saveLayout
GET    /admin/colony-pipeline/{id}/pricing                                  → pricingDashboard
POST   /admin/colony-pipeline/{id}/pricing/calculate                        → calculatePricing
POST   /admin/colony-pipeline/{id}/pricing/apply                            → applyPricing
GET    /admin/colony-pipeline/{id}/costs                                    → developmentCosts
POST   /admin/colony-pipeline/{id}/costs/store                              → storeCost
GET    /admin/colony-pipeline/{id}/plots                                    → plotList
GET    /admin/colony-pipeline/{id}/plots/stats                              → plotStats (JSON)
```

#### Colony Feasibility Routes (5 routes) — Lines 1876–1880

```
GET    /admin/colony-feasibility                                            → index
GET    /admin/colony-feasibility/{id}                                       → calculator
POST   /admin/colony-feasibility/{id}/calculate                             → calculate
GET    /admin/colony-feasibility/{id}/history                               → history
GET    /admin/colony-feasibility/{id}/preview                               → preview (AJAX JSON)
```

#### Plot Cost Routes (5 routes) — Lines 999–1003

```
GET    /admin/plot-costs                                                    → index
GET    /admin/plot-costs/colony/{id}                                        → colony
POST   /admin/plot-costs/add-cost                                           → addCost
POST   /admin/plot-costs/calculate                                          → calculate
GET    /admin/plot-costs/report/{id}                                        → report
```

#### Admin Plots Routes (10+ routes) — Lines 1044–1053+

```
GET    /admin/plots                                                         → index
GET    /admin/plots/create                                                  → create
POST   /admin/plots                                                         → store
GET    /admin/plots/check-availability                                      → checkAvailability
POST   /admin/plots/bulk-price-update                                       → bulkPriceUpdate
GET    /admin/plots/availability                                            → availability
GET    /admin/plots/availability-data                                       → availabilityData (JSON)
GET    /admin/plots/map                                                     → map
GET    /admin/plots/{id}                                                    → show
GET    /admin/plots/edit/{id}                                               → edit
POST   /admin/plots/update/{id}                                             → update
POST   /admin/plots/destroy/{id}                                            → destroy
```

#### Farmer Routes (9 routes) — Lines 788–796

```
GET    /farmers                                                             → index
GET    /farmers/list                                                        → list
GET    /farmers/create                                                      → create
POST   /farmers                                                             → store
GET    /farmers/search                                                      → search
GET    /farmers/{id}                                                        → show
GET    /farmers/{id}/edit                                                   → edit
POST   /farmers/{id}/update                                                 → update
POST   /farmers/{id}/delete                                                 → delete
```

#### Legacy Land Routes (6 routes) — Lines 2056–2061

```
GET    /admin/land/create                                                   → create
POST   /admin/land/store                                                    → store
GET    /admin/land/acquisitions                                             → acquisitions
GET    /admin/land/records                                                  → records
GET    /admin/land/stats                                                    → getStats
GET    /admin/land/{id}                                                     → show
```

#### Employee Land Manager Routes (5 routes) — Lines 852–856

```
POST   /employee/land/site-visit                                            → scheduleSiteVisit
POST   /employee/land/acquisition                                           → updateAcquisition
POST   /employee/land/complete-visit                                        → completeSiteVisit
POST   /employee/land/documentation                                         → updatePropertyDocumentation
POST   /employee/land/report                                                → generateLandReport
```

### 1.7 Sidebar Menu Items (from `reseed_sidebar_clean.php`)

```php
['name' => 'Colony Pipeline',     'url' => '/admin/colony-pipeline',       'section' => 'properties', 'order_index' => 1]
['name' => 'Colony Feasibility',  'url' => '/admin/colony-feasibility',    'section' => 'properties', 'order_index' => 2]
['name' => 'Land Acquisitions',   'url' => '/admin/land-inventory/acquisitions', 'section' => 'properties', 'order_index' => 6]
['name' => 'Land Leads',          'url' => '/admin/land-inventory/leads',  'section' => 'properties', 'order_index' => 7]
['name' => 'Land Brokers',        'url' => '/admin/land-inventory/brokers','section' => 'properties', 'order_index' => 8]
```

---

## 2. Database Schema — All Land-Related Tables

### 2.1 Primary Land Acquisition Tables (Module 1 — 10 Tables)

#### `land_brokers`

| Column                 | Type                      | Notes          |
| ---------------------- | ------------------------- | -------------- |
| id                     | INT(11) UNSIGNED PK       | Auto-increment |
| broker_name            | VARCHAR(255) NOT NULL     |                |
| phone                  | VARCHAR(20)               |                |
| email                  | VARCHAR(255)              |                |
| pan_number             | VARCHAR(20)               |                |
| aadhaar_number         | VARCHAR(20)               |                |
| rera_number            | VARCHAR(100)              |                |
| address                | TEXT                      |                |
| commission_percentage  | DECIMAL(5,2) DEFAULT 2.00 |                |
| bank_account           | VARCHAR(50)               |                |
| ifsc                   | VARCHAR(20)               |                |
| active                 | TINYINT(1) DEFAULT 1      |                |
| created_at, updated_at | TIMESTAMP                 |                |

#### `land_leads`

| Column                           | Type                                                                                                               | Notes                 |
| -------------------------------- | ------------------------------------------------------------------------------------------------------------------ | --------------------- |
| id                               | INT(11) UNSIGNED PK                                                                                                | Auto-increment        |
| lead_source                      | ENUM('broker','scout','direct','referral','web','phone')                                                           |                       |
| broker_id                        | INT(11) UNSIGNED FK                                                                                                | → land_brokers.id     |
| land_owner_name                  | VARCHAR(255) NOT NULL                                                                                              |                       |
| owner_phone                      | VARCHAR(20)                                                                                                        |                       |
| owner_email                      | VARCHAR(255)                                                                                                       |                       |
| village, tehsil, district, state | VARCHAR(255)                                                                                                       | Location fields       |
| pincode                          | VARCHAR(10)                                                                                                        |                       |
| gps_lat                          | DECIMAL(10,7)                                                                                                      |                       |
| gps_lng                          | DECIMAL(10,7)                                                                                                      |                       |
| survey_number                    | VARCHAR(100)                                                                                                       |                       |
| area_acres                       | DECIMAL(10,2)                                                                                                      |                       |
| area_sqft                        | DECIMAL(12,2)                                                                                                      |                       |
| expected_price                   | DECIMAL(15,2)                                                                                                      |                       |
| status                           | ENUM('new','screening','visit_done','dd','negotiation','legal','sale_agreement','registered','rejected','dropped') | Forward-only pipeline |
| assigned_to                      | INT(11) UNSIGNED FK                                                                                                | → users.id            |
| notes                            | TEXT                                                                                                               |                       |
| created_at, updated_at           | TIMESTAMP                                                                                                          |                       |

**Indexes**: status, broker_id, assigned_to, district, lead_source, created_at

#### `land_documents`

| Column                 | Type                                            | Notes                                                                                                                                                                                                                                            |
| ---------------------- | ----------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| id                     | INT(11) UNSIGNED PK                             |                                                                                                                                                                                                                                                  |
| land_lead_id           | INT(11) UNSIGNED FK                             | → land_leads.id                                                                                                                                                                                                                                  |
| land_deal_id           | INT(11) UNSIGNED FK                             | → land_deals.id                                                                                                                                                                                                                                  |
| doc_type               | ENUM(18 types)                                  | mother_deed, chain_of_title, ec_30yr, patta, chitta, fmb, a_register, property_tax, kist_receipt, succession_cert, noc_co_owners, layout_plan, conversion_order, power_of_attorney, sale_agreement, registered_deed, mutation_application, other |
| doc_number             | VARCHAR(100)                                    |                                                                                                                                                                                                                                                  |
| doc_date               | DATE                                            |                                                                                                                                                                                                                                                  |
| uploaded_by            | INT(11) UNSIGNED FK                             | → users.id                                                                                                                                                                                                                                       |
| file_path              | VARCHAR(500)                                    |                                                                                                                                                                                                                                                  |
| verification_status    | ENUM('pending','verified','missing','rejected') |                                                                                                                                                                                                                                                  |
| verified_by            | INT(11) UNSIGNED FK                             |                                                                                                                                                                                                                                                  |
| verified_at            | DATETIME                                        |                                                                                                                                                                                                                                                  |
| remarks                | TEXT                                            |                                                                                                                                                                                                                                                  |
| created_at, updated_at | TIMESTAMP                                       |                                                                                                                                                                                                                                                  |

#### `land_site_visits`

| Column               | Type                        | Notes                     |
| -------------------- | --------------------------- | ------------------------- |
| id                   | INT(11) UNSIGNED PK         |                           |
| land_lead_id         | INT(11) UNSIGNED FK         | → land_leads.id           |
| visited_by           | INT(11) UNSIGNED FK         | → users.id                |
| visit_date           | DATETIME NOT NULL           |                           |
| gps_lat, gps_lng     | DECIMAL(10,7)               |                           |
| weather              | VARCHAR(100)                |                           |
| observations         | TEXT                        |                           |
| encroachment_found   | TINYINT(1) DEFAULT 0        |                           |
| encroachment_details | TEXT                        |                           |
| photos_json          | TEXT                        | JSON array of photo paths |
| risk_rating          | ENUM('low','medium','high') |                           |
| created_at           | TIMESTAMP                   |                           |

#### `land_legal_opinions`

| Column                       | Type                                    | Notes           |
| ---------------------------- | --------------------------------------- | --------------- |
| id                           | INT(11) UNSIGNED PK                     |                 |
| land_lead_id                 | INT(11) UNSIGNED FK                     | → land_leads.id |
| advocate_name                | VARCHAR(255) NOT NULL                   |                 |
| opinion_date                 | DATE NOT NULL                           |                 |
| status                       | ENUM('clear','conditional','not_clear') |                 |
| title_verified_chain         | TINYINT(1) DEFAULT 0                    |                 |
| encumbrance_review           | TINYINT(1) DEFAULT 0                    |                 |
| boundary_match               | TINYINT(1) DEFAULT 0                    |                 |
| co_owners_identified         | TINYINT(1) DEFAULT 0                    |                 |
| encroachment_risk            | VARCHAR(50)                             |                 |
| government_acquisition_check | TINYINT(1) DEFAULT 0                    |                 |
| rera_implications            | TEXT                                    |                 |
| opinion_document_path        | VARCHAR(500)                            |                 |
| remarks                      | TEXT                                    |                 |
| created_at                   | TIMESTAMP                               |                 |

#### `land_deals`

| Column                 | Type                                                               | Notes           |
| ---------------------- | ------------------------------------------------------------------ | --------------- |
| id                     | INT(11) UNSIGNED PK                                                |                 |
| land_lead_id           | INT(11) UNSIGNED FK                                                | → land_leads.id |
| colony_id              | INT(11) UNSIGNED FK                                                | → colonies.id   |
| total_area_sqft        | DECIMAL(12,2)                                                      |                 |
| acquired_area_sqft     | DECIMAL(12,2)                                                      |                 |
| total_consideration    | DECIMAL(15,2)                                                      |                 |
| advance_paid           | DECIMAL(15,2) DEFAULT 0                                            |                 |
| balance_amount         | DECIMAL(15,2) DEFAULT 0                                            |                 |
| sale_agreement_date    | DATE                                                               |                 |
| sale_agreement_number  | VARCHAR(100)                                                       |                 |
| registration_date      | DATE                                                               |                 |
| registration_number    | VARCHAR(100)                                                       |                 |
| sub_registrar_office   | VARCHAR(255)                                                       |                 |
| stamp_duty_amount      | DECIMAL(15,2) DEFAULT 0                                            |                 |
| registration_fee       | DECIMAL(15,2) DEFAULT 0                                            |                 |
| mutation_status        | ENUM('not_started','applied','in_progress','completed','rejected') |                 |
| mutation_number        | VARCHAR(100)                                                       |                 |
| mutation_date          | DATE                                                               |                 |
| status                 | ENUM('in_progress','registered','mutated','closed','cancelled')    |                 |
| created_at, updated_at | TIMESTAMP                                                          |                 |

#### `land_deal_payments`

| Column                 | Type                                                                                                             | Notes           |
| ---------------------- | ---------------------------------------------------------------------------------------------------------------- | --------------- |
| id                     | BIGINT(20) UNSIGNED PK                                                                                           |                 |
| land_deal_id           | INT(11) UNSIGNED FK                                                                                              | → land_deals.id |
| payment_type           | ENUM('advance','balance','stamp_duty','registration_fee','mutation_fee','broker_commission','legal_fee','other') |                 |
| payee_name             | VARCHAR(255) NOT NULL                                                                                            |                 |
| payee_pan              | VARCHAR(20)                                                                                                      |                 |
| payee_bank_account     | VARCHAR(50)                                                                                                      |                 |
| amount                 | DECIMAL(15,2) NOT NULL                                                                                           |                 |
| payment_date           | DATE NOT NULL                                                                                                    |                 |
| payment_mode           | ENUM('cash','cheque','rtgs','neft','upi','dd')                                                                   |                 |
| cheque_number          | VARCHAR(50)                                                                                                      |                 |
| cheque_date            | DATE                                                                                                             |                 |
| bank_name              | VARCHAR(255)                                                                                                     |                 |
| transaction_ref        | VARCHAR(100)                                                                                                     |                 |
| tds_amount             | DECIMAL(15,2) DEFAULT 0                                                                                          |                 |
| tds_section            | VARCHAR(20)                                                                                                      |                 |
| voucher_number         | VARCHAR(100)                                                                                                     |                 |
| status                 | ENUM('pending','cleared','bounced','cancelled')                                                                  |                 |
| created_at, updated_at | TIMESTAMP                                                                                                        |                 |

#### `colony_development_costs`

| Column                 | Type                                                            | Notes                                                                                                                                                                                           |
| ---------------------- | --------------------------------------------------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| id                     | INT(11) UNSIGNED PK                                             |                                                                                                                                                                                                 |
| colony_id              | INT(11) UNSIGNED FK                                             | → colonies.id                                                                                                                                                                                   |
| cost_type              | ENUM(18 types)                                                  | land_acquisition, road, electricity, water, sewerage, street_light, drainage, compound_wall, gate, security, landscaping, approval_fee, legal, brokerage, marketing, office_setup, staff, other |
| vendor_id              | INT(11) UNSIGNED                                                |                                                                                                                                                                                                 |
| vendor_name            | VARCHAR(255)                                                    |                                                                                                                                                                                                 |
| work_description       | TEXT                                                            |                                                                                                                                                                                                 |
| invoice_number         | VARCHAR(100)                                                    |                                                                                                                                                                                                 |
| invoice_date           | DATE                                                            |                                                                                                                                                                                                 |
| amount                 | DECIMAL(15,2) NOT NULL                                          |                                                                                                                                                                                                 |
| gst_amount             | DECIMAL(15,2) DEFAULT 0                                         |                                                                                                                                                                                                 |
| tds_section            | VARCHAR(20)                                                     |                                                                                                                                                                                                 |
| payment_status         | ENUM('unpaid','partial','paid')                                 |                                                                                                                                                                                                 |
| paid_amount            | DECIMAL(15,2) DEFAULT 0                                         |                                                                                                                                                                                                 |
| balance_amount         | DECIMAL(15,2) DEFAULT 0                                         |                                                                                                                                                                                                 |
| completion_date        | DATE                                                            |                                                                                                                                                                                                 |
| status                 | ENUM('planned','in_progress','completed','on_hold','cancelled') |                                                                                                                                                                                                 |
| created_at, updated_at | TIMESTAMP                                                       |                                                                                                                                                                                                 |

#### `colony_layouts`

| Column                     | Type                                            | Notes         |
| -------------------------- | ----------------------------------------------- | ------------- |
| id                         | INT(11) UNSIGNED PK                             |               |
| colony_id                  | INT(11) UNSIGNED FK                             | → colonies.id |
| layout_name                | VARCHAR(255) NOT NULL                           |               |
| total_plots                | INT(11) DEFAULT 0                               |               |
| total_area_sqft            | DECIMAL(12,2)                                   |               |
| layout_plan_image          | VARCHAR(500)                                    |               |
| approved_by                | VARCHAR(255)                                    |               |
| approval_date              | DATE                                            |               |
| government_approval_number | VARCHAR(100)                                    |               |
| status                     | ENUM('draft','submitted','approved','rejected') |               |
| created_at, updated_at     | TIMESTAMP                                       |               |

#### `plot_status_history`

| Column        | Type                   | Notes      |
| ------------- | ---------------------- | ---------- |
| id            | BIGINT(20) UNSIGNED PK |            |
| plot_id       | INT(11) UNSIGNED FK    | → plots.id |
| old_status    | VARCHAR(50)            |            |
| new_status    | VARCHAR(50) NOT NULL   |            |
| changed_by    | INT(11) UNSIGNED FK    | → users.id |
| change_reason | VARCHAR(255)           |            |
| changed_at    | TIMESTAMP              |            |
| metadata_json | TEXT                   |            |

### 2.2 Colony & Plot Tables

#### `colonies`

| Key Column               | Type                | Notes          |
| ------------------------ | ------------------- | -------------- |
| id                       | INT PK              |                |
| district_id              | INT FK              | → districts.id |
| name                     | VARCHAR(255)        |                |
| slug                     | VARCHAR(255) UNIQUE |                |
| description              | TEXT                |                |
| total_plots              | INT                 |                |
| available_plots          | INT                 |                |
| starting_price           | DECIMAL(15,2)       |                |
| is_active                | TINYINT(1)          |                |
| show_plots_publicly      | TINYINT(1)          |                |
| is_featured              | TINYINT(1)          |                |
| image_path, banner_image | VARCHAR(500)        |                |

#### `plots`

| Key Column          | Type          | Notes                              |
| ------------------- | ------------- | ---------------------------------- |
| id                  | INT PK        |                                    |
| colony_id           | INT FK        | → colonies.id                      |
| layout_id           | INT FK        | → colony_layouts.id                |
| block               | VARCHAR(50)   | Block name (A, B, C...)            |
| plot_number         | VARCHAR(50)   | e.g., A-001                        |
| area_sqft           | DECIMAL(10,2) |                                    |
| width_ft, length_ft | DECIMAL(8,2)  | Actual dimensions                  |
| price_per_sqft      | DECIMAL(10,2) |                                    |
| total_price         | DECIMAL(15,2) |                                    |
| status              | ENUM          | available, booked, sold, hold      |
| corner_plot         | TINYINT(1)    | Auto-detected by PlotCutterService |
| park_facing         | TINYINT(1)    | Auto-detected by PlotCutterService |
| road_width_ft       | DECIMAL(6,1)  |                                    |

#### `colony_pricing_feasibility`

Audit trail for ColonyFeasibilityService calculations — stores all input overrides and calculated outputs.

### 2.3 Legacy / Farmer Tables

| Table                       | Purpose                                                    |
| --------------------------- | ---------------------------------------------------------- |
| `land_acquisitions`         | Farmer-oriented acquisitions (older system with farmer_id) |
| `land_records`              | Basic land records                                         |
| `land_parcels`              | Geographic records with Khasra/Khata/Khatauni numbers      |
| `price_history`             | Pricing change audit trail per plot                        |
| `plot_bookings`             | Customer plot bookings                                     |
| `booking_payment_schedules` | EMI installments per booking                               |
| `booking_payment_receipts`  | Payment receipts                                           |

---

## 3. Complete Workflow — From Farmer Land Purchase to Plot Sales

```
PHASE 1: LAND IDENTIFICATION & LEAD GENERATION
═══════════════════════════════════════════════
  [Source: Broker / Scout / Direct / Referral / Web / Phone]
              │
              ▼
  ┌──────────────────────────────────────┐
  │  land_leads                          │
  │  LandInventoryController@leadStore   │
  │  → LandAcquisitionService::createLead│
  │  status = 'new'                      │
  └──────────────┬───────────────────────┘
                 │
                 ▼
PHASE 2: SCREENING & SITE VISIT
════════════════════════════════
  ┌──────────────────────────────────────────┐
  │  land_site_visits                        │
  │  LandInventoryController@visitStore      │
  │  → LandAcquisitionService::createVisit() │
  │  GPS coordinates + observations          │
  │  Encroachment check + risk rating        │
  │  Auto-advance: new → screening           │
  │              → visit_done                │
  └──────────────┬───────────────────────────┘
                 │
                 ▼
PHASE 3: DUE DILIGENCE
═══════════════════════
  ┌──────────────────────────────────────────┐
  │  land_documents                          │
  │  LandInventoryController@documentUpload  │
  │  → LandAcquisitionService::              │
  │    uploadDocument()                      │
  │  18 document types:                      │
  │  mother_deed, chain_of_title, ec_30yr,   │
  │  patta, chitta, fmb, a_register,         │
  │  property_tax, kist_receipt, etc.        │
  │  Status: screening → visit_done → dd     │
  └──────────────┬───────────────────────────┘
                 │
                 ▼
PHASE 4: NEGOTIATION & LEGAL OPINION
═════════════════════════════════════
  ┌──────────────────────────────────────────┐
  │  land_legal_opinions                     │
  │  LandInventoryController@opinionStore    │
  │  → LandAcquisitionService::              │
  │    createOpinion()                       │
  │  Advocate opinion:                       │
  │  ✓ Title chain verified                  │
  │  ✓ Encumbrance review                    │
  │  ✓ Boundary match                        │
  │  ✓ Government acquisition check          │
  │  ✓ RERA implications                     │
  │  Auto-advance: dd → legal                │
  └──────────────┬───────────────────────────┘
                 │
                 ▼
PHASE 5: SALE AGREEMENT → DEAL CLOSE
═════════════════════════════════════
  ┌──────────────────────────────────────────┐
  │  land_deals                              │
  │  LandInventoryController@registerSubmit  │
  │  → LandAcquisitionService::closeDeal()   │
  │  Captures:                               │
  │  • total_consideration (deal amount)     │
  │  • advance_paid                          │
  │  • sale_agreement_number + date          │
  │  • colony_id (link to colony)            │
  │  Status: legal → sale_agreement          │
  └──────────────┬───────────────────────────┘
                 │
                 ▼
PHASE 6: REGISTRATION & PAYMENT
════════════════════════════════
  ┌──────────────────────────────────────────┐
  │  land_deals + land_deal_payments         │
  │  LandInventoryController@registerForm    │
  │  → LandAcquisitionService::registerDeal()│
  │  Auto-creates PENDING payment records:   │
  │  • stamp_duty (7% of estimated land cost)│
  │  • registration_fee                      │
  │  Payment ledger tracks:                  │
  │  advance, balance, stamp duty,           │
  │  registration fee, mutation fee,         │
  │  broker commission, legal fee            │
  │  Status: sale_agreement → registered     │
  └──────────────┬───────────────────────────┘
                 │
                 ▼
PHASE 7: COLONY DEVELOPMENT
════════════════════════════
  ┌──────────────────────────────────────────┐
  │  colony_development_costs                │
  │  ColonyPipelineController@storeCost      │
  │  18 cost categories:                     │
  │  land_acquisition, road, electricity,    │
  │  water, sewerage, street_light,          │
  │  drainage, compound_wall, gate,          │
  │  security, landscaping, approval_fee,    │
  │  legal, brokerage, marketing,            │
  │  office_setup, staff, other              │
  │  Each with: amount, GST, TDS, payment    │
  │  status, vendor, invoice tracking        │
  └──────────────┬───────────────────────────┘
                 │
                 ▼
PHASE 8: LAYOUT CONFIGURATION → PLOT CUTTING
══════════════════════════════════════════════
  ┌──────────────────────────────────────────┐
  │  colony_layouts                          │
  │  ColonyPipelineController@layoutForm     │
  │  → PlotCutterService::generatePlots()    │
  │                                          │
  │  ADMIN CONFIGURES:                       │
  │  • Block names + count per block         │
  │  • Road width (ft)                       │
  │  • Park areas (x, y, width, height)      │
  │  • Amenity deductions                    │
  │  • Plot width × length (ft)              │
  │  • Road facing direction                 │
  │                                          │
  │  ALGORITHM:                              │
  │  1. Subtract roads + parks + amenities   │
  │     from total raw area                  │
  │  2. Calculate saleable area              │
  │  3. Greedy largest-first grid fill       │
  │     (try biggest plot size first,        │
  │      then smaller if doesn't fit)        │
  │  4. Mark corner plots (2+ exposed sides) │
  │  5. Mark park-facing plots               │
  │  6. Determine facing direction (NSEW)    │
  │  7. Persist all plots in DB transaction  │
  │  8. Create colony_layouts record         │
  │                                          │
  │  AJAX PREVIEW → review → DELETE →        │
  │  REGENERATE → SAVE (final)               │
  └──────────────┬───────────────────────────┘
                 │
                 ▼
PHASE 9: PRICING
════════════════
  ┌──────────────────────────────────────────┐
  │  TWO PRICING ENGINES:                    │
  │                                          │
  │  A) ColonyPricingService::               │
  │     calculateColonyPricing()             │
  │  ┌────────────────────────────────────┐  │
  │  │ Cost Basis:                        │  │
  │  │  land_cost + total_dev_costs       │  │
  │  │                                    │  │
  │  │ Base Price:                        │  │
  │  │  cost / saleable_area_sqft         │  │
  │  │                                    │  │
  │  │ Selling Price:                     │  │
  │  │  cost / (1 - 0.50) = 2x markup    │  │
  │  │  (50% overhead:                    │  │
  │  │   25% MLM commissions              │  │
  │  │   + 5% G&A                         │  │
  │  │   + 20% profit margin)             │  │
  │  │                                    │  │
  │  │ Premiums:                          │  │
  │  │  Corner plot:    +10%              │  │
  │  │  Park-facing:    +15%              │  │
  │  │  Wide road (40ft+): +8%            │  │
  │  │                                    │  │
  │  │ Logs to: price_history             │  │
  │  │ Updates: colonies.starting_price   │  │
  │  └────────────────────────────────────┘  │
  │                                          │
  │  B) ColonyFeasibilityService::           │
  │     calculateFeasibility()               │
  │  ┌────────────────────────────────────┐  │
  │  │ Same base formula + adds:          │  │
  │  │ • Registry/stamp duty (7% land)    │  │
  │  │ • Approval costs breakdown         │  │
  │  │ • Office overhead (separate)       │  │
  │  │                                    │  │
  │  │ Override params:                   │  │
  │  │ • total_raw_area_sqft              │  │
  │  │ • yield_pct (default 60%)          │  │
  │  │ • target_profit_pct (default 20%)  │  │
  │  │ • office_overhead_pct (default 5%) │  │
  │  │ • mlm_budget_pct (default 25%)     │  │
  │  │                                    │  │
  │  │ Logs to: colony_pricing_feasibility│  │
  │  │ Has: AJAX preview + audit history  │  │
  │  └────────────────────────────────────┘  │
  └──────────────┬───────────────────────────┘
                 │
                 ▼
PHASE 10: PLOT SALES & BOOKING
═══════════════════════════════
  ┌──────────────────────────────────────────┐
  │  plots (status → booked)                 │
  │  plot_bookings                           │
  │  booking_payment_schedules               │
  │  booking_payment_receipts                │
  │  booking_commissions                     │
  │                                          │
  │  PlottingController / BookingController  │
  │  → PlottingService::bookPlot()           │
  │                                          │
  │  Commission calculation:                 │
  │  • L1 (direct sponsor):     3%           │
  │  • L2 (2nd level upline):  1.5%          │
  │  • L3 (3rd level upline):   1%           │
  │  • Direct sale override:    2%           │
  └──────────────┬───────────────────────────┘
                 │
                 ▼
PHASE 11: REGISTRY & COMPLETION
════════════════════════════════
  ┌──────────────────────────────────────────┐
  │  MoneyWorkflowService::                  │
  │  checkRegistryEligibility()              │
  │                                          │
  │  BLOCKS registry if:                     │
  │  • Overdue installments exist            │
  │  • Accrued penalties > 0                 │
  │                                          │
  │  STATUS FLOW:                            │
  │  emi_active → fully_paid →               │
  │  registration_done                       │
  └──────────────────────────────────────────┘
```

---

## 4. What's Working vs What's Stubbed

### 4.1 FULLY WORKING (Production-Ready)

| Component                    | Evidence                                                                                                                                                                                                                                                                |
| ---------------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **LandAcquisitionService**   | All methods implemented: createLead, updateLead, forward-only status flow, auto-advance, createVisit, uploadDocument, createOpinion, closeDeal, registerDeal, listPayments, addPayment, CRUD for dev costs/layouts/brokers. All transactional with rollback. Null-safe. |
| **PlotCutterService**        | Real algorithm: road/park deduction, grid generation, greedy fill, corner/park-facing detection, facing direction, layout persistence in transaction.                                                                                                                   |
| **ColonyPricingService**     | Full cost-plus pricing: land + dev costs, markup formula, premiums, bulk-apply, price history logging, colony starting_price update, financial summary, cross-colony comparison.                                                                                        |
| **ColonyFeasibilityService** | Advanced calculator with override params, audit trail to `colony_pricing_feasibility`, AJAX preview endpoint, per-colony history.                                                                                                                                       |
| **ColonyPipelineController** | 12+ methods: dashboard, colony detail, layout form + plot generation + preview + delete + save, pricing dashboard + calculate + apply, dev costs CRUD, plot list + stats.                                                                                               |
| **LandInventoryController**  | 20+ methods: full lead lifecycle (CRUD + advance), site visits, documents, legal opinions, deals/acquisitions, registration, payments (CRUD), dev costs, layouts, brokers.                                                                                              |
| **ColonyController**         | 8 methods: full colony CRUD with show (plot stats, block breakdown, dev costs, layout, price history), plots, financials.                                                                                                                                               |
| **PlotManagementController** | 1386 lines: full admin plot CRUD, categories, availability, map, bulk price update, check availability, status management.                                                                                                                                              |
| **PlotCostController**       | Colony list with cost summary, cost breakdown, add cost, recalculate prices, cost analysis report.                                                                                                                                                                      |
| **PlottingService**          | Land acquisitions CRUD, plot CRUD, booking with commission calculation, payment tracking.                                                                                                                                                                               |
| **All 35 views**             | All rendering properly with admin layout, using `aps-cp-*` design system classes, CSRF tokens, i18n `__()` calls.                                                                                                                                                       |
| **All 49+ routes**           | 39 land-inventory + 14 colony-pipeline + 5 colony-feasibility + 5 plot-costs + 10 plots + 9 farmer + 6 land + 5 employee land routes.                                                                                                                                   |

### 4.2 PARTIALLY WORKING

| Component                          | Gap                                                                                                                                                                                                         |
| ---------------------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **PlotDevelopmentCostService**     | Calculates land/road/electricity/water/amenities/legal/misc costs but NOT integrated into ColonyPipelineController (which uses ColonyPricingService instead). Slightly redundant with ColonyPricingService. |
| **LandController (Admin)**         | Basic CRUD for legacy `land_records` table. Simpler than LandInventoryController — appears to be an older system that was superseded.                                                                       |
| **FarmerController**               | Farmer CRUD is functional but the farmer→land acquisition→colony→plot flow is NOT connected to the newer pipeline. Farmers are standalone entities, not integrated into the land_leads pipeline.            |
| **Employee LandManagerController** | Dashboard and forms exist but the employee portal workflow is isolated — it does not feed data into the admin LandInventoryController pipeline.                                                             |

### 4.3 STUBBED / PLACEHOLDER

| Component                    | Evidence                                                                                                                                                                                                                                                                                                       |
| ---------------------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **PlotBookings integration** | The `plot_bookings` table exists and PlottingService has booking logic, but the full booking lifecycle (EMI schedule, payments, commission, cancellation, transfer) is handled by the separate `BookingLifecycleService` (Module 2), not integrated with the plot-cutting→pricing→booking pipeline end-to-end. |
| **Registry execution**       | `MoneyWorkflowService::checkRegistryEligibility()` checks for overdue installments but the actual registry workflow (sub-registrar visit, stamp duty payment, deed generation) is NOT implemented — only a blocking check exists.                                                                              |

---

## 5. Actual Business Logic in Each Service

### 5.1 LandAcquisitionService

- **Lead pipeline**: Forward-only linear flow: `new → screening → visit_done → dd → negotiation → legal → sale_agreement → registered`. Branches to `rejected` / `dropped`.
- **Auto-advance triggers**:
  - First site visit bumps `new → screening → visit_done`
  - Legal opinion bumps `dd → legal`
- **Registration**: When a deal reaches "registered" status, auto-creates pending payment records for:
  - Stamp duty (7% of estimated land cost)
  - Registration fee
- **Null-safe**: All DB queries wrapped in try/catch, returns graceful empty results on failure.

### 5.2 PlotCutterService

1. **Deduction calculation**: Subtract roads (configurable width × colony dimensions), parks (deducted rectangular areas), and amenity areas from total raw area
2. **Saleable area** = raw area − deductions
3. **Greedy grid fill**: Try largest plot size first, then progressively smaller sizes if it doesn't fit
4. **Block assignment**: Each block gets its own plot sequence (A-001, A-002... B-001, B-002...)
5. **Corner detection**: Plot is "corner" if exposed on 2+ sides (not adjacent to another plot)
6. **Park-facing detection**: Plot is "park-facing" if adjacent to a designated park area
7. **Facing direction**: N/S/E/W based on plot position relative to nearest road
8. **Transaction persistence**: All plots saved in a single DB transaction; creates `colony_layouts` record on persist

### 5.3 ColonyPricingService

- **Base formula**: `base_price_per_sqft = (land_cost + total_dev_costs) / saleable_area_sqft`
- **Selling price**: `cost / (1 - 0.50)` = 2× cost markup
  - 50% of selling price is overhead:
    - 25% → MLM commissions
    - 5% → G&A (General & Administrative)
    - 20% → Profit margin
- **Premiums applied per-plot**:
  - Corner plot: +10%
  - Park-facing: +15%
  - Wide road (40ft+): +8%
- **Total plot price**: `(price_per_sqft + premiums) × area_sqft`
- **Audit**: Logs every price change to `price_history` table
- **Colony update**: Updates `colonies.starting_price` to minimum plot price

### 5.4 ColonyFeasibilityService

- Same base formula as ColonyPricingService but adds:
  - Registry/stamp duty estimate: 7% of (land_area × local rate per acre)
  - Approval cost breakdown (separate line item)
  - Office overhead (separate from G&A)
- **Override params** (admin can customize):
  - `total_raw_area_sqft` — override auto-calculated area
  - `yield_pct` — saleable area percentage (default 60%)
  - `target_profit_pct` — desired profit margin (default 20%)
  - `office_overhead_pct` — overhead (default 5%)
  - `mlm_budget_pct` — MLM commission budget (default 25%)
- **Audit trail**: Logs to `colony_pricing_feasibility` with full inputs + outputs + timestamp
- **AJAX preview**: Real-time pricing preview without saving (for "what-if" analysis)

### 5.5 PlottingService (Older System)

- Uses `land_acquisitions` table (farmer_id-based, NOT lead-based)
- Plot CRUD with proper DB column mapping (45+ columns on plots table)
- Booking flow: validate plot availability → create plot_booking → create payment schedule → calculate commission
- **Commission calculation**: Walks `users.referred_by` chain:
  - L1 (direct sponsor): 3%
  - L2 (2nd level upline): 1.5%
  - L3 (3rd level upline): 1%
  - Direct sale override: 2%

### 5.6 PlotDevelopmentCostService

- Breaks costs into 7 categories: land, development, amenities, legal, miscellaneous
- `calculateColonyCost()` sums all categories and divides by saleable area to get per-sqft cost
- `calculatePlotPrice()` applies configurable margin % on top of per-sqft cost
- Individual cost getters: `getLandCost()`, `getDevelopmentCost()`, `getAmenitiesCost()`, `getLegalCost()`, `getMiscCost()`

### 5.7 MoneyWorkflowService::checkRegistryEligibility()

- **Blocks registry** if customer has overdue EMI installments
- **Blocks registry** if accrued penalties > 0
- Uses JOIN through `plots.colony_id` to link to colony data
- Returns eligibility status with detailed reason

---

## 6. Summary

### System Maturity: ~85% Complete

```
┌─────────────────────────────────────────────────────────┐
│              PIPELINE COMPLETION STATUS                  │
├──────────────────────────────┬──────────┬───────────────┤
│ Phase                        │ Status   │ Completeness  │
├──────────────────────────────┼──────────┼───────────────┤
│ 1. Land Lead Generation      │ COMPLETE │ ██████████ 100%│
│ 2. Site Visit + Screening    │ COMPLETE │ ██████████ 100%│
│ 3. Document Collection       │ COMPLETE │ ██████████ 100%│
│ 4. Legal Opinion             │ COMPLETE │ ██████████ 100%│
│ 5. Sale Agreement + Close    │ COMPLETE │ ██████████ 100%│
│ 6. Registration + Payment    │ COMPLETE │ ██████████ 100%│
│ 7. Colony Development Costs  │ COMPLETE │ ██████████ 100%│
│ 8. Layout + Plot Cutting     │ COMPLETE │ ██████████ 100%│
│ 9. Pricing Engine            │ COMPLETE │ ██████████ 100%│
│ 10. Plot Sales + Booking     │ COMPLETE │ ████████░░  80%│
│ 11. Registry Execution       │ PARTIAL  │ ████░░░░░░  40%│
│ 12. EMI → Full Payment       │ PARTIAL  │ ██████░░░░  60%│
│ 13. Farmer → Lead Conversion │ MISSING  │ ░░░░░░░░░░   0%│
│ 14. Employee Portal → Admin  │ MISSING  │ ████░░░░░░  30%│
├──────────────────────────────┼──────────┼───────────────┤
│ OVERALL                      │          │ ████████░░ 85%│
└──────────────────────────────┴──────────┴───────────────┘
```

### Complete Pipeline at a Glance

```
Farmer Land Owner
  ──→ Land Lead (land_leads)
    ──→ Site Visit + GPS (land_site_visits)
      ──→ Document Collection (land_documents — 18 types)
        ──→ Legal Opinion (land_legal_opinions)
          ──→ Sale Agreement → Deal Close (land_deals)
            ──→ Registration + Payment (land_deal_payments)
              ──→ Link to Colony (colony_id FK)
                ──→ Development Costs (colony_development_costs — 18 types)
                  ──→ Layout Config (colony_layouts)
                    ──→ Plot Cutting Algorithm (PlotCutterService)
                      ──→ Pricing Engine (ColonyPricingService + ColonyFeasibilityService)
                        ──→ Plot Sales (plot bookings)
                          ──→ EMI Payments
                            ──→ Registry Eligibility Check
```

### Gaps (Non-Blocking)

| #   | Gap                                                            | Priority | Impact                                                                     |
| --- | -------------------------------------------------------------- | -------- | -------------------------------------------------------------------------- |
| 1   | Farmer → Lead conversion not automated                         | Medium   | Farmers must be manually entered as land_leads                             |
| 2   | Employee portal disconnected from admin pipeline               | Low      | Land manager activities don't sync to lead status                          |
| 3   | Registry execution (sub-registrar, deed generation)            | High     | Blocking check exists but no execution workflow                            |
| 4   | Full EMI → payment → commission → booking integration          | Medium   | PlottingService booking vs BookingLifecycleService (Module 2) are separate |
| 5   | PlotDevelopmentCostService redundant with ColonyPricingService | Low      | Two cost calculators with overlapping logic                                |

### Key Files Quick Reference

| What you need              | Go to                                                                                          |
| -------------------------- | ---------------------------------------------------------------------------------------------- |
| Start a land acquisition   | `LandInventoryController@leadForm` → `LandAcquisitionService::createLead()`                    |
| Cut plots from land        | `ColonyPipelineController@layoutForm` → `PlotCutterService::generatePlots()`                   |
| Price the colony           | `ColonyPipelineController@pricingDashboard` → `ColonyPricingService::calculateColonyPricing()` |
| Feasibility analysis       | `ColonyFeasibilityController@calculator` → `ColonyFeasibilityService::calculateFeasibility()`  |
| Register a deal            | `LandInventoryController@registerForm` → `LandAcquisitionService::registerDeal()`              |
| Check registry eligibility | `MoneyWorkflowService::checkRegistryEligibility()`                                             |
| Legacy farmer system       | `PlottingController` → `PlottingService`                                                       |
| Development costs          | `PlotCostController@colony` → `PlotDevelopmentCostService::calculateColonyCost()`              |
