# APS Dream Home - MASTER IMPLEMENTATION PLAN

**Version**: 2.0 | **Date**: 2026-07-04 | **Scope**: Full Ecosystem (Land → Colony → Plot → Booking → Commission → MLM → AI Agents)

---

## 📊 EXECUTIVE SUMMARY

### Current State Analysis

| Metric               | Reality                                                   | Gap                                                                             |
| -------------------- | --------------------------------------------------------- | ------------------------------------------------------------------------------- |
| **Database**         | 549 tables, 361 populated, 188 empty (34% bloat)          | Clean up, add missing FKs, add gata/khasra tracking                             |
| **Land Acquisition** | Pipeline exists (land_leads → land_deals → land_parcels)  | Missing: gata-wise plot mapping, mutation workflow, cost allocation             |
| **Colony/Plot**      | PlotCutterService + ColonyPricingService exist            | Not wired to UI, no gata linkage, pricing not auto-applied                      |
| **Booking**          | TWO parallel systems (plot_bookings + bookings)           | **CRITICAL**: Must unify                                                        |
| **MLM Commission**   | HybridCommissionEngine (2183 lines) + MLMCommissionEngine | Complete but UI not wired to real data                                          |
| **Associate Portal** | 56 associates, 30+ views                                  | Dashboard shows mock data, network tree not interactive                         |
| **Customer Portal**  | 49 customers, 28 views                                    | Plot detail basic, EMI calendar works but no map                                |
| **Admin**            | 178 controllers, 3,722 routes                             | No module architecture, RBAC works but menus incomplete                         |
| **Voice/AUC**        | VoiceCallService, TwilioVoiceService, AsteriskService     | Browser voice (Option A) + SIM calling (Option C) both exist but not integrated |
| **Agentic AI**       | AgentOrchestrator + 8 agent tables (all empty)            | Tables exist, no implementation                                                 |

---

## 🎯 STRATEGIC ARCHITECTURE DECISIONS

### 1. **Module Architecture (Adopt Now)**

```
app/Modules/
├── LandAcquisition/        # Land leads → deals → parcels → mutation
├── ColonyDevelopment/      # Sites → colonies → layouts → plot cutting
├── PlotManagement/         # Plots → gata mapping → pricing → inventory
├── SalesBooking/           # Unified bookings → payments → EMI → registry
├── AssociateMLM/           # Network → commissions → ranks → payouts
├── FinanceAccounting/      # Cash/Bank → TDS/GST → Vendors → Budgets
├── HRBackoffice/           # Employees → attendance → payroll → KPI
├── CRM/                    # Leads → pipeline → activities → scoring
├── VoiceAUC/               # Browser voice + SIM calling + WhatsApp + OLN
└── AgenticAI/              # Orchestrator + 8 specialist agents
```

### 2. **Event-Driven Architecture**

Every business action emits event → decoupled listeners:

```
BookingConfirmed → [CommissionCalc, EMIGeneration, Notification, InventoryUpdate, AgenticAITrigger]
PaymentReceived → [CommissionRelease, LedgerUpdate, ReceiptGen, AgenticAITrigger]
LeadCreated → [AutoAssign, WhatsAppTemplate, CRMPipelineEntry, AgenticAITrigger]
PlotGenerated → [PricingApply, MapUpdate, AvailabilitySync, AgenticAITrigger]
```

### 3. **Unified Booking System (SINGLE SOURCE OF TRUTH)**

```
DROP plot_bookings table → MERGE into bookings table
bookings.type ENUM('plot', 'property', 'resell')
All FKs: booking_payment_schedules, booking_documents, agreements, booking_demand_letters → bookings.id
```

---

## 📋 PHASE-WISE EXECUTION PLAN

---

### **PHASE 0: FOUNDATION & CLEANUP (Week 1-2)**

#### 0.1 Database Hygiene

