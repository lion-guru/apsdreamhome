# PART 5: MODULE SPECIFICATIONS

## 15. Module 1: Colony Development Pipeline

### Purpose
Manage complete lifecycle: Land → Colony → Plot Cutting → Pricing → Sales Ready

### Flowchart
```
┌─────────────────────────────────────────────────────────────────┐
│                    COLONY DEVELOPMENT PIPELINE                   │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  ┌──────────┐    ┌──────────┐    ┌──────────┐    ┌──────────┐  │
│  │  LAND    │───▶│  COLONY  │───▶│  PLOT    │───▶│ PRICING  │  │
│  │ACQUISITION│   │ CREATION │    │ CUTTING  │    │ APPROVAL │  │
│  └──────────┘    └──────────┘    └──────────┘    └──────────┘  │
│       │               │               │               │        │
│       ▼               ▼               ▼               ▼        │
│  ┌──────────┐    ┌──────────┐    ┌──────────┐    ┌──────────┐  │
│  │ FARMER   │    │LEGAL     │    │ DIMENSION│    │  FINAL   │  │
│  │ PAYMENT  │    │ DOCUMENT │    │  ENTRY   │    │  RATE    │  │
│  └──────────┘    └──────────┘    └──────────┘    └──────────┘  │
│                      │               │               │        │
│                      ▼               ▼               ▼        │
│                 ┌──────────┐    ┌──────────┐    ┌──────────┐  │
│                 │  RERA    │    │  MAP     │    │  SALES   │  │
│                 │COMPLIANCE│    │  UPLOAD  │    │  READY   │  │
│                 └──────────┘    └──────────┘    └──────────┘  │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

### Features

| Feature | Controller | Tables | Status |
|---------|------------|--------|--------|
| Colony CRUD | ColonyController | colonies | Built |
| Plot Management | PlotManagementController | plots, plot_categories | Built |
| Plot Cutting | PlotCutterService | plots, colony_layouts | Built |
| Pricing | ColonyPricingService | price_history, pricing_approvals | Built |
| Land Inventory | LandInventoryController | land_acquisitions | Built |
| Interactive Map | ColonyController::map | plots (GeoJSON) | Built |
| Feasibility | ColonyFeasibilityService | colony_development_costs | Built |

## 16. Module 2: Sales and Booking Lifecycle

### Purpose
Manage: Lead → Qualification → Site Visit → Booking → EMI → Registry

### Flowchart
```
┌─────────────────────────────────────────────────────────────────┐
│                    SALES AND BOOKING LIFECYCLE                   │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  ┌──────────┐    ┌──────────┐    ┌──────────┐    ┌──────────┐  │
│  │  LEAD    │───▶│ QUALIFY  │───▶│   SITE   │───▶│ BOOKING  │  │
│  │ CAPTURE  │    │ SCORING  │    │   VISIT  │    │   ENTRY  │  │
│  └──────────┘    └──────────┘    └──────────┘    └──────────┘  │
│       │               │               │               │        │
│       ▼               ▼               ▼               ▼        │
│  ┌──────────┐    ┌──────────┐    ┌──────────┐    ┌──────────┐  │
│  │ CAMPAIGN │    │  ASSIGN  │    │FEEDBACK  │    │  TOKEN   │  │
│  │ TRACKING │    │ASSOCIATE │    │ CAPTURE  │    │  AMOUNT  │  │
│  └──────────┘    └──────────┘    └──────────┘    └──────────┘  │
│                      │               │               │        │
│                      ▼               ▼               ▼        │
│                 ┌──────────┐    ┌──────────┐    ┌──────────┐  │
│                 │   HOT    │    │ SCHEDULE │    │AGREEMENT │  │
│                 │  LEAD    │    │  VISIT   │    │  SIGN    │  │
│                 └──────────┘    └──────────┘    └──────────┘  │
│                                                                 │
│  ┌──────────┐    ┌──────────┐    ┌──────────┐    ┌──────────┐  │
│  │   EMI    │───▶│ PAYMENT  │───▶│   NOC    │───▶│REGISTRY  │  │
│  │ SCHEDULE │    │  TRACK   │    │GENERATION│    │  DONE    │  │
│  └──────────┘    └──────────┘    └──────────┘    └──────────┘  │
│       │               │               │               │        │
│       ▼               ▼               ▼               ▼        │
│  ┌──────────┐    ┌──────────┐    ┌──────────┐    ┌──────────┐  │
│  │ DEMAND   │    │ RECEIPT  │    │POSSESSION│    │COMPLETE  │  │
│  │ LETTER   │    │ GENERATE │    │  DATE    │    │          │  │
│  └──────────┘    └──────────┘    └──────────┘    └──────────┘  │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

