# 📐 Comprehensive Granular Form, Page & Database Mapping Blueprint
> **Document Type:** End-to-End System Specifications, URL Routes, Form Field Inventory & DB Schema Mapping  
> **Platform:** APS Dream Home (Real Estate ERP, CRM, MLM & Multi-Tenant White-Label SaaS)  
> **Prepared By:** Senior Lead Software Developer & Chief Architect  

---

## 📌 1. Structural Architecture Overview

System me Har Workflow (Process) 6 Layers me connected hota hai:

```
┌─────────────────────────────────────────────────────────────────────────────────────────┐
│                                 GRANULAR WORKFLOW LAYER                                 │
│                                                                                         │
│  1. User Interface Page    ──► URL Route & View Template (.php / Flutter Screen)        │
│  2. Form Specification     ──► Form Name, Input Fields, Input Types & Validations       │
│  3. Controller Action      ──► Controller Method (e.g. BookingController::store)        │
│  4. Service Logic          ──► Domain Service + 7-Layer Tenant Scoping                    │
│  5. Database Persistence   ──► Target Database Table & Columns Mapping                  │
│  6. Flow Linkage           ──► Post-Submit Action, Status Locks & Next Page Redirect    │
└─────────────────────────────────────────────────────────────────────────────────────────┘
```

---

## 📑 2. Granular Module-by-Module Form & Database Mapping

### 🚜 WORKFLOW 1: Farmer & Land Acquisition Management