```sql
-- 1. Add missing columns to plots
ALTER TABLE plots ADD COLUMN gata_number VARCHAR(50) NULL AFTER block;
ALTER TABLE plots ADD COLUMN khasra_number VARCHAR(50) NULL AFTER gata_number;
ALTER TABLE plots ADD COLUMN khata_number VARCHAR(50) NULL AFTER khasra_number;
ALTER TABLE plots ADD COLUMN survey_number VARCHAR(50) NULL AFTER khata_number;
ALTER TABLE plots ADD COLUMN land_parcel_id INT NULL AFTER colony_id;
ALTER TABLE plots ADD COLUMN acquisition_cost_allocated DECIMAL(15,2) DEFAULT 0 AFTER total_price;
ALTER TABLE plots ADD COLUMN development_cost_allocated DECIMAL(15,2) DEFAULT 0 AFTER acquisition_cost_allocated;
ALTER TABLE plots ADD INDEX idx_gata (gata_number);
ALTER TABLE plots ADD INDEX idx_khasra (khasra_number);
ALTER TABLE plots ADD CONSTRAINT fk_plot_parcel FOREIGN KEY (land_parcel_id) REFERENCES land_parcels(id);

-- 2. Fix colony duplication
-- Merge Raghunath Nagri (id 4 and 6 in colonies table)
-- Update all FKs to point to single colony_id

-- 3. Add site_id to plots (currently nullable)
-- UPDATE plots SET site_id = (SELECT site_id FROM colonies WHERE colonies.id = plots.colony_id)

-- 4. Drop 188 empty tables (backup first!)
-- Tables with 0 rows AND no FK references AND not referenced by any FK
```

#### 0.2 Core Infrastructure

- [ ] **Event System**: `app/Shared/Events/EventDispatcher.php` + listeners
- [ ] **DTO Layer**: `app/Shared/DTO/` for all module communication
- [ ] **Repository Pattern**: BaseRepository + module-specific repositories
- [ ] **Service Contracts**: Interfaces for all major services

#### 0.3 Unified Booking Migration

```php
// Migration script: merge_plot_bookings_to_bookings.php
// 1. Add type column to bookings
// 2. Migrate plot_bookings → bookings with type='plot'
// 3. Update all FK references
// 4. Drop plot_bookings table
// 5. Add triggers to keep sites.total_plots in sync
```

---

### **PHASE 1: LAND ACQUISITION MODULE (Week 2-3)**

#### 1.1 Data Model Enhancement

```sql
-- land_parcels: Add gata/khasra/khata/survey tracking
ALTER TABLE land_parcels ADD COLUMN gata_number VARCHAR(50);
ALTER TABLE land_parcels ADD COLUMN khasra_number VARCHAR(50);
ALTER TABLE land_parcels ADD COLUMN khata_number VARCHAR(50);
ALTER TABLE land_parcels ADD COLUMN khatauni_number VARCHAR(50);
ALTER TABLE land_parcels ADD COLUMN survey_number VARCHAR(50);
ALTER TABLE land_parcels ADD COLUMN mutation_status ENUM('pending','in_progress','completed','rejected') DEFAULT 'pending';
ALTER TABLE land_parcels ADD COLUMN mutation_number VARCHAR(100);
ALTER TABLE land_parcels ADD COLUMN mutation_date DATE;
ALTER TABLE land_parcels ADD COLUMN revenue_village VARCHAR(100);
ALTER TABLE land_parcels ADD COLUMN tehsil VARCHAR(100);
ALTER TABLE land_parcels ADD COLUMN district VARCHAR(100);
ALTER TABLE land_parcels ADD COLUMN state VARCHAR(50);
ALTER TABLE land_parcels ADD COLUMN pincode VARCHAR(10);
ALTER TABLE land_parcels ADD COLUMN gps_boundary JSON; -- GeoJSON polygon

-- land_acquisitions: Cost breakdown per gata
ALTER TABLE land_acquisitions ADD COLUMN cost_per_gata JSON; -- {gata_no: cost}
ALTER TABLE land_acquisitions ADD COLUMN total_gatas INT;
ALTER TABLE land_acquisitions ADD COLUMN total_area_acres DECIMAL(10,4);
ALTER TABLE land_acquisitions ADD COLUMN stamp_duty_pct DECIMAL(5,2);
ALTER TABLE land_acquisitions ADD COLUMN registration_fee_pct DECIMAL(5,2);
```

#### 1.2 Workflow Implementation

| Stage              | Action                         | Auto-Trigger                         |
| ------------------ | ------------------------------ | ------------------------------------ |
| **New Lead**       | Broker/scout entry             | WhatsApp to owner, assign telecaller |
| **Screening**      | Legal check (Patta, EC, FMB)   | Document checklist auto-create       |
| **Site Visit**     | GPS + photos + measurements    | Visit report template                |
| **Due Diligence**  | Title search, encumbrance cert | Legal opinion request                |
| **Negotiation**    | Price + terms                  | Offer letter generator               |
| **Sale Agreement** | Advance payment + agreement    | Stamp duty calc + doc gen            |
| **Registration**   | Sub-registrar appointment      | Payment schedule for balance         |
| **Mutation**       | Revenue dept application       | Status tracking + alerts             |

