# PART 4: DATABASE DESIGN

## 11. Database Architecture

### Overview

| Metric | Value | Industry Standard |
|--------|-------|-------------------|
| Total Tables | 584 | 300-500 (good) |
| Views | 1 | N/A |
| Foreign Keys | 263 | 200+ (good) |
| InnoDB Tables | 100% | Required |
| Tables with PK | 100% | Required |
| tenant_id Coverage | 429 tables | SaaS essential |

### Database Naming Conventions

```
Table names:    snake_case, plural (e.g., plot_bookings)
Column names:   snake_case (e.g., tenant_id, created_at)
Primary keys:   id (auto_increment)
Foreign keys:   {table}_id (e.g., booking_id)
Indexes:        idx_{table}_{column}
Unique:         uniq_{table}_{column}
```

### Critical Tables with Data

| Table | Rows | Purpose |
|-------|------|---------|
| users | 187 | All system users |
| plots | 456 | Plot inventory |
| bookings | 41 | Customer bookings |
| leads | 9,546 | Lead database |
| colonies | 5 | Active colonies |
| associates | 65 | MLM network |
| employees | 11 | Staff |
| mlm_commission_ledger | 307 | Commission records |

## 12. Entity-Relationship Diagram (Core)

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                         CORE ENTITY RELATIONSHIPS                            │
├─────────────────────────────────────────────────────────────────────────────┤
│                                                                             │
│  ┌──────────┐       ┌──────────┐       ┌──────────┐                        │
│  │ colonies │──────▶│  plots   │──────▶│plot_bookings│                      │
│  └──────────┘       └──────────┘       └──────────┘                        │
│       │                   │                   │                             │
│       ▼                   ▼                   ▼                             │
│  ┌──────────┐       ┌──────────┐       ┌──────────┐                        │
│  │colony_   │       │plot_costs│       │bookings  │                        │
│  │development│      └──────────┘       └──────────┘                        │
│  │_costs    │                               │                              │
│  └──────────┘                               ▼                              │
│                                        ┌──────────┐                        │
│  ┌──────────┐       ┌──────────┐      │booking_  │                        │
│  │  users   │──────▶│ leads    │      │payment_  │                        │
│  └──────────┘       └──────────┘      │schedules │                        │
│       │               │               └──────────┘                        │
│       │               │                                                    │
│       ▼               ▼                                                    │
│  ┌──────────┐       ┌──────────┐       ┌──────────┐                        │
│  │associates│──────▶│mlm_      │       │mlm_      │                        │
│  └──────────┘       │network_  │       │commission│                        │
│       │             │tree      │       │_ledger   │                        │
│       │             └──────────┘       └──────────┘                        │
│       │                                                                     │
│       ▼                                                                     │
│  ┌──────────┐       ┌──────────┐       ┌──────────┐                        │
│  │employees │──────▶│employee_ │       │departments│                       │
│  └──────────┘       │attendance│       └──────────┘                        │
│                     └──────────┘                                           │
│                                                                             │
└─────────────────────────────────────────────────────────────────────────────┘
```

## 13. Table Categories and Purposes

### User Management (15 tables)

```
users                      → Core user table (all roles)
user_roles                 → Role assignments
role_permissions           → RBAC permissions
admin_menu_items           → Sidebar menu config
admin_user_menu_permissions → Per-user menu access
login_attempts             → Brute force protection
password_reset_tokens      → Password reset flow
user_preferences           → User settings
user_activity_logs_unified → Audit trail
sessions                   → Session management
```

### Colony and Land (25 tables)

```
colonies                   → Colony/master community
plots                      → Individual plots
plot_bookings              → Plot reservations
plot_categories            → Plot types
plot_costs                 → Development costs
land_acquisitions          → Land purchase records
land_purchases             → Farmer land deals
field_visits               → Site inspection logs
colony_layouts             → Plot map layouts
colony_development_costs   → Infrastructure costs
price_history              → Pricing changes
pricing_approvals          → Price change approvals
```

### Sales and Bookings (18 tables)

```
bookings                   → Customer bookings
booking_payment_schedules  → EMI schedules
booking_payment_receipts   → Payment records
booking_demand_letters     → Demand notices
booking_refunds            → Refund records
booking_status_history     → Status timeline
booking_transfers          → Ownership transfers
booking_commission_lock    → Commission rate lock
booking_emis               → EMI tracking
nach_mandates              → NACH/e-Mandate registration
nach_debit_log             → Debit tracking
```

### MLM Commission Engine (20 tables)

```
mlm_network_tree           → Unilevel tree (commission calc)
network_tree               → Binary tree (display)
mlm_commission_ledger      → 307 entries, ₹1.05Cr+
mlm_commission_plans       → Commission plan config
mlm_rank_slabs             → Rank thresholds
mlm_rank_benefits          → Rank rewards
mlm_rank_history           → Rank progression
mlm_profiles               → Associate profiles
mlm_settings               → 18 config rows
mlm_payout_batches         → Payout processing
mlm_payouts                → Individual payouts
mlm_royalty_pool           → Royalty distribution
mlm_salary_grants          → Salary assignments
```

### Finance (20 tables)

```
accounting                 → Transactions
bank_accounts_master       → Bank accounts
bank_reconciliation        → Reconciliation records
cash_collections           → Cash receipts
chart_of_accounts          → COA structure
cheque_register            → Cheque tracking
daily_cash_book            → Daily cash records
expenses                   → Expense records
gst_transactions           → GST entries
journal_entries            → Journal entries
journal_entry_lines        → Journal line items
payment_voucher_log        → Voucher records
petty_cash                 → Petty cash management
tds_register               → TDS tracking
tds_certificates_issued    → TDS certificates
vendor_payments            → Vendor payments
vendors                    → Vendor master
```

### CRM (25 tables)

```
leads                      → Lead management
lead_activities            → Activity timeline
lead_deals                 → Deal pipeline
lead_notes                 → Lead notes
lead_scores                → Lead scoring
lead_sources_extended      → Source tracking
crm_assignments            → Lead assignment
crm_campaigns              → Marketing campaigns
crm_custom_fields          → Custom field config
crm_form_submissions       → Form submissions
crm_interactions           → Communication logs
crm_segments               → Smart segments
crm_tasks                  → Follow-up tasks
crm_lead_forms             → Form builder
```

### HR (10 tables)

```
employees                  → Employee master
employee_attendance        → Attendance records
employee_leave_requests    → Leave management
employee_payslips          → Payslip generation
departments                → Department master
designations               → Designation master
employee_designation_roles → Role mapping
```

### Communication (15 tables)

```
notifications              → Notification master
email_queue                → Email queue
email_templates            → Email templates
email_tracking             → Open/click tracking
sms_queue                  → SMS queue
sms_templates              → SMS templates
whatsapp_messages          → WhatsApp logs
whatsapp_lead_shares       → Lead sharing tracking
push_notifications         → Push notification queue
push_subscriptions         → FCM subscriptions
```

### Legal (10 tables)

```
legal_documents            → Document master
legal_document_categories  → Document types
legal_document_templates   → Template library
legal_clause_library       → Clause repository
legal_ai_prompts           → AI document prompts
rera_compliance_log        → RERA tracking
```