### Features

| Feature | Controller | Tables | Status |
|---------|------------|--------|--------|
| Booking CRUD | BookingLifecycleController | bookings | Built |
| EMI Schedule | BookingPaymentSchedule | booking_payment_schedules | Built |
| Payment Recording | recordPayment() | booking_payment_receipts | Built |
| Demand Letters | BookingDemandLetter | booking_demand_letters | Built |
| Refund Processing | BookingRefund | booking_refunds | Built |
| Booking Transfer | BookingTransfer | booking_transfers | Built |
| Agreement | AgreementController | agreements | Built |
| NOC Generation | NocController | noc_documents | Built |
| Registry | RegistryController | registries | Built |

## 17. Module 3: MLM Commission Engine

### Purpose
Calculate and distribute commissions through multi-level marketing network (Fully Database-Driven).

### Commission Structure & Rank System (DEPRECATED: Hardcoded rules removed)

> **[WARNING] - Deprecation Notice**
> The hardcoded Track A/B/C and static rank thresholds have been completely removed to prevent revenue loops. 
> The system now relies 100% on the `mlm_rank_slabs` database table for Rank Configurations and the `mlm_commission_settings` / `BookingLifecycleService` for all distribution caps. 
> Freelance agents and Salaried Agents now have dual-mode resolution. Maximum payout cap is strictly 20%.

All commission slab details, rank targets (GBV), and bonuses are managed by the Admin Dashboard via DB tables, NOT code.

## 18. Module 4: Finance and Accounting

### Purpose
Complete financial management with Indian compliance (TDS, GST)

### Features

| Feature | Controller | Tables | Status |
|---------|------------|--------|--------|
| Cash Book | DailyCashBookController | daily_cash_book | Built |
| Bank Reconciliation | BankReconciliationController | bank_reconciliation | Built |
| Cheque Register | ChequeRegisterController | cheque_register | Built |
| TDS Management | TdsController | tds_register, tds_certificates_issued | Built |
| GST Filing | GstController | gst_transactions | Built |
| Vendor Payments | VendorController | vendor_payments, vendors | Built |
| Expenses | ExpenseController | expenses, expense_approvals | Built |
| Petty Cash | PettyCashController | petty_cash | Built |
| Financial Reports | FinancialReportController | chart_of_accounts | Built |

## 19. Module 5: CRM System

### Purpose
Complete lead-to-customer lifecycle management

### Lead Pipeline Stages
```
New → Contacted → Qualified → Site Visit → Proposal → Negotiation → Won/Lost
```

### Features

| Feature | Controller | Tables | Status |
|---------|------------|--------|--------|
| Lead Capture | LeadController | leads | Built |
| Lead Scoring | LeadScoringService | lead_scores | Built |
| Pipeline | LeadKanbanController | leads, lead_activities | Built |
| Deals | DealController | lead_deals | Built |
| Email Templates | CRMTemplateController | email_templates | Built |
| Bulk Outreach | CRMBulkController | email_queue, sms_queue | Built |
| Segmentation | CRMSegmentController | crm_segments | Built |
| Form Builder | CRMFormController | crm_lead_forms | Built |
| SLA Tracking | SLAController | crm_sla_rules, crm_sla_logs | Built |
| Meetings | MeetingController | crm_meetings | Built |
| Drip Campaigns | DripCampaignController | drip_enrollments | Built |

## 20-23. Modules 6-9: HR, Communication, AI, Mobile

### Summary

| Module | Key Features | Status |
|--------|-------------|--------|
| **HR** | Attendance, Leave, Payslips, Departments | Built |
| **Communication** | Email, SMS, WhatsApp, Push | Built |
| **AI** | Chatbot, Scoring, Valuation, Documents | Built |
| **Mobile** | 147 pages, Flutter, Android/iOS | Built |