#### 1.3 Services to Wire/Complete

- `LandAcquisitionService` → Already complete, needs UI wiring
- `PlotCutterService` → Add gata-wise allocation logic
- `ColonyPricingService` → Auto-fetch land cost + dev cost

#### 1.4 UI Views (admin/land-inventory/)

- [ ] Leads pipeline (Kanban) ✅ exists
- [ ] Lead detail with timeline ✅ exists
- [ ] **NEW**: Gata/Parcel mapping view
- [ ] **NEW**: Mutation tracker
- [ ] **NEW**: Cost allocation per gata
- [ ] Documents ✅ exists
- [ ] Site visits ✅ exists
- [ ] Legal opinions ✅ exists
- [ ] Payments ledger ✅ exists

---

### **PHASE 2: COLONY DEVELOPMENT & PLOT MANAGEMENT (Week 3-4)**

#### 2.1 Colony Creation Flow

```
Land Acquisition (registered + mutated)
    ↓
Create Colony (sites table)
    ↓
Upload Layout (DWG/PDF/Image) → colony_layouts
    ↓
Define Blocks/Sectors
    ↓
Plot Cutting (PlotCutterService) with gata mapping
    ↓
Auto-Pricing (ColonyPricingService)
    ↓
Sales Ready
```

#### 2.2 Plot-Cutter Enhancement (Gata-Wise)

```php
// In PlotCutterService::generatePlots()
// NEW: Accept land_parcel_id parameter
// NEW: For each generated plot, assign gata_number from parcel
// NEW: Allocate acquisition_cost proportionally: plot_area / total_parcel_area * parcel_cost
// NEW: Store in plots.gata_number, plots.land_parcel_id, plots.acquisition_cost_allocated
```

#### 2.3 Colony Pricing Automation

```php
// ColonyPricingService::calculateColonyPricing()
// Already fetches: land_cost (from land_acquisitions) + dev_cost (colony_development_costs)
// NEW: Auto-trigger after plot generation
// NEW: Apply premiums: corner +10%, park-facing +15%, wide-road +8%
// NEW: Update plots.base_price_per_sqft, price_per_sqft, total_price
// NEW: Update colony.starting_price = MIN(plots.total_price)
```

#### 2.4 Layout Map Visualization

- **Leaflet.js** + **GeoJSON** for interactive map
- Color-code: Available (green), Booked (yellow), Sold (red), Hold (gray)
- Click plot → sidebar with details, booking CTA
- Overlay: Gata boundaries, roads, parks, amenities

#### 2.5 UI Views (admin/colony-pipeline/)

- [ ] Dashboard ✅ exists
- [ ] Colony Detail ✅ exists
- [ ] Layout Form ✅ exists
- [ ] **NEW**: Plot Grid with gata overlay
- [ ] **NEW**: Interactive Map (Leaflet)
- [ ] Development Costs ✅ exists
- [ ] **NEW**: Pricing Calculator (auto + manual override)
- [ ] **NEW**: Block-wise availability

---

### **PHASE 3: UNIFIED SALES & BOOKING (Week 4-5)**

#### 3.1 Single Booking System

```sql
-- bookings table (enhanced)
ALTER TABLE bookings ADD COLUMN type ENUM('plot','property','resell') NOT NULL DEFAULT 'plot';
ALTER TABLE bookings ADD COLUMN plot_id INT NULL; -- for plot bookings
ALTER TABLE bookings ADD COLUMN property_id INT NULL; -- for property bookings
ALTER TABLE bookings ADD COLUMN colony_id INT NULL;
ALTER TABLE bookings ADD COLUMN booking_amount DECIMAL(15,2);
ALTER TABLE bookings ADD COLUMN total_price DECIMAL(15,2);
ALTER TABLE bookings ADD COLUMN status ENUM(
  'token_paid','agreement_signed','emi_active','partially_paid',
  'fully_paid','registry_done','possession_given','cancelled','transferred'
) DEFAULT 'token_paid';
ALTER TABLE bookings ADD COLUMN customer_id BIGINT UNSIGNED NOT NULL;
ALTER TABLE bookings ADD COLUMN associate_id BIGINT UNSIGNED NULL;
ALTER TABLE bookings ADD COLUMN sales_manager_id BIGINT UNSIGNED NULL;
ALTER TABLE bookings ADD COLUMN channel ENUM('direct','associate','online','walkin','referral') DEFAULT 'direct';
ALTER TABLE bookings ADD COLUMN referral_code VARCHAR(50) NULL;
```

