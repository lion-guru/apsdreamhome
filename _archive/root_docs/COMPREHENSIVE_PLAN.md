# APS Dream Home - Comprehensive Production Readiness Plan

**Date**: 2026-07-03  
**Scope**: Full ecosystem - Public Frontend, Associate Portal, Customer Portal, Admin Panel, Land→Colony→Plot→Booking→Commission→MLM workflows  
**Target**: Production-ready, modern UI/UX, deep workflow coverage, zero broken flows

---

## 🔴 CRITICAL FIXES (Week 1)

### 1. RBAC & Access Control

- [x] AdminController enforces `isAdmin()` for all 178 admin controllers
- [x] `isAdmin()` checks `role IN ('admin','super_admin','manager','employee','telecaller')`
- [x] `test_login` no longer grants `admin_id` to associate/customer
- [x] Employee login sets `employee_id` session for dashboard
- [ ] **ADD**: Customer portal routes use `requireCustomerLogin()` (already in UserController)
- [ ] **ADD**: Associate routes use `requireAssociateLogin()` (need to verify)
- [ ] **ADD**: Role-based sidebar via `AdminMenuService` already works - verify all 20 sub-roles

### 2. Database Schema Gaps

- [x] `tasks.department` column added
- [ ] **ADD**: `plots.colony_id` FK to colonies (verify)
- [ ] **ADD**: `plot_bookings.plot_id` FK to plots (verify)
- [ ] **ADD**: `booking_payment_schedules.booking_id` FK
- [ ] **ADD**: `mlm_commission_ledger.associate_id` FK to users
- [ ] **ADD**: Index on `users.referred_by` for upline walks

### 3. Missing Menu Items (Admin Sidebar)

Based on `admin_menu_items` vs 178 controllers - add missing:

- [ ] **Land Acquisition**: land_acquisitions, land_parcels, land_brokers, land_deals, land_documents
- [ ] **Colony Pipeline**: colony_layouts, colony_pricing_feasibility, colony_development_costs
- [ ] **Plot Management**: plots, plot_bookings, plot_development, plot_locks
- [ ] **Sales/Booking**: bookings, booking_payment_schedules, booking_demand_letters, booking_documents, booking_refunds, booking_transfers
- [ ] **Finance**: bank_accounts, cash_book, petty_cash, cheques, tds, gst, vendors, expenses, reconciliation
- [ ] **MLM**: commission_ledger, payout_batches, royalty_pool, rank_benefits, network_tree, generation_bonuses
- [ ] **Backoffice**: attendance, leaves, payslips, kpis, shifts, documents
- [ ] **CRM**: leads, lead_pipeline, lead_activities, lead_scores, lead_sources
- [ ] **Voice/WhatsApp**: voice_agents, call_schedule, oln_campaigns, whatsapp_templates
- [ ] **AI**: chatbot_conversations, ai_models, prompt_templates

---

## 🟡 PUBLIC FRONTEND OVERHAUL (Week 2-3)

### 1. Design System Migration