#### 📍 Page Details:
- **URL Route:** `/admin/farmers/create`
- **View File:** [create.php](file:///c:/xampp/htdocs/apsdreamhome/app/views/admin/farmers/create.php)
- **Controller:** `FarmerAdminController::store()`
- **Service:** `FarmerService::registerFarmerWithLand()`

#### 📝 Form Specification (`farmer_registration_form`):
| Form Field Label | Input Name | Input Type | Validation Rules | Target DB Table & Column |
| :--- | :--- | :--- | :--- | :--- |
| Farmer Full Name | `farmer_name` | Text | Required, Max 150 | `farmers.name` |
| Phone Number | `phone` | Tel / Digits | Required, 10 Digits | `farmers.phone` |
| Aadhaar Card No | `aadhaar_no` | Text | Required, 12 Digits | `farmers.aadhaar_no` |
| PAN Card No | `pan_no` | Text | Optional, 10 Chars | `farmers.pan_no` |
| Khasra / Khatauni No | `khasra_no` | Text | Required, Max 50 | `land_parcels.khasra_no` |
| Total Acreage (Acres) | `total_acreage` | Decimal | Required, Numeric | `land_parcels.acreage` |
| Agreed Rate (Per Acre) | `agreed_rate` | Currency | Required, Numeric | `land_parcels.agreed_rate` |
| Bank Account Number | `bank_account_no`| Text | Required | `farmers.bank_account_no` |
| Bank IFSC Code | `ifsc_code` | Text | Required, 11 Chars | `farmers.ifsc_code` |

#### 🔗 Workflow Linkage & Data Flow:
```
[Fill Farmer Form] ──► Controller validates inputs & injects tenant_id
                        ──► Inserts row into `farmers` table
                        ──► Inserts row into `land_parcels` (Linked via farmer_id)
                        ──► Redirects to `/admin/farmers/show/{farmer_id}`
                        ──► Generates Farmer Milestone Payment Ledger & Legal Agreement
```

---

### 🗺️ WORKFLOW 2: Colony & Plot Inventory Creation Wizard

#### 📍 Page Details:
- **URL Route:** `/admin/plots/create`
- **View File:** [create.php](file:///c:/xampp/htdocs/apsdreamhome/app/views/admin/plots/create.php)
- **Controller:** `PlotManagementController::store()`
- **Service:** `PlotService::createPlot()`

#### 📝 Form Specification (`plot_creation_form`):
| Form Field Label | Input Name | Input Type | Validation Rules | Target DB Table & Column |
| :--- | :--- | :--- | :--- | :--- |
| Select Colony | `colony_id` | Dropdown | Required, Integer | `plots.colony_id` |
| Select Sector/Block | `sector_id` | Dropdown | Required, Integer | `plots.sector_id` |
| Plot Number | `plot_number` | Text | Required, Max 20 | `plots.plot_number` |
| Plot Area (Sq.Ft.) | `area_sqft` | Numeric | Required, > 0 | `plots.area_sqft` |
| Base Rate (Per Sq.Ft.)| `base_rate` | Currency | Required, > 0 | `plots.base_rate` |
| PLC Charge (Corner/Road)| `plc_charge` | Currency | Optional, >= 0 | `plots.plc_charge` |
| Facing Direction | `facing` | Select (East/West/North/South) | Required | `plots.facing_direction` |
| Plot Category | `plot_type` | Select (Residential/Commercial) | Required | `plots.plot_type` |

#### 🔗 Workflow Linkage & Data Flow:
```
[Submit Plot Form] ──► Controller calculates Total Plot Price = (area_sqft * base_rate) + plc_charge
                       ──► Inserts into `plots` table with status = 'Available'
                       ──► Updates Interactive SVG Plot Layout Coordinates
                       ──► Redirects to `/admin/colonies/view-map/{colony_id}`
```

---

### 💳 WORKFLOW 3: Customer Plot Booking & Token Payment Form

#### 📍 Page Details:
- **URL Route:** `/admin/bookings/create`
- **View File:** [create.php](file:///c:/xampp/htdocs/apsdreamhome/app/views/admin/bookings/create.php)
- **Controller:** `BookingController::store()`
- **Service:** `BookingService::processPlotBooking()`

#### 📝 Form Specification (`plot_booking_form`):
| Form Field Label | Input Name | Input Type | Validation Rules | Target DB Table & Column |
| :--- | :--- | :--- | :--- | :--- |
| Select Customer | `customer_id` | Search Dropdown | Required, Integer | `bookings.customer_id` |
| Select Associate/Agent | `associate_id` | Search Dropdown | Required, Integer | `bookings.associate_id` |
| Select Available Plot | `plot_id` | Dynamic Dropdown | Required, Available Status | `bookings.plot_id` |
| Booking Date | `booking_date` | Date Picker | Required, YYYY-MM-DD | `bookings.booking_date` |
| Total Plot Cost | `total_amount` | Currency Readonly | Auto-computed | `bookings.total_amount` |
| Down Payment Paid | `booking_amount` | Currency | Required, > 0 | `bookings.booking_amount` |
| EMI Tenure (Months) | `emi_months` | Select (12/24/36/48) | Required | `bookings.emi_tenure_months` |
| Payment Mode | `payment_mode` | Select (Cash/UPI/Cheque/Bank) | Required | `payment_receipts.payment_mode` |
| Transaction Ref / Cheque No | `transaction_ref` | Text | Required if Non-Cash | `payment_receipts.transaction_ref` |

#### 🔗 Workflow Linkage & Data Flow:
```
[Submit Booking Form] ──► Updates `plots.status` from 'Available' ➔ 'Booked' (LOCKED)
                         ──► Inserts row into `bookings` table (Status = 'Active')
                         ──► Inserts initial payment into `payment_receipts`
                         ──► Auto-generates rows in `installments` table for EMI Khatabook
                         ──► Triggers MLM Engine: Inserts commissions in `commissions` table
                         ──► Sends instant WhatsApp PDF Receipt to Customer & Associate
                         ──► Redirects to `/admin/bookings/receipt/{booking_id}`
```

---

### 🌳 WORKFLOW 4: Associate / Agent MLM Network Registration Form

#### 📍 Page Details:
- **URL Route:** `/associate/register`
- **View File:** [associate_register.php](file:///c:/xampp/htdocs/apsdreamhome/app/views/auth/associate_register.php)
- **Controller:** `AssociateAuthController::register()`
- **Service:** `MLMService::registerAssociateInTree()`

#### 📝 Form Specification (`associate_registration_form`):
| Form Field Label | Input Name | Input Type | Validation Rules | Target DB Table & Column |
| :--- | :--- | :--- | :--- | :--- |
| Sponsor Associate Code | `sponsor_code` | Text | Required, Valid Associate Code | `associates.sponsor_id` |
| Tree Placement Leg | `placement_leg` | Radio (Left/Right/Auto) | Required | `network_tree.position` |
| Full Name | `name` | Text | Required, Max 150 | `users.name` |
| Mobile Number | `phone` | Tel / Digits | Required, Unique, 10 Digits | `users.phone` |
| Email Address | `email` | Email | Required, Unique | `users.email` |
| Password | `password` | Password | Required, Min 8 Chars | `users.password` (Bcrypt Hashed) |
| PAN Card Number | `pan_card` | Text | Required, 10 Chars | `associates.pan_card` |
| Bank Account Number | `bank_account` | Text | Required | `associates.bank_account` |
| Bank IFSC Code | `bank_ifsc` | Text | Required, 11 Chars | `associates.bank_ifsc` |

#### 🔗 Workflow Linkage & Data Flow:
```
[Submit Registration Form] ──► Creates User record in `users` (role_id = Associate)
                            ──► Generates unique `associate_code` (e.g. APS10492)
                            ──► Inserts record in `associates` table
                            ──► Inserts binary position node in `network_tree`
                            ──► Inserts parent-chain path in `mlm_network_tree`
                            ──► Sends Welcome SMS & App Login Credentials
                            ──► Redirects to `/associate/dashboard` (Genealogy Tree View)
```

---

### 🤖 WORKFLOW 5: CRM Lead Capture & AI Telecalling Queue Form

#### 📍 Page Details:
- **URL Route:** `/admin/leads/create`
- **View File:** [create.php](file:///c:/xampp/htdocs/apsdreamhome/app/views/admin/leads/create.php)
- **Controller:** `LeadController::store()`
- **Service:** `LeadService::captureAndAssignLead()`

#### 📝 Form Specification (`lead_capture_form`):
| Form Field Label | Input Name | Input Type | Validation Rules | Target DB Table & Column |
| :--- | :--- | :--- | :--- | :--- |
| Lead Customer Name | `customer_name` | Text | Required | `leads.name` |
| Phone Number | `phone` | Tel / Digits | Required, 10 Digits | `leads.phone` |
| Lead Source | `source` | Select (Meta Ads/Google/Walkin)| Required | `leads.source` |
| Budget Range | `budget` | Select (10L-20L / 20L-50L / 50L+) | Required | `leads.budget` |
| Interested Colony | `colony_id` | Dropdown | Optional | `leads.colony_id` |
| Assign Telecaller | `telecaller_id` | Dropdown | Optional | `leads.telecaller_id` |

#### 🔗 Workflow Linkage & Data Flow:
```
[Submit Lead Form] ──► Inserts row into `leads` table (Status = 'New')
                    ──► Pushes lead into Gemini AI Telephonic Auto-Dialer Queue
                    ──► Assigns lead to Telecaller Dashboard `/admin/telecaller/dashboard`
                    ──► Triggers instant WhatsApp Welcome Brochure to Customer
```

---

### 🌐 WORKFLOW 6: White-Label Tenant Onboarding Form (SaaS Model)

#### 📍 Page Details:
- **URL Route:** `/godmode/tenants/create`
- **View File:** [create.php](file:///c:/xampp/htdocs/apsdreamhome/app/views/admin/tenants/create.php)
- **Controller:** `TenantController::store()`
- **Service:** `TenantService::onboardNewTenant()`

#### 📝 Form Specification (`tenant_onboarding_form`):
| Form Field Label | Input Name | Input Type | Validation Rules | Target DB Table & Column |
| :--- | :--- | :--- | :--- | :--- |
| Real Estate Company Name| `company_name` | Text | Required | `tenants.name` |
| Subdomain Prefix | `subdomain` | Text | Required, Unique, Lowercase | `tenants.subdomain` |
| Custom Domain Name | `custom_domain` | Text | Optional, e.g. `client.com` | `tenants.domain` |
| Tenant Admin Email | `admin_email` | Email | Required, Unique | `users.email` (Tenant Admin) |
| SaaS Package Plan | `package_id` | Select (Basic/Pro/Enterprise) | Required | `tenants.package_id` |
| Brand Primary Color | `brand_color` | Color Picker | Optional (Default #007bff) | `tenant_settings.primary_color` |
| Company Logo Upload | `logo_file` | File (PNG/JPG) | Optional, Max 2MB | `tenant_settings.logo_path` |

#### 🔗 Workflow Linkage & Data Flow:
```
[Submit Tenant Form] ──► Inserts tenant record into `tenants` table
                      ──► Creates Tenant Admin user in `users` (tenant_id = new_tenant_id)
                      ──► Saves branding settings in `tenant_settings`
                      ──► Configures Virtual Host & Domain Router
                      ──► Sends Onboarding Email with Login Link `tenant.apsdreamhome.com/admin/login`
```

---

## 🗺️ 3. Full Navigation & Route Linkage Map

| Module | URL Route | View File (.php) | Primary Controller | DB Table Linked | Post-Submit Destination |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **Farmers** | `/admin/farmers/create` | `admin/farmers/create.php` | `FarmerAdminController` | `farmers`, `land_parcels` | `/admin/farmers/show/{id}` |
| **Plots** | `/admin/plots/create` | `admin/plots/create.php` | `PlotManagementController` | `plots`, `colonies` | `/admin/colonies/view-map/{id}` |
| **Bookings** | `/admin/bookings/create` | `admin/bookings/create.php` | `BookingController` | `bookings`, `payment_receipts`, `installments` | `/admin/bookings/receipt/{id}` |
| **Associates**| `/associate/register` | `auth/associate_register.php` | `AssociateAuthController` | `users`, `associates`, `network_tree` | `/associate/dashboard` |
| **Leads** | `/admin/leads/create` | `admin/leads/create.php` | `LeadController` | `leads`, `voice_calls` | `/admin/leads/kanban` |
| **Tenants** | `/godmode/tenants/create`| `admin/tenants/create.php` | `TenantController` | `tenants`, `tenant_settings` | `/godmode/tenants/index` |