#### 3.2 Booking Lifecycle State Machine

```
TOKEN_PAID → AGREEMENT_SIGNED → EMI_ACTIVE → PARTIALLY_PAID → FULLY_PAID → REGISTRY_DONE → POSSESSION_GIVEN
     ↓              ↓              ↓              ↓              ↓           ↓            ↓
  CANCELLED      CANCELLED     CANCELLED     CANCELLED     CANCELLED   TRANSFERRED  TRANSFERRED
```

#### 3.3 Services (Wire Existing)

- `BookingLifecycleService` → 14 methods, complete
- `EMIAutomationService` → Daily penalty accrual, demand letters
- `AgreementGenerationService` → Doc generation + eSign
- `NocRegistryService` → NOC + registry workflow

#### 3.4 Payment Flow

| Step        | Amount       | Trigger    | Auto-Action                                         |
| ----------- | ------------ | ---------- | --------------------------------------------------- |
| Token       | 1-5%         | Booking    | Plot status → booked, WhatsApp to customer          |
| Agreement   | 10-20%       | Sign       | EMI schedule generated, NACH mandate                |
| EMI Monthly | Per schedule | Due date   | Penalty accrual (cron), demand letter (30d overdue) |
| Registry    | Balance      | Completion | NOC, registry, possession                           |

#### 3.5 UI Views

- **Admin**: booking-lifecycle/ (all 18 views ✅)
- **Associate**: my_bookings, book_plot, browse ✅
- **Customer**: bookings, payment, agreements ✅
- **NEW**: Interactive booking map (Leaflet)

---

### **PHASE 4: ASSOCIATE MLM ECOSYSTEM (Week 5-6)**

#### 4.1 Real Data Dashboard

```php
// AssociateController::dashboard() - REPLACE MOCK WITH:
$stats = [
  'total_earnings' => $this->mlmEngine->getTotalEarnings($associateId),
  'month_earnings' => $this->mlmEngine->getMonthEarnings($associateId),
  'network_size' => $this->networkService->getDownlineCount($associateId),
  'direct_referrals' => $this->networkService->getDirectCount($associateId),
  'pending_payouts' => $this->mlmEngine->getPendingPayouts($associateId),
  'rank_progress' => $this->rankService->getProgress($associateId),
  'next_rank_requirements' => $this->rankService->getNextRankReq($associateId),
];
```

#### 4.2 Network Tree Visualization

- **Cytoscape.js** or **D3.js** force-directed graph
- Click node → modal with: name, rank, earnings, downline count, join date
- Filters: by rank, by status, by date range
- Export: PNG, PDF

#### 4.3 CRM Kanban (Leads)

```
NEW → CONTACTED → QUALIFIED → PROPOSAL → NEGOTIATION → WON/LOST
```

- Drag-drop between columns
- Lead detail: timeline, notes, calls, WhatsApp, documents
- Quick actions: Call, WhatsApp, Email, Schedule Visit
- Bulk import CSV + assign to team

#### 4.4 Commission Breakdown (Real)

| Track       | Source             | Rate            | UI Section           |
| ----------- | ------------------ | --------------- | -------------------- |
| **A**       | Slab Differential  | 15%             | Plot sale commission |
| **B**       | Performance Rollup | 3%              | Monthly bonus        |
| **C**       | Milestone Escrow   | 2%              | Registry/completion  |
| **Monthly** | Generation Bonus   | 2%/1.5%/1%/0.5% | Gen 1-7              |
| **Monthly** | Matching Bonus     | 100%/50%/25%    | Gen 1-3              |
| **Royalty** | Site Manager Pool  | 2%              | ≥₹50L GBV            |

#### 4.5 Referral System

- QR Code (Canvas API) + Share links
- Track clicks in `referral_clicks` table
- Leaderboard: weekly/monthly/all-time
- Tiered bonuses: 5 referrals = bonus, 10 = higher tier

#### 4.6 KYC & Documents

- Upload: PAN, Aadhaar, Photo, Bank proof, Cancelled cheque
- Status: pending → verified → rejected
- Expiry alerts (30d before)
- Admin approval workflow