- [ ] **Color**: All indigo/purple (#667eea, #764ba2, #4f46e5) → Teal (#0d9488, #0f766e, #14b8a6)
- [ ] **Components**: Glass morphism cards, 3D hover effects, consistent spacing
- [ ] **Typography**: Inter font throughout, consistent heading scale
- [ ] **Icons**: FontAwesome 6.5, consistent sizing

### 2. Pages to Modernize (Priority Order)

| Page                         | Status                      | Action                                                            |
| ---------------------------- | --------------------------- | ----------------------------------------------------------------- |
| `pages/home.php`             | Has hero, stats, projects   | Polish animations, add colony cards                               |
| `pages/properties.php`       | Full filter/sort/pagination | Glass cards, better empty states                                  |
| `pages/property_detail.php`  | Basic                       | **REBUILD**: Gallery, virtual tour, EMI calc, share, inquiry CTA  |
| `pages/colony_detail.php`    | Basic                       | **REBUILD**: Master plan, plot map, amenities, price bands        |
| `pages/plot_detail.php`      | Basic                       | **REBUILD**: Dimensions, facing, price, booking CTA, nearby plots |
| `pages/booking_form.php`     | Functional                  | Multi-step wizard, document upload, payment gateway               |
| `pages/user_dashboard.php`   | Good                        | Polish stats cards, add quick actions                             |
| `pages/about.php`            | CMS-driven                  | Modern team grid, timeline, certifications                        |
| `pages/contact.php`          | Working                     | Floating WhatsApp, callback request                               |
| `pages/become_associate.php` | Standalone                  | **MERGE** into associate portal layout                            |

### 3. Under Construction Page

- [ ] Replace orange theme with teal
- [ ] Add email capture for launch notifications
- [ ] Show actual feature preview cards

---

## 🟢 ASSOCIATE PORTAL DEEP ENHANCEMENT (Week 3-4)

### 1. Dashboard - Real Data (Not Mock)

```php
// Current: mock stats
// Needed:
$stats = [
  'total_earnings' => MLMCommissionEngine::getTotalEarnings($associateId),
  'month_earnings' => MLMCommissionEngine::getMonthEarnings($associateId),
  'network_size' => MLMNetworkService::getDownlineCount($associateId),
  'direct_referrals' => MLMNetworkService::getDirectCount($associateId),
  'pending_payouts' => MLMCommissionEngine::getPendingPayouts($associateId),
  'rank_progress' => RankPromotionService::getProgress($associateId),
];
```

### 2. Network Tree Visualization

- [ ] Interactive D3.js / Cytoscape.js tree
- [ ] Click node → see downline, commissions, rank
- [ ] Filter by rank, status, date joined

### 3. CRM (Leads) - Full Pipeline

- [ ] Kanban board: New → Contacted → Qualified → Proposal → Negotiation → Won/Lost
- [ ] Lead detail: timeline, notes, calls, WhatsApp, documents
- [ ] Quick actions: Call, WhatsApp, Email, Schedule visit
- [ ] Bulk import CSV, assign to team

### 4. Commissions - Real Breakdown

- [ ] Track A (Slab Differential 15%), Track B (Performance 3%), Track C (Milestone 2%)
- [ ] Monthly: Generation Bonus, Matching Bonus, Royalty Pool
- [ ] Download PDF statement per month

### 5. Team Management

- [ ] Add team members (by referral code)
- [ ] View their: leads, bookings, earnings, rank
- [ ] Set targets, track performance

### 6. Property Listings (Associate-owned)

- [ ] CRUD with images, pricing, colony mapping
- [ ] Status: draft → listed → booked → sold
- [ ] Share links with referral tracking

### 7. Referral System

- [ ] QR code (canvas-based, working)
- [ ] Share to WhatsApp/FB/Telegram/Twitter/LinkedIn/Email/SMS
- [ ] Track clicks in `referral_clicks` table
- [ ] Leaderboard

### 8. Documents & KYC

- [ ] Upload: PAN, Aadhaar, Photo, Bank proof
- [ ] Status: pending → verified → rejected
- [ ] Expiry alerts

---

## 🔵 CUSTOMER PORTAL DEEP ENHANCEMENT (Week 4-5)

### 1. Dashboard

- [ ] My Plots: status, payments, documents
- [ ] Upcoming EMI calendar with amounts
- [ ] Site visit scheduling
- [ ] Support tickets

### 2. Plot Detail View

- [ ] Interactive map (Leaflet) with plot highlighted
- [ ] Payment schedule with download receipts
- [ ] Agreement e-sign (already has esign route)
- [ ] NACH mandate (already has route)
- [ ] Demand letters history

### 3. Booking Flow

```
Browse → Select Plot → Token Payment (Razorpay) →
Agreement Sign → EMI Schedule → Registry → Possession
```

- [ ] Each step tracked in `booking_status_history`
- [ ] SMS/Email/WhatsApp at each transition
- [ ] Customer sees real-time status

### 4. Wallet & Payments

- [ ] Wallet balance, transaction history
- [ ] Multiple payment methods
- [ ] Auto-debit for EMI

---

## 🟣 ADMIN PANEL - MODULE COMPLETION (Week 5-7)

### Module 1: Land Acquisition → Colony Development

| Table                        | Controller                | Views                    | Status     |
| ---------------------------- | ------------------------- | ------------------------ | ---------- |
| `land_acquisitions`          | LandAcquisitionController | list, create, view, edit | ❌ Missing |
| `land_parcels`               | LandParcelController      | list, map view           | ❌ Missing |
| `land_deals`                 | LandDealController        | pipeline kanban          | ❌ Missing |
| `sites` (colonies)           | ColonyController          | 14 views exist           | ✅ Done    |
| `colony_layouts`             | ColonyLayoutController    | upload, view, blocks     | ❌ Partial |
| `colony_pricing_feasibility` | PricingController         | feasibility report       | ❌ Missing |
| `plots`                      | PlotController            | grid, list, map          | ⚠️ Basic   |

**Action**: Build missing controllers + views using existing services (`LandAcquisitionService`, `PlotCutterService`, `ColonyPricingService`)

### Module 2: Sales + Booking Lifecycle

- [ ] Booking approvals workflow (admin review → approve/reject)
- [ ] Demand letter generation (auto + manual)
- [ ] Payment collection (cash/cheque/online/bank)
- [ ] Refund workflow with approval
- [ ] Transfer of booking (nomination)
- [ ] RERA compliance checklist per booking
- [ ] Registry NOC generation

### Module 3: Finance & Accounting (15 tables, 50+ methods)

- [ ] Bank accounts & reconciliation
- [ ] Cash book with denominations
- [ ] Petty cash with vouchers
- [ ] Cheque register (issue/deposit/clear/bounce)
- [ ] TDS: deduct, deposit, 26Q/27Q return prep
- [ ] GST: invoices, GSTR-1/3B prep
- [ ] Vendor management + purchase orders
- [ ] Expense claims with approval workflow
- [ ] Budget vs actual per department

### Module 4: MLM Commission Engine

- [ ] **HybridCommissionEngine** (colony-specific 3-track) - exists, verify
- [ ] **MLMCommissionEngine** (full MLM) - exists, verify
- [ ] Generation Bonus Engine - exists
- [ ] Matching Bonus Service - exists
- [ ] Infinity Override - exists
- [ ] Daily cron: rank promotion, clawback check, payout batch
- [ ] Payout approval workflow
- [ ] TDS 194H on commission

### Module 5: Backoffice

- [ ] Attendance (biometric sync)
- [ ] Leave management (apply/approve/balance)
- [ ] Payroll with salary structure
- [ ] Payslip generation (PDF)
- [ ] KPI tracking per employee
- [ ] Operations log (daily site reports)
- [ ] Reports: sales, collection, commission, HR

---

## 🤖 MULTI-AGENT AGENTIC AI SYSTEM (Week 7-8)

### Architecture

```
┌─────────────────────────────────────────────┐
│           ORCHESTRATOR (CEO Agent)          │
│  - Receives high-level goals                │
│  - Delegates to specialist agents           │
│  - Tracks progress, resolves conflicts      │
└─────────────────────────────────────────────┘
         │           │           │           │
    ┌────┴────┐ ┌────┴────┐ ┌────┴────┐ ┌────┴────┐
    │ LeadGen │ │  Sales  │ │Marketing│ │ Finance │
    │ Agent   │ │ Agent   │ │ Agent   │ │ Agent   │
    └────┬────┘ └────┬────┘ └────┬────┘ └────┬────┘
         │           │           │           │
    ┌────┴────┐ ┌────┴────┐ ┌────┴────┐ ┌────┴────┐
    │   HR    │ │  Ops    │ │ CustSvc │ │  Data   │
    │ Agent   │ │ Agent   │ │ Agent   │ │ Agent   │
    └─────────┘ └─────────┘ └─────────┘ └─────────┘
```

### Agent Capabilities

| Agent          | Tools                             | Triggers                | Outputs                        |
| -------------- | --------------------------------- | ----------------------- | ------------------------------ |
| **LeadGen**    | FB/Google Ads API, Scraper, Email | Daily cron, new project | Leads in `leads` table, scored |
| **Sales**      | WhatsApp, Call, SMS, CRM          | New lead, follow-up due | Conversations, bookings        |
| **Marketing**  | Social APIs, Email, SMS           | Campaign schedule       | Posts, emails, metrics         |
| **Finance**    | Bank API, Tally, GST              | Daily, monthly          | Reconciliation, alerts         |
| **HR**         | Attendance, Leave, Payroll        | Daily, monthly          | Payslips, compliance           |
| **Operations** | Site visit, Vendor, Stock         | Daily                   | Reports, issues                |
| **CustSvc**    | Chat, WhatsApp, Ticket            | Incoming query          | Resolution, escalation         |
| **Data**       | SQL, ML, Reports                  | On-demand               | Insights, predictions          |

### Implementation

- [ ] `AgentOrchestrator` service (exists: `AgentOrchestrator.php`)
- [ ] Each agent = class with `execute(Task $task): Result`
- [ ] Task queue: `agent_task_logs` table
- [ ] Communication: `agent_messages` table
- [ ] Escalation: `agent_escalations` table
- [ ] Insights: `agent_insights` table
- [ ] Admin UI: `/admin/agentic-ai/*` (views created, needs wiring)

---

## 🎙️ VOICE & WHATSAPP (AUC) SYSTEM (Week 8-9)

### Current State: ALREADY BUILT

- `VoiceCallService`, `TwilioVoiceService`, `AICallingAgent`
- 3 Voice Agents: SiteVisitBooking, PropertyInquiry, LeadFollowUp
- `OLNService` for outbound nurturing
- WhatsApp webhook, templates, floating widget

### Gaps to Fix

- [ ] **Option A**: Browser Voice Bot (FREE) - `VoiceBotController` exists, needs Hindi STT/TTS
  - Use Web Speech API (Chrome Hindi) + piper-tts (offline) OR Sarvam AI
  - Integrate in `chat_widget.php` mic button
- [ ] **Option C**: SIM Calling (Asterisk + GSM Gateway)
  - `AsteriskService` AMI integration - exists
  - `SIMCallingController` + views - exists
  - AGI script `aps_ai_agent.php` - exists, fix STDIN crash
  - Dialplan generator for 4-8 SIM slots
- [ ] **Unified AUC Brain** - `AucBrainService` exists, wire to all channels
- [ ] WhatsApp click tracking - `whatsapp_click_log` table + `/api/track/whatsapp-click` - done
- [ ] WhatsApp templates pre-filled - 5 templates in chat widget - done

---

## 🗄️ DATABASE & INFRA (Week 9-10)

### 1. Migrations Needed

```sql
-- Foreign keys (add if missing)
ALTER TABLE plots ADD CONSTRAINT fk_plots_colony FOREIGN KEY (colony_id) REFERENCES sites(id);
ALTER TABLE plot_bookings ADD CONSTRAINT fk_pb_plot FOREIGN KEY (plot_id) REFERENCES plots(id);
ALTER TABLE plot_bookings ADD CONSTRAINT fk_pb_customer FOREIGN KEY (customer_id) REFERENCES users(id);
ALTER TABLE booking_payment_schedules ADD CONSTRAINT fk_bps_booking FOREIGN KEY (booking_id) REFERENCES bookings(id);

-- Indexes for performance
CREATE INDEX idx_users_referred_by ON users(referred_by);
CREATE INDEX idx_leads_assigned_status ON leads(assigned_to, status);
CREATE INDEX idx_plot_bookings_status ON plot_bookings(status);
CREATE INDEX idx_mlm_ledger_associate ON mlm_commission_ledger(associate_id);
```

### 2. Cron Jobs (systemd / Windows Task Scheduler)

| Script                                | Frequency | Purpose                            |
| ------------------------------------- | --------- | ---------------------------------- |
| `scripts/firebase_sync_cron.php`      | 5 min     | Block C Firebase → MySQL           |
| `scripts/daily_mlm_cron.php`          | 02:00     | Rank promo, clawback, payout batch |
| `scripts/daily_emi_cron.php`          | 03:00     | Penalty accrual, demand letters    |
| `scripts/daily_voice_cron.php`        | 06:00     | Schedule OLN calls                 |
| `scripts/agent_orchestrator_cron.php` | 15 min    | Process agent tasks                |
| `scripts/cache_warmup.php`            | 10 min    | Hot path cache                     |

### 3. Caching

- Redis for sessions, hot-path cache (5 min TTL)
- File fallback when Redis down
- Cache tags for invalidation

---

## 🎨 UI/UX STANDARDIZATION (Cross-cutting)

### Design Tokens

```css
:root {
  --color-primary: #0d9488; /* Teal 600 */
  --color-primary-dark: #0f766e; /* Teal 700 */
  --color-primary-light: #14b8a6; /* Teal 500 */
  --color-bg: #f8fafc;
  --color-surface: #ffffff;
  --color-border: #e2e8f0;
  --radius-sm: 8px;
  --radius-md: 12px;
  --radius-lg: 16px;
  --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.05);
  --shadow-md: 0 4px 12px rgba(0, 0, 0, 0.08);
  --shadow-lg: 0 8px 24px rgba(0, 0, 0, 0.12);
  --transition: all 0.2s ease;
}
```

### Component Library (Build Once, Use Everywhere)

- [ ] `Card` - glass morphism variant
- [ ] `Button` - primary/secondary/ghost/danger
- [ ] `StatCard` - with icon, trend, animation
- [ ] `Table` - sortable, filterable, paginated
- [ ] `Form` - consistent validation, floating labels
- [ ] `Modal` - centered, backdrop blur
- [ ] `Toast` - top-right, auto-dismiss
- [ ] `Sidebar` - collapsible, mobile drawer
- [ ] `Breadcrumb` - consistent
- [ ] `Avatar` - with fallback initials
- [ ] `Badge` - status colors
- [ ] `Progress` - steps, linear, circular

### Responsive Breakpoints

- Mobile: < 576px
- Tablet: 576px - 991px
- Desktop: 992px - 1399px
- Large: ≥ 1400px

---

## 🧪 TESTING & QA

### E2E Test Coverage (Playwright)

- [ ] Public: Home → Properties → Detail → Inquiry → Booking
- [ ] Associate: Register → Login → Dashboard → Leads → Commission → Referral
- [ ] Customer: Login → Dashboard → My Plots → Payments → Documents
- [ ] Admin: Login → Each module CRUD → Reports → Settings
- [ ] Voice: Call flow, webhook, recording
- [ ] WhatsApp: Template send, webhook, click tracking
- [ ] MLM: Commission calc, rank promo, payout
- [ ] Agentic AI: Task creation, delegation, completion

### Unit Tests (PHPUnit)

- [ ] Commission engines (all tracks)
- [ ] Plot cutter algorithm
- [ ] Booking lifecycle state machine
- [ ] Lead scoring
- [ ] Rank promotion logic

### Performance

- [ ] Page load < 2s (Lighthouse)
- [ ] API p95 < 500ms
- [ ] DB query optimization (EXPLAIN ANALYZE)
- [ ] Cache hit rate > 80%

---

## 📦 DEPLOYMENT READINESS

### Docker

- [ ] `Dockerfile` - multi-stage (composer, npm, php-fpm)
- [ ] `docker-compose.yml` - app, mysql, redis, nginx
- [ ] `.dockerignore`

### CI/CD (GitHub Actions)

- [ ] Lint (PHP CS Fixer, PHPStan)
- [ ] Test (PHPUnit, Playwright)
- [ ] Build Docker image
- [ ] Deploy to Azure Container Apps / VM

### Environment

- [ ] `.env.example` with all keys
- [ ] Azure Key Vault integration
- [ ] Health check endpoint `/health`

---

## 📋 UPDATED AGENTS.md - Key Additions

```markdown
## Multi-Agent System

- Orchestrator: `App\Services\AgentOrchestrator`
- Agents: LeadGen, Sales, Marketing, Finance, HR, Ops, CustSvc, Data
- Tables: agent_task_logs, agent_messages, agent_escalations, agent_insights
- Admin UI: /admin/agentic-ai/\*

## Voice/AUC System

- Browser Voice (Option A): VoiceBotController + Web Speech API
- SIM Calling (Option C): AsteriskService + SIMCallingController
- Unified Brain: AucBrainService
- WhatsApp: WhatsAppService + click tracking

## Department-Level RBAC

- employee_designation_roles: 43 mappings
- admin_role_menu_permissions: 20+ sub-roles
- AdminMenuService resolves designation+department → sub_role
- Sidebar filters automatically

## Land→Colony→Plot→Booking Workflow

Services: LandAcquisitionService, PlotCutterService, ColonyPricingService, PlottingService
Tables: land\_\*, sites, colonies, plots, plot_bookings, bookings, booking_payment_schedules
```

---

## 🚀 EXECUTION ORDER

```
Week 1-2:  CRITICAL FIXES + Public Frontend Design System
Week 3-4:  Associate Portal (Dashboard, Network, CRM, Commissions, Team)
Week 4-5:  Customer Portal (Dashboard, Plot Detail, Booking Flow, Wallet)
Week 5-7:  Admin Modules 1-5 (Complete all CRUD + Workflows)
Week 7-8:  Multi-Agent AI System (Orchestrator + 8 Agents + Admin UI)
Week 8-9:  Voice/AUC (Browser Voice + SIM Calling + Unified Brain)
Week 9-10: Database, Cron, Cache, Docker, CI/CD, Testing
```

**Total**: ~10 weeks for production-ready, enterprise-grade system

---

## 🛠️ MCP TOOLS TO USE

| Task                        | Tool                      |
| --------------------------- | ------------------------- |
| Database queries, schema    | `mysql` MCP               |
| Browser testing (E2E)       | `playwright` MCP          |
| File operations             | `filesystem` MCP          |
| Knowledge graph (decisions) | `memory` MCP              |
| Sequential reasoning        | `sequential_thinking` MCP |

---

## ✅ VERIFICATION CHECKLIST (Per Feature)

- [ ] Routes exist in `web.php` / `api.php`
- [ ] Controller extends correct base (AdminController / BaseController)
- [ ] Service class exists with null-safe methods
- [ ] View uses unified layout (admin / associate / customer / public)
- [ ] RBAC: Sidebar filters, controller protects
- [ ] DB: Tables exist, FKs, indexes
- [ ] Tests: E2E passes, unit covers logic
- [ ] UI: Teal theme, glass cards, responsive, i18n keys
- [ ] Docs: AGENTS.md updated, inline comments for complex logic

```

```