---

### **PHASE 5: CUSTOMER PORTAL EXCELLENCE (Week 6-7)**

#### 5.1 Plot Detail Page (Interactive)

- **Leaflet Map**: Plot highlighted, neighboring plots clickable
- **Payment Timeline**: Vertical timeline with status, amounts, receipts
- **Documents**: Agreement (eSign status), NACH mandate, Demand letters, NOC
- **Site Visit**: Calendar booking + feedback form
- **EMI Calculator**: What-if scenarios (prepay, tenure change)

#### 5.2 Wallet & Auto-Pay

- Wallet balance + transaction history
- Auto-debit for EMI (NACH/Razorpay)
- Top-up: UPI, Card, NetBanking
- Withdrawal request (refunds)

#### 5.3 Communication Center

- Notifications (in-app + push + WhatsApp)
- Support tickets with SLA
- Chat with associate/sales manager

---

### **PHASE 6: FINANCE & ACCOUNTING (Week 7-8)**

#### 6.1 Modules (Use Existing Services)

| Module         | Tables                                 | Service               | UI Status |
| -------------- | -------------------------------------- | --------------------- | --------- |
| Bank Accounts  | bank_accounts, bank_transactions       | BankingService        | ❌        |
| Cash Book      | cash_book, cash_book_denominations     | CashBookService       | ❌        |
| Petty Cash     | petty_cash, petty_cash_vouchers        | PettyCashService      | ❌        |
| Cheques        | cheques_issued, cheques_received       | ChequeService         | ❌        |
| TDS            | tds_entries, tds_challans, tds_returns | TdsConfigService      | ❌        |
| GST            | gst_invoices, gst_returns              | GSTService            | ❌        |
| Vendors        | vendors, purchase_orders, vendor_bills | VendorService         | ❌        |
| Expenses       | expenses, expense_claims               | ExpenseService        | ❌        |
| Budgets        | department_budgets, budget_expenses    | BudgetService         | ❌        |
| Reconciliation | bank_reconciliation                    | ReconciliationService | ❌        |

#### 6.2 Integration Points

- **Booking Payment** → Auto-create bank_transaction + cash_book entry
- **Commission Payout** → TDS 194H (5%) auto-deduct → TDS challan
- **Vendor Bill** → 3-way match (PO + GRN + Bill) → Payment
- **Expense Claim** → Employee portal → Manager approval → Accounting

---

### **PHASE 7: HR & BACKOFFICE (Week 8-9)**

#### 7.1 Employee Portal (Department-RBAC)

- Attendance (biometric sync ready)
- Leave management (apply/approve/balance)
- Payroll with salary structure
- Payslip PDF generation
- KPI tracking per designation

#### 7.2 Operations

- Daily site reports
- Vendor management
- Material stock
- Quality checklists

---

### **PHASE 8: VOICE + WHATSAPP (AUC) SYSTEM (Week 9-10)**

#### 8.1 Option A: Browser Voice Bot (FREE)

```javascript
// In chat_widget.php - already has mic button
// Use Web Speech API (Chrome Hindi support)
// STT: browser native (free, Hindi)
// TTS: Sarvam AI (free tier) OR piper-tts (self-hosted)
// LLM: Existing AucBrainService → PropertyChatbotService
```

#### 8.2 Option C: SIM Calling (Asterisk + GSM Gateway)

```php
// AsteriskService (AMI) - EXISTS
// SIMCallingController + views - EXISTS
// AGI Script: aps_ai_agent.php - EXISTS (fix STDIN crash)
// Dialplan Generator: For 4-8 SIM slots
// Hardware: 4-port GSM Gateway (OpenVox/Yeastar) ~₹50k
```

#### 8.3 Unified AUC Brain

- `AucBrainService` → Single intelligence for all channels
- Context: Customer history, plot interest, conversation memory
- Handoff: Bot → Human (associate/telecaller) seamless

#### 8.4 WhatsApp Business API

- Meta Cloud API (free tier: 1000 conversations/month)
- Templates: 5 pre-filled (Property, Price, Visit, EMI, Booking)
- Click tracking: `whatsapp_click_log` table ✅
- Webhook: Incoming → CRM lead creation → Auto-assign

---

### **PHASE 9: AGENTIC AI - 8 AGENT SYSTEM (Week 10-12)**

#### 9.1 Architecture (Copy from Best PHP Frameworks)

```php
// Based on: Neuron AI, SuperAgent, Pagent, Agent Orchestrator
// Pattern: Workflow-based, Tool-calling, Multi-agent delegation

// AgentOrchestrator.php - EXISTS (empty tables)
class AgentOrchestrator {
    public function dispatch(Task $task): Result;
    public function delegate(string $agentType, Task $task): Result;
    public function workflow(WorkflowDefinition $wf): Result;
}

// Each Agent = Class with Tools
interface AgentInterface {
    public function execute(Task $task): Result;
    public function getTools(): array;
    public function canHandle(string $intent): bool;
}
```

#### 9.2 The 8 Agents

| Agent                | Tools                                      | Triggers                | Key Outputs                     |
| -------------------- | ------------------------------------------ | ----------------------- | ------------------------------- | ----------------- | ---------------------- |
| **LeadGen**          | FB Ads API, Google Ads API, Scraper, Email | Daily cron, new project | Leads in `leads` table, scored  |
| **Sales**            | WhatsApp, Call, SMS, CRM                   | New lead, follow-up due | Conversations, bookings         |
| **Marketing**        | Social APIs, Email, SMS                    | Campaign                | Campaign                        | Campaign calendar | Posts, emails, metrics |
| **Finance**          | Bank API, Tally, GST Portal                | Daily, monthly          | Reconciliation, alerts, returns |
| **HR**               | Attendance, Leave, Payroll                 | Daily, monthly          | Payslips, compliance            |
| **Operations**       | Site visit, Vendor, Stock                  | Daily                   | Site reports, issues            |
| **Customer Success** | Chat, WhatsApp, Ticket                     | Inbound query           | Resolution, escalation          |
| **Data/Insights**    | SQL, ML, Reports                           | On-demand + scheduled   | Predictions, anomalies          |

#### 9.3 Implementation Using Existing Tables

```sql
-- Tables already exist (empty):
agent_task_logs, agent_insights, agent_escalations,
agent_conversations, agent_messages

-- Add:
ALTER TABLE agent_task_logs ADD COLUMN agent_type VARCHAR(50);
ALTER TABLE agent_task_logs ADD COLUMN parent_task_id INT NULL; -- for delegation
```

#### 9.4 Admin UI (Views Exist - Wire Them)

- `/admin/agentic-ai/` - Dashboard ✅
- `/admin/agentic-ai/agent/{type}` - Agent detail ✅
- `/admin/agentic-ai/conversations` - All conversations ✅
- `/admin/agentic-ai/conversation/{id}` - Chat view ✅
- `/admin/agentic-ai/logs` - Task logs ✅
- `/admin/agentic-ai/auto-reply` - Auto-reply config ✅

---

### **PHASE 10: PUBLIC FRONTEND MODERNIZATION (Week 12-14)**

#### 10.1 Design System (Teal Theme)

```css
:root {
  --primary: #0d9488;
  --primary-dark: #0f766e;
  --primary-light: #14b8a6;
  --surface: #ffffff;
  --bg: #f8fafc;
  --border: #e2e8f0;
  --radius-sm: 8px;
  --radius-md: 12px;
  --radius-lg: 16px;
  --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.05);
  --shadow-md: 0 4px 12px rgba(0, 0, 0, 0.08);
  --shadow-lg: 0 8px 24px rgba(0, 0, 0, 0.12);
}
```

#### 10.2 Component Library (Build Once)

| Component | Variants                          | Source |
| --------- | --------------------------------- | ------ |
| Card      | default, glass, elevated          | Custom |
| Button    | primary, secondary, ghost, danger | Custom |
| StatCard  | icon + value + trend              | Custom |
| Table     | sortable, filterable, paginated   | Custom |
| FormField | floating label, validation        | Custom |
| Modal     | centered, drawer, fullscreen      | Custom |
| Sidebar   | collapsible, mobile drawer        | Custom |
| Avatar    | image/initials + status           | Custom |

#### 10.3 Pages to Rebuild (Priority)

1. **Home** - Hero + colony cards + stats
2. **Properties** - Filter sidebar + glass cards + map view
3. **Property Detail** - Gallery, virtual tour, EMI calc, share, inquiry
4. **Colony Detail** - Master plan, plot map, amenities, price bands
5. **Plot Detail** - Dimensions, facing, price, booking CTA, nearby
6. **Booking Flow** - Multi-step wizard, docs, payment
7. **Associate Portal** - Teal theme, glass cards
8. **Customer Portal** - Teal theme, glass cards

#### 10.4 Libraries to Adopt (Copy from GitHub)

- **Leaflet.js** - Interactive maps (MIT)
- **Cytoscape.js** - Network graphs (MIT)
- **Chart.js** - Dashboards (MIT)
- **Tom Select** - Advanced selects (MIT)
- **Flatpickr** - Date picker (MIT)
- **Dropzone.js** - File upload (MIT)
- **Toastify** - Notifications (MIT)

---

### **PHASE 11: INFRASTRUCTURE & DEVOPS (Week 14-15)**

#### 11.1 Docker

```dockerfile
# Multi-stage: composer → npm build → php-fpm
FROM php:8.3-fpm-alpine
# Install extensions: pdo_mysql, redis, gd, zip, intl
COPY --from=composer /usr/bin/composer /usr/bin/composer
WORKDIR /var/www/html
```

#### 11.2 CI/CD (GitHub Actions)

```yaml
# .github/workflows/ci.yml
- PHPStan Level 5
- PHP CS Fixer
- PHPUnit (unit)
- Playwright (E2E)
- Docker build
- Deploy to Azure Container Apps
```

#### 11.3 Cron Jobs (systemd timers)

| Script                        | Schedule        | Purpose                            |
| ----------------------------- | --------------- | ---------------------------------- |
| `firebase_sync_cron.php`      | _/5 _ \* \* \*  | Block C Firebase → MySQL           |
| `daily_mlm_cron.php`          | 0 2 \* \* \*    | Rank promo, clawback, payout batch |
| `daily_emi_cron.php`          | 0 3 \* \* \*    | Penalty accrual, demand letters    |
| `daily_voice_cron.php`        | 0 6 \* \* \*    | Schedule OLN calls                 |
| `agent_orchestrator_cron.php` | _/15 _ \* \* \* | Process agent tasks                |
| `cache_warmup.php`            | _/10 _ \* \* \* | Hot path cache                     |

#### 11.4 Monitoring

- Health endpoint: `/health` (DB, Redis, Queue)
- Laravel Telescope equivalent for custom MVC
- Sentry for error tracking

---

## 🔑 KEY INTEGRATION POINTS (WIRE THESE FIRST)

| From              | To                | Event                | Listener                                  |
| ----------------- | ----------------- | -------------------- | ----------------------------------------- |
| LandAcquisition   | ColonyDevelopment | LandRegistered       | Create colony, link parcels               |
| ColonyDevelopment | PlotManagement    | LayoutApproved       | Run PlotCutter, apply pricing             |
| PlotManagement    | SalesBooking      | PlotsGenerated       | Update availability, notify associates    |
| SalesBooking      | AssociateMLM      | BookingConfirmed     | Calculate commission, create payout batch |
| SalesBooking      | Finance           | PaymentReceived      | Ledger entry, receipt, TDS                |
| CRM               | AgenticAI         | LeadCreated          | LeadGen agent scores, Sales agent assigns |
| VoiceAUC          | CRM               | CallCompleted        | Log activity, update lead status          |
| All Modules       | AgenticAI         | _Any business event_ | Data agent analyzes, Insight agent alerts |

---

## 🎯 SUCCESS METRICS (Per Phase)

| Phase | Metric                  | Target            |
| ----- | ----------------------- | ----------------- |
| 0     | Empty tables            | < 10              |
| 0     | Booking tables          | 1 unified         |
| 1     | Lead conversion         | 20% (from 0.7%)   |
| 1     | Lead response time      | < 30 min          |
| 2     | Plot generation time    | < 5 min           |
| 2     | Pricing accuracy        | 100% auto         |
| 3     | Booking → Registry      | < 45 days         |
| 3     | Payment collection      | > 95% on-time     |
| 4     | Associate active rate   | > 80%             |
| 4     | Commission transparency | 100% real-time    |
| 5     | Customer self-service   | > 90%             |
| 6     | Finance close           | < 5 days          |
| 8     | Voice call success      | > 85%             |
| 8     | WhatsApp opt-in         | > 60%             |
| 9     | Agent task automation   | > 70%             |
| 10    | Page load speed         | < 2s (Lighthouse) |
| 11    | Deployment frequency    | Daily             |
| 11    | Zero-downtime deploy    | 100%              |

---

## ⚠️ RISKS & MITIGATION

| Risk                                | Probability | Impact   | Mitigation                                         |
| ----------------------------------- | ----------- | -------- | -------------------------------------------------- |
| Booking merge breaks existing data  | High        | Critical | Full backup, staging test, feature flag            |
| Colony duplication causes FK issues | High        | High     | Merge script with transaction, verify all FKs      |
| PlotCutter gata mapping incomplete  | Medium      | High     | Add validation, manual override UI                 |
| MLM commission calc mismatch        | Low         | Critical | Unit tests for every track, audit trail            |
| Agentic AI hallucination            | Medium      | High     | Human-in-loop for critical actions, eval framework |
| Voice bot Hindi accuracy            | Medium      | Medium   | Test with real users, fallback to human            |
| Performance at scale                | Low         | Medium   | Load test, Redis cache, query optimization         |

---

## 📦 GITHUB REPOS TO ADAPT (COPY CODE, NOT FORK)

| Repo                                      | What to Copy                                       | License |
| ----------------------------------------- | -------------------------------------------------- | ------- |
| `neuron-core/neuron-ai`                   | Workflow engine, tool system, multi-agent patterns | MIT     |
| `forgeomni/superagent`                    | Agent loop, multi-provider, squad mode             | MIT     |
| `helgesverre/pagent`                      | Pest-inspired API, streaming, observability        | MIT     |
| `agenticOrchestrator/agenticorchestrator` | Multi-tenancy, workflow patterns, evaluation       | MIT     |
| `claude-php/claude-php-agent`             | ReAct, Plan-Execute, MCP server                    | MIT     |
| `LISACORNEL/lms` (Frappe)                 | Land acquisition → plot → contract workflow        | MIT     |
| `iRaed/pm-os` (Laravel)                   | Modular monolith architecture, RBAC                | MIT     |
| `derpixler/skolva` (Go)                   | Property/leasing modules, double-entry accounting  | AGPL    |

---

## 🚀 IMMEDIATE NEXT ACTIONS (TODAY)

1. **Run booking merge migration** (backup first!)
2. **Fix colony duplication** (merge Raghunath Nagri)
3. **Add gata/khasra columns to plots**
4. **Wire PlotCutterService to layout-form** (test with Raghunath Nagri)
5. **Wire ColonyPricingService to auto-apply after plot generation**
6. **Replace AssociateController mock data with real MLM queries**
7. **Add Leaflet map to colony detail + plot detail**
8. **Start AgentOrchestrator implementation using Neuron AI patterns**

---

## 📝 UPDATED AGENTS.MD ADDITIONS

```markdown
## Module Architecture

- app/Modules/{Domain}/ with Service, Repository, Events, Routes, DTOs
- Controllers thin: HTTP → DTO → Service → Event
- Shared kernel in app/Shared/{Events,DTO,Repository,Contracts}

## Event System

- BookingConfirmed, PaymentReceived, CommissionCalculated, LeadCreated
- Listeners in each Module's EventServiceProvider
- AgenticAI subscribes to all for autonomous action

## Data Integrity Rules

- sites.total_plots = COUNT(plots) WHERE colony_id = sites.colony_id
- colonies.total_plots = same
- plots.site_id FK required (add column)
- plots.gata_number + land_parcel_id for gata-wise tracking
- bookings table UNIFIED (type: plot|property|resell)

## Empty Table Policy

- Migration creates table → Feature MUST populate within 1 sprint
- Else: DROP in next cleanup sprint
- Current: 188 empty tables = technical debt to clear in Phase 0

## Agentic AI Stack

- Base: Neuron AI workflow patterns + SuperAgent multi-agent
- Orchestrator: app/Services/AgenticAI/AgentOrchestrator.php
- 8 Agents: LeadGen, Sales, Marketing, Finance, HR, Ops, CustSvc, Data
- Tables: agent_task_logs, agent_insights, agent_escalations, agent_conversations, agent_messages
- Admin UI: /admin/agentic-ai/\* (views created, needs wiring)

## Voice/AUC Stack

- Browser Voice (Option A): Web Speech API + Sarvam AI TTS
- SIM Calling (Option C): Asterisk AMI + GSM Gateway (4-8 ports)
- Unified Brain: AucBrainService (single intelligence)
- WhatsApp: Meta Cloud API + click tracking + templates
```

---

**This plan is exhaustive, phase-gated, and wired to your existing codebase. Every service mentioned already exists in `app/Services/` - the work is WIRING, not BUILDING from scratch. Start Phase 0 today.**
