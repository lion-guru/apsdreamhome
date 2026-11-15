# CEO/COO Gap Analysis — APS Dream Home (Gorakhpur Real Estate ERP/CRM)
**Date:** 2026-11-16 · **Method:** read-only repo scan (grep/glob/read) + read-only live-DB probes (SELECT/SHOW only). No DB writes, no code edits, no commits.
**Scope question:** can this software actually run the company — land → R&D → plots → sales → bookings → EMI → registry → possession → MLM → HR → finance → marketing — as it stands today?
**Legend:** `LIVE` = used with real business data · `BUILT-UNPROVEN` = code complete & tested, but never touched real business · `PARTIAL` = exists, gaps/bypasses · `MISSING` = never built · `BROKEN` = exists, proven broken

---

## 1. Executive summary (15 lines)
1. The company has **excellent architecture and near-zero business reality running on it** — this is a very thoroughly-engineered **prototype-grade stack**, not an operating company.
2. Reference numbers (live DB): 82 users · 766 plots · 8 colonies · 994 live leads (37k+ junk soft-archived) · **23 plot bookings · 4 payments · 359 MLM ledger rows · 0 GST invoices · 0 cash withdrawals · 0 salary users actually processed against real attendance**.
3. **Nothing is LIVE end-to-end for real money**: Razorpay test-mode only, keys blank; EMI agreements 0 rows; registry appointments 0; possession certificates 0; attendance 0.
4. **Legal blocker**: every colony (8/8) has `rera_number = NULL` — the company has no legally-sellable RERA record anywhere in the system; no possession NOC exists.
5. **Not production-deployed**: `APP_ENV=development`, `APP_DEBUG=true`, localhost URL; mobile APK built but not on Play Console; secrets unrotated; domain untouched.
6. **Pipeline is a showroom**: 994 leads (87 won) have **zero linkage to plot bookings** (`plot_bookings.lead_id` = 0/23) — marketing spend cannot be ROI-measured.
7. **MLM is engineered but cash-flow-unproven**: commission engine (differential, rank, royalty, cap) implemented & probed; **never paid a real rupee**; `salary_grants = 0`, TDS register = 0, GST = 0.
8. **HR shell is full and empty**: 15 employees, 0 attendance, 0 leave applications, 7 test salary rows. Payroll has never processed a real month.
9. **Finance is built but unused/non-compliant**: GST/TDS/e-invoice/e-way-bill APIs have no keys; no invoices; no statutory receipts.
10. **Land brokers & farmer acquisition**: engine built (agrees/proforma/voucher) but `land_brokers = 1` after 3 months, possession 0 — land pipeline is developer-side Excel work, not system.
11. **Marketing "automation" is honest only after bugfix**: every drip-enrollment 500'd for months; SMTP blank = all email sends silently fail; WhatsApp/Twilio live-ish but never sent a single real customer message; AI voice dialer is dry-run-only by owner decision.
12. **A genuinely rare strength**: the brownfield rescue rate is unusually high — the team repaired 500s, phantom schemas, enum/truncation bugs, double-entrancy-transaction wallet races. Engineering culture is "verify, don't believe."
13. **But it proves the opposite of operations**: 1,165 uncommitted files at S451; five parallel actors; recurring regressions; the codebase is better tested than it is used.
14. **Verdict:** Do **not** onboard a single real customer until the 8-item "Phase 0" gate in §4 passes. The software is 70% feature-complete and 5% operationally-complete.
15. **Single sentence for the CEO:** the product is a fully-built-but-never-flown aircraft; the next quarter belongs to **one real booking walked end-to-end with real money, tests deleted, RERA live, production live** — not more features.

---

## 2. Capability matrix — Flow | Status | Evidence | Notes

### A. Land acquisition → colony development → plot cutting
| Flow | Status | Evidence | Notes |
|---|---|---|---|
| Land leads / farmer ledger | BUILT-UNPROVEN | `/admin/land-inventory/leads` (47 routes, `LandInventoryController`), farmer-ledger, RTGS voucher view, `FarmerAcquisitionController@disburseMilestone` | `farmer_land_acquisitions = 0` |
| Broker KYC + agreements | PARTIAL | `/admin/land-inventory/brokers`, `broker-agreement.php` (11-point legal indemnity, S456) | `land_brokers = 1`; no agreement signed |
| Colony model + plot cutting | PARTIAL-LIVE | `colonies`=8, `ColonyController`, `PlotManagementController`, `LegalColonyPipelineController@plotCutting`; `plots`=766, colony plots visible on live `/colony/{slug}/plots` | Cutting done for 3 Gorakhpur colonies; rest synthetic |
| Development cost / P&L per plot | BUILT-UNPROVEN | `colony_development_costs` (migrations S455), `ColonyAnalyticsService::getColonyAnalytics` | never used for a green P&L report with real costs |
| Plot pricing / PLC rules | BUILT-UNPROVEN | `PlcRuleService` (+ `commission engine`, PLC config, S455) | plots priced in DB once; 0 real customers affected |
| Gata/Khasra land linking | BUILT-UNPROVEN | `land_parcels` 22, Link Desk (S311) | no parcel validated against a real bhulekh entry |
| **RERA registration / certificate** | **MISSING** | `colonies.rera_number` 8/8 NULL; no RERA certificate generated | legal prerequisite to sell plots in UP |
| Land litigation tracking | BUILT-UNPROVEN | `/admin/land-inventory/litigations`, `land_litigations` table | never used with real case |

### B. Leads → pipeline → site visit → sales desk
| Flow | Status | Evidence | Notes |
|---|---|---|---|
| Lead capture (web/AI/portal) | PARTIAL-LIVE | 994 live (38k junk archived S255); sources: referral 103, website 95 | 0 attribution to any booking |
| CRM pipeline (Kanban, leads desk) | BUILT | `/associate/leads`, `leads` kanban, lost-reason/SLA (S264), scoring | live use limited to demos |
| Site visits | PARTIAL-BUILT | `site_visits`=37; GPS check-in + maps routes exist | no visit tied to real closing |
| AI qualification | BUILT-UNPROVEN | 994 scored (~300 hot); `LeadScoringService`; **0 conversions attributed** | blind AI proposes nothing — leads sit in `nurture` |
| Assignment → associate | BUILT | round-robin, `biz_stakeholder` style filters | 37k unassigned-ghost leads had to be archived |
| Tele-calling | PARTIAL | `telecalling_desk.php` + dialer task scheduler registered | no live agent has used it |
| e-sign / KYC packets | BROKEN-BY-CREDS | `document_esign`=0; Leegality `api_key` **empty** | e-sign dead at credential layer |

### C. Booking → payment → EMI
| Flow | Status | Evidence | Notes |
|---|---|---|---|
| Plot booking end-to-end (front + associate) | BUILT-UNPROVEN | `stories`: booking lifecycle 26/26 E2E steps green (S455); `BookingLifecycleService`; token/EMI schedule | `plot_bookings`=23 (seeded/synthetic) |
| Promo codes / concession engine | BUILT-UNPROVEN | `/api/promo/validate`, `FESTIVE21`, `SUBHLABH5` (S462) | 0 redemptions by real buyers |
| Token booking (₹25k/25%) | BUILT-UNPROVEN | `plot_token_bookings`, TokenBookingController | flow never run live |
| Razorpay live payments | **MISSING** | `service_configs razorpay.key_id/key_secret = EMPTY`; `razorpay.test_mode = 1`; webhook_secret empty | **no buyer can pay online**, only wire/cash |
| EMI schedule / auto-debit (NACH) | PARTIAL-UNPROVEN | 276 `booking_payment_schedules` rows (240 synthetic TEST-HIST + 36 seeded); `booking_emi_agreements`=0; `EMIAutomationService` never executed with a real debit attempt | collections risk #1 |
| Foreclosure (prepayment) | BUILT-UNPROVEN | `ForeclosureService` fixed S515; probe green rollback-txn | never a real foreclosure request |
| Demand letters / penal execution | PARTIAL-UNPROVEN | S285 DripQueue liveness fixed; demand letters table exists, `booking_demand_letters`=? (none verified) | no letter actually posted to a defaulting buyer |
| Receipts / payment history | BUILT-UNPROVEN | `booking_payment_receipts`=3; passbook view | 3 receipts, all from seeds |
| Wallet MLM credit (referral bonus) | BUILT | `wallet_points`=39 rows live, `wallet_transactions`=3; `WalletService` txn-hardened (S455) | wallet never used for a live purchase |
| Online registration installments | PARTIAL | NACH routes exist `/user/bookings/{id}/nach`; not connected to a live bank API | no mandate ever registered |

### D. Registry → mutation → possession → legal handover
| Flow | Status | Evidence | Notes |
|---|---|---|---|
| Registry stepper (7 stages) | BUILT-UNPROVEN | `BookingLifecycleController@registryStepper`, `RegistryAppointmentController`, SRO gatepass print | `registry_appointments`=0 |
| NOC / No-Dues certificate | PARTIAL | `NocRegistryController` routes complete; `NocService` | never issued; dues live with fake installment state |
| Mutation update | BUILT | `RegistryController@updateMutation` | never executed |
| Possession certificate | BUILT-UNPROVEN | `/admin/sales/bookings/{id}/possession-certificate` | `possession_certificates`=0 |
| Stamp duty / surcharge calc | BUILT | calculators + UP rates hardcoded in views | correct math, never consumed by a real deal |
| Document locker / e-copies | PARTIAL | `document_esign`=0, documents=27 (all test) | buyer has never seen their papers |
| **Legal enforceability of sale agreement** | **MISSING (process)** | Agreement generator exists; **no signed agreement ever exists** in DB (signed gate) | no enforceable contract in play |
| Possession → handover to buyer | MISSING (business) | `township_*` ops (lighting/water/waste/etc.) invented for future customer handover; 0 real residents ever received keys | handover is nothing more than code |

### E. MLM — joining → lead → commission → payout → rank
| Flow | Status | Evidence | Notes |
|---|---|---|---|
| Rank matrix (7 tiers, ₹10L+5Ass → ₹5Cr+3Pres) | BUILT-UNPROVEN | `update_mlm_plan_and_leader_allowances_s456.php`; `MLMCommissionEngine` | 5 associate_profiles; nobody at BDM yet |
| Differential / generation overrides | BUILT | `explainDifferentialDistribution()` S455: 3-tier senior→junior gap proven | math correct; zero real distributions |
| Commission ledger | PARTIAL-TABLE-LIVE | 359 entries: paid=204, approved=27, pending=3, **cancelled=125** | 1/3 cancelled; nobody knows why |
| Wallet-based payout | BUILT-MITIGATED | `WalletService` FOR-UPDATE hardening (S455/S466); foreclosure/LeaveEncash rewires | no INR actually moved (3 wallet txn rows) |
| ₹5,000 spot bonus + leaderboard | BUILT | `SpotBonusService`, `sales_races` S204 | never awarded for a real closing |
| Leadership salary (5-tier career plan) | BUILT-UNPROVEN | `SalaryIncentiveService`, cron `cron_salary_grants.php`, `mlm_salary_grants`=0 | enabled the moment commission engine started; still no RB |
| Fast-start ₹2,100 kickstart | BUILT | `evaluateFastStartKickstart()` | 0 triggered (no real new joiner) |
| Rank rewards (Mobile/Tablet/Laptop/Tour/Bike/Car) | BUILT-UNPROVEN | `CareerRewardService`; claim→approve→dispatch desk S459 | 0 functional claims |
| Allowances (office rent ₹10–50k; site visits ₹800/₹1,800) | BUILT-UNPROVEN | `LeadershipExpenseFundService` S456; admin claims desk | 0 claims paid; migration not live |
| Withdrawal (MLM → bank) | BUILT + probed | `WalletController`, deduct-at-request, bank-verify; probe green | never a real withdrawal |
| **TDS deduction on payout** | PARTIAL | 10% TDS in `PropertyCommissionController` & `tds_register`; `Associate\TdsController` views + 16A slips | `tds_register`=0 → **no TDS ever deducted, company has statutory liability** |
| MLM auditability | LACKING | clawback S-H1; `clawed_back` ledger type exists; no periodic reconciliation report in use | nobody reconciles ledger↔wallet↔bank monthly |
| Gen-gap cross-team security | FIXED | S455 probe: unrelated upline received ₹0 | correct |

### F. HR — attendance, leave, payroll, F&F
| Flow | Status | Evidence | Notes |
|---|---|---|---|
| Employee database | PARTIAL | `employees`=15 (no exits/joins tracked); `employee_profiles`=8; `employee_attendance`=0 | **nobody has ever marked in/out in this system for real** |
| Attendance biometric/sync | MISSING | no biometric device integration; pattern only | HR has Excel/fingerprint-company machine outside system |
| Leave (apply/approve/balance/types) | BUILT-UNPROVEN | `EmployeeSelfServiceController@leave*`, `leave_applications`=0; leave_types tested | never a real leave request |
| Payroll (process/approve/payslip/PDF) | PARTIAL-SKELETON | `PayrollController`, `PayrollService` (÷ run, 7 test salary_payments); phantom columns 500'd pre S467 | never a real month closed; Form 16 generation can run |
| Salary advance / loan with wallet link | BUILT-UNPROVEN | `SalaryService::deductFromSalary` + wallet credit path (S466 fix — previously money never arrived) | 0 real advances touched wallet |
| F&F / Exit settlement | BUILT-UNPROVEN | `FnfSettlementController`, `OffboardingService` | never executed for a real leaver |
| Overtime / incentive | BUILT-UNPROVEN | `OvertimeService`, S344 phase 1&2 | nothing in payroll actually ran with it |
| Tax declarations / Form 16 | BUILT | ESS tax regime view + `downloadForm16` | never generated for a real employee |
| **PF/ESI statutory** | MISSING | `pf_deduction`/`esi_deduction` columns only; no EPF challan, no ESIC monthly return, no UAN | statutory non-compliance |
| Departments / offer letters | PARTIAL | onboarding skeleton only | 0 offer letters ever sent through system |

### G. Finance — receipts, ledgers, GST, TDS, bills
| Flow | Status | Evidence | Notes |
|---|---|---|---|
| Chart of Accounts / Journal entry | PARTIAL | `JournalEntryService` (post/ledger/TB + recurring); `journal_entries`+lines exist | nobody reconciles; books never closed |
| Cash drawer / day-end closing | BUILT-UNPROVEN | `cash_drawer_closings`, denomination counter | 0 closings ever submitted |
| Ledgers (AR/AP aging) | BUILT (v3 of gap plan) | `ReceivablesAgingService`, `PayablesAgingService` | never a real aging report consumed by CFO |
| Vendor management / PO / GRN | BUILT-UNPROVEN | `VendorController` (KYC), PO workflow (S469 tables), GRN desk | `contractor_bills`=0 |
| **GST invoices (B2B)** | BUILT-UNPROVEN | `GstComplianceController` + `SupportTicketController` — invoice_form renders; **0 invoice rows; `gstn` API keys empty; `invoices` table 0** | buyer has never received a compliant tax invoice for any booking |
| **GST e-invoice / e-way bill** | BROKEN/CRED-GATED | `e_invoice_number` columns; GSTN sandbox empty | impossible legally for B2B >₹1Cr |
| TDS register & 16A | BUILT-UNPROVEN | `tds_register`=0; 16A view built | no TDS ever deducted/deposited |
| Fund flow / cash flow | BUILT-UNPROVEN | `FundFlowService` — built last phase | never runs |
| Bank statement sync | MISSING | manual reconciliation only | no auto-match of buyer payments |
| Multi-currency | BUILT-UNPROVEN | `ExchangeRateService` | not needed yet; exists |
| Payment gateway (live) | **MISSING** | razorpay keys empty, test mode only | see gap #1 |
| Fixed assets / depreciation | MISSING | FNF assets-only; no fixed-asset register | balance sheet is fiction |
| Salary accounts / PF/ESI | MISSING | see F | statutory |

### H. Marketing — campaigns, drip, WhatsApp, SMS, AI voice
| Flow | Status | Evidence | Notes |
|---|---|---|---|
| Drip campaigns (UI/queue) | PARTIAL-BROKEN-BEFORE | S284–285 fixed enroll 1265; honest send (or logged fail); 5 campaigns queued | **no SMTP credentials → every email would fail** |
| SMS | PARTIAL-CRED-GATED | `twilio` SID set, `test_mode=0`; but identity-provider exists | 0 SMS sent to a real customer ever |
| WhatsApp bot (menu/plots/visit/EMI) | BUILT-UNPROVEN | `WhatsAppService` + flow engine; autoReply | Twilio whatsapp number `+18126684585` set; never a real customer interaction logged |
| Marketing campaigns | PARTIAL | 11 campaigns exist (mostly seed/demo) | 0 linked to closed deals |
| Funnel analytics | PARTIAL | 994 lead stage distribution; sources by campaign quality visible | no channel ever optimized by real data |
| AI call agent (outbound) | BUILT-DRY-RUN-ONLY | `ai_calling_schedule` 2, `ai_call_sessions` 27 — all test/PROBE; **never dialled a real lead** by owner decision | company does it all by human phone today |
| Live dialing | Owner-gated | `--execute` not run; 9:00–20:00 gate | **company inbound is live; outbound is theatre** |
| Push notifications (web) | BUILT | service worker, VAPID set | no customer ever opted-in for real |
| Referral program (all roles) | BUILT-UNPROVEN | `customer_referral.php` upgraded S484; leaderboard | 0 referral bonuses paid to real referrers |

### I. Portals — customer / associate / employee / mobile app
| Flow | Status | Evidence | Notes |
|---|---|---|---|
| Customer portal | BUILT-UNPROVEN | `/user/dashboard` — passbook, EMI, receipts, documents, grievance, solar, registry | **0 real customers ever used it** — all "Customer One" seeds |
| Associate portal | BUILT-UNPROVEN | commission dashboard, rank progress, ID card, marketing kit, ledger | 5 associates, all test |
| Agent portal | BUILT-UNPROVEN | leads desk + co-own | 0 agents |
| Employee portal | BUILT-UNPROVEN | self-service (S352/344),Grievance, Form 16 | 15 employees, never logged |
| Mobile app (Flutter) | BUILT-ALPHABET | 1.2.10+13 AAB built & deployed to download folder; 500s swept to 0 (S448–450) | **Play Console upload pending**; no real user installed it |
| Public website | BUILT | 14 public colony/project/location pages, offers, promo validator, 2005 routes swept 0×500 | actually the only surface real people could reach |

### J. Platform / security / operations
| Flow | Status | Evidence | Notes |
|---|---|---|---|
| Auth/login, lockout, brute-force | FIXED | S473 dead-lockout layers revived; 5 fails → 15 min | good |
| CI/CD | GREEN | 5 workflows green since S465; lint/test/schema guards + pubspec drift guard | good |
| Route sweep | GREEN | 1990+2005 routes → 0×500 (S463/450/483) | static guarantee only |
| Schema ratchet | GREEN but SHALLOW | mismatch scanner fixed (S466); 1133 tables all referenced ones exist now | large dead surface (~1200 tables) |
| Migrations idempotent | GREEN | `database/migrations/` pattern holds | good |
| Backup | PARTIAL | mysqldump habit; nightly cron set up; 43 GB workspace | **no offsite backup confirmed**; restore never rehearsed |
| Infra (still XAMPP/Windows dev) | **MISSING** | no staging, no production host, no domain | total blocker |
| Security (headers, CSRF, CSRF-via-GET) | GOOD | S471/463/487/461 fixed | good |
| Disk | TIGHT | 0 free was reached twice; 43 GB reclaimed | monitor |

### K. Field ops, construction & smart modules (built 2025–2026)
| Flow | Status | Evidence | Notes |
|---|---|---|---|
| Township ops desks (water/DG/lighting/CCTV/waste/horticulture/security) | BUILT-UNPROVEN | `township_water_logs`=15, `township_security_rosters`=15, `township_horticulture`, CCTV, waste etc.; S212–216 | All seed data; **zero real residents, zero real incidents** |
| Commercial leases / rent roll | BUILT-UNPROVEN | `/admin/commercial-leases`, `commercial_leases`=0 | shops audited but never rented by system |
| Construction approvals / map NOC / excavation permits | BUILT-UNPROVEN | `construction_approvals`=0 | no buyer ever applied for a building permit |
| RWH / groundwater; PM Surya Ghar solar | BUILT-UNPROVEN | `customer_solar_requests`=0 | never used |
| Construction Work Orders / RA billing | BUILT-UNPROVEN | `construction_work_orders`=0, `contractor_bills`=0 | civil works billed in Excel |
| Demarcations / geotagging | PARTIAL | `plot_demarcations`=1 | only 1 plot ever surveyed via system |
| Project images / gallery | BUILT | S477–479; `project_images`; lightbox | never fed with real site photos by a buyer |
| CustomerGrievances / Society | BUILT-UNPROVEN | `township_resident_grievances`; customer portal page | 0 grievances filed (no residents yet) |
| SmartIoT desk | BUILT-UNPROVEN | `/admin/iot/alerts`; field ingest | devices not installed in live colony |
| Dynamic Pricing AI | BUILT-UNPROVEN | `DynamicPricingService`; PLC rules | never repriced a single live plot |

### Measurement vs reality (live DB, 2026-11-16)
| Metric | Live value | Claimed in docs/tests | Reading |
|---|---|---|---|
| `colonies.rera_number` not null | **0 / 8** | "RERA Approved" in marketing | fatal legal gap |
| `payments` | 4 (1 cancelled) | "payment gateway live" | test-mode only |
| `wallet_transactions` | 3 | "MLM payouts working" | no cash ever moved |
| `employee_attendance` | 0 | "HR system complete" | nobody ever marked in/out |
| `leave_applications` | 0 | "HR overhaul complete (S344)" | never used |
| `tds_register` | 0 | "TDS ₹16A ready" | statutory exposure |
| `gst_invoices` | 0 | "GST system complete" | no buyer ever billed |
| `plot_bookings.lead_id` not null | **0 / 23** | "CRM ✓ best-in-class" | zero ROI attribution |
| `possession_certificates` | 0 | "handover complete" | no customer ever took keys |
| `registry_appointments` | 0 | "registry desk complete" | no SRO visit ever logged |
| `document_esign` | 0 | "e-sign verified" | Leegality key empty |
| `user_wallets` | 0 (dead) | "dual wallet expected" | consolidate |
| `mlm_salary_grants` | 0 | "career salary plan live" | never activated |
| `site_visits` | 37 | "sales in-field live" | all seeds |
| `maintenance_dues` | 50 | "collection workflow" | probe leftovers |
| `leads` (live) | 994 | "1,000 leads managed" | source mix legitimate but unworked |
| `audit_logs` | 3,094 | — | the only thing genuinely running |
| `documents` | 27 | — | all synthetic |

---

## 3. Top 10 business gaps — ranked by impact (revenue/legal/operator risk × probability)

| # | Gap | Type | Impact (why) |
|---|-----|------|--------------|
| 1 | **No RERA registration for any of 8 colonies** | Legal | The entire product is unsellable legally in UP; RERA certificate is the precondition for every booking. Every sale closed without it is voidable, and every association into it is a criminal exposure of the directors. |
| 2 | **RZP live keys missing → zero money can be collected online** | Revenue/Platform | Razorpay `key_id/key_secret` empty; test_mode=1; webhook secret blank. Company has never cleared ₹1 online; bookings and EMI are cash/wire with no system trail. |
| 3 | **Not deployed to production** — APP_ENV=development, DEBUG=true, localhost URL, Play Console unsubmitted, secrets everywhere in AGENTS.md/DB | Operations | Zero customers can reach the product today without manipulation; public credibility zero; debug leaks stack traces; any data-entry risk increases. |
| 4 | **EMI & auto-debit never executed for any real buyer** | Cash flow | 276 schedule rows are synthetic; NACH mandate never registered; 0 actual debits attempted; collections are manual honor system; payment_risks on 20 pending-emi bookings = 100% unknown. |
| 5 | **Lead→booking attribution = 0** | Marketing ROI | 23 bookings, 994 leads, ₹0 ad-budget trackable to a closed deal — marketing is burning money without provable return; campaigns cannot be shut or doubled. |
| 6 | **MLM has paid 0 real commission; TDS/PF/ESI = 0** | Tax/Finance | 359 ledger rows contradict 0 real payouts (all seeds/probes) — the MLM promise on the marketing collateral is diluted by non-observance; statutory exposure grows with every rupee owed. |
| 7 | **Sales system never closed a real deal** | Business proof | 23 bookings (20 synthetic, 2 test, 1 admin); 1047 site visits, all seeds; no real buyer has used booking→registry→possession |
| 8 | **Payroll/attendance nonexistent in reality** | HR | 15 employees, with HR using company fingerprint machine; PF/ESI/tDS not being deducted/deposited; ₹ liabilities growing daily |
| 9 | **Possession/handover never practiced** | Customer experience | 0 possession certs, 0 meter installs, 0 resident registrations at a sold unit; no "after-sale" existence means no CX, no referrals, no reputation |
| 10 | **No offsite, rehearsed backup; no DR plan** | Continuity | `backups/db/` has many dumps, but restoring a live server mid-crisis (S223) was a fire drill that went bad once (1453 rows rewritten); single-box XAMPP = single point of failure for the entire company |

Honorable mentions (impact-bounded): business metrics dashboards (all live data shows as "empty" because usage doesn't exist yet) · broker network never activated (1/37 associates) · GST e-invoice · messaging downtime (SMTP blank) · associate onboarding flow unproven with a real joiner · mobile app not published — **nothing API-bound to Play Console yet**.

---

## 4. What the software needs next — ordered to run the company completely
### PHASE 0 — Gate to go live (legal + money + production), unblocks revenue
1. **Register colony licensing with UP RERA (all 8 colonies)** — furnish `rera_number` + RERA certificate per colony in `colony_rera`/colony columns; no API is missing, just the legal artifact. **Blocker for every sale.**
2. **Production deploy + domain** — move to a VPS (2 vCPU / 4 GB min), install Nginx+PHP8.x+MariaDB, flip `APP_ENV=production`, `APP_DEBUG=false`, point `APP_URL` to `https://apsdreamhome.com`, HTTPS TLS, run `scripts/setup_backup_task.ps1 promoted to cron`, verify SSL.
3. **Razorpay merchant onboarding** — KYC pan/bank/GST, fill `key_id/key_secret/webhook_secret`, set `test_mode=0`, register webhook, run one ₹1 smoke-capable live transaction with a ₹100 test customer purchase; document monthly settlement.
4. **Play Console publish** — AAB 1.2.10+13 (APK already built), screenshots, content rating, ₹1,500 fee; **90 seconds of upload + review**; your customers cannot install without this.
5. **Secrets rotation** — rotate Twilio, any `Enc` keys in DB or AGENTS.md; scrub `.env` from applog; rotate DB password; enable 2FA on owner accounts (code already exists: S344).
6. **Offsite backup rehearsed** — tar sql/code/logs → S3 (S3 is configured) + weekly restore drill on a staging box; document RTO/RPO; `docs/PRODUCTION_SETUP_GUIDE.md` finalization; monitoring (`health` endpoint) via UptimeRobot.
7. **E-invoice + GST sandbox** — GSTN/TIN/Leegality keys + one handshake; make first real B2B buyer invoice; validate TDS register prints 16A.

### PHASE 1 — First real customer journey (weeks 1–4)
8. **One real buyer walk-through** — pick one public lead (referral), book a site visit, send token ₹25k via Razorpay, generate receipt, agreement via Leegality, EMI schedule, passbook live, notify on every milestone, registry NOC, stamp duty, handover letter, possession cert with reserved fact — **every step with a human sign-off, then delete the seeds**.
9. **One real MLM record** — promote the fastest associate by ₹10L volume via wages of real sales; verify ledger→wallet→withdrawal→UTR bank transfer completes; pay 10% TDS; issue 16A; post accounting entry; reconcile by hand against bank statement.
10. **One real salary run** — mark attendance for 15 employees for one month, apply leave, process salary, push payslips, generate Form 16; deposit PF/ESI/TDS; file ECR return.
11. **Registry/Possession exercise** — execute one registered deed at SRO with this software as witness; scan registered deed into documents; issue possession NOC + certificate; hand over keys; collect maintenance dues month 1.

### PHASE 2 — Operational maturity (weeks 5–12)
12. **Redact test suite from production DB** — `TEST-HIST-*` bookings, test users, ₹25 Crore test payments: either archived or deleted; re-baseline `testing/master_test_runner.php` (currently 11/11 against seeds) – zero test artifacts left.
13. **Marketing automation lit up for real channels** — SMTP + WhatsApp + SMS API keys; one real campaign against one named township project; measure cost-per-lead & cost-per-closing; kill channels with <10% conversion.
14. **CRM discipline** — force associates to log every site visit / call / objection outcome; leadership dashboard: closing velocity, per-assnciate commission, funnel leak attribution; settle incentives from the system, not from Excel.
15. **Compliance calendar** — statutory calendar implementation (GST 11th of month, TDS 7th, ECR 15th, PF 25th, PATR 15th); automated reminders; SRO registry slot calendar by legal head.

### PHASE 3 — Scale
16. **Second colony launch** with this stack; **second channel** (NRI outreach); **associate recruitment** through app (pending: 1 real signup flow with KYC + agreement); **payroll expansion** (contractor muster, S203).
17. **Retire the parallel `user_wallets`** (0 rows, dead); consolidate `associates` legacy (37) → `associate_profiles` (5) with a real migration; retire dead `ai_calling_agents` for good; prune 1200 unused tables.
18. **Engineering hygiene** — 250 remaining sessions of drift (1,165 uncommitted files), contributor onboarding doc, rotated secrets, incident postmortems — never again a "full review done" commit with ambiguous state.

---

## 5. Assets worth keeping — audit results

| Asset | Why keep | Condition |
|---|---|---|
| `testing/master_test_runner.php` (11/11 green) | Best-in-class CI discipline; keeps every change honest | Must be re-baselined to **real schema, zero test seeds** |
| `scripts/scan_schema_mismatches.php` + ratchet baseline | Prevents silent 500s; objective measure of drift | Keep threshold at 0 NEW mismatches |
| RBAC middleware chain (S463/471) | Properly closes CSRF-via-GET; **security-by-default** | Done — keep the router sync, not reinvent |
| `WalletService` transaction model (FOR-UPDATE, fail-closed, ledger-only) | Correct money architecture | Extend pattern to commission debit + bank withdrawal |
| `MLMCommissionEngine` differential payout verified (S455 test doc) | Handles 3-tier hierarchy dynamically; honors caps & cross-team isolation | Re-validate on first live distribution |
| `book_payment_schedules` + `installments` UI for customer portal | Real product journey for EMI | Not yet seeded with a real buyer EMI row |
| Land link desk — parcel↔colony↔plots — `/admin/land-inventory/plot-gata-link` | First-of-its-kind title-ambiguity tracker | Empty of live records |
| `docs/MLM_SYSTEM_COMPLETE_TECHNICAL_BLUEPRINT.md`, `LAND_ACQUISITION_PROCESS_ANALYSIS.md`, `COMMISSION_BREAKDOWN_1LAKH.md` | Business domain model encoding that took months | Keep; convert to training manuals |
| Mobile Flutter app (912 tests passing in S455, Analyze clean) | Customer/associate face; published to download folder | Blocked on Play Console publish |
| Backup scripts + honest session log `AGENTS.md` | Unique accountability culture | Enforce "verify, don't believe" beyond the code |
| `scripts/verify_agents_log.php` + session numbers | Matches rigor of a compliance org | Keep |
| Festival Offers / Promo Code engine (S462) | Genuinely usable growth entitlement on customer bookings | Needs one real festival redemption |

**Do not keep:** `user_wallets` (0 rows, dead) · `ai_calling_agents` (deleted concept, keep nothing) · 17 duplicate-user/seed artifacts · 37k unattributed leads · 204 orphan EMI schedules (already deleted) · the 5-parallel-actor workflow (governance problem, not a code problem).

---

## 5b. Risk register — what breaks at go-live (one line each)
1. **UP RERA complaint filed by an unhappy buyer** → show all colonies w/o `rera_number` + no sale agreement executed → director-level culpability.
2. **First live payment settlement fails** because Razorpay webhook secret wrong → buyer's money captured, internal ledger never updateable → dispute.
3. **First real EMI missed** and customer reports "no demand letter was ever sent" → loss of recovery remedy (no notice = no legal right to interest).
4. **First commission withdrawal bounces** (bank KYC incomplete) → trust collapse in associate network → MLM churn.
5. **First possession handover holds keys but has no signed-deed copy** in locker → title suit in 6 months.
6. **PF/ESIC audit** finds 15 employees never registered → ₹ liability + penalty + personal director liability.
7. **Data leak via debug pages** left reachable under `APP_DEBUG=true` (e.g. `.env.backup`, phpinfo) → credential disclosure.
8. **Single XAMPP box fails** mid-month → payroll cannot run, bookings cannot be written → one day to full chaos.
9. **First customer grievance** arrives and there is no resident-address dossier (documents table synthetic) → reputation loss #1.
10. **Play Console review rejects the 1.2.10+13 upload** (privacy policy URL missing / data safety form blank) → 2-week delay.
11. **Restore catastrophe again** (S223 pattern: tables dropped) → entire company frozen while dump imported — not a theoretical risk, it has happened once.
12. **Associate files for wage commissions for 3 years retroactively** — commission engine has no immutable audit, can be re-run and re-triggered; must lock ledger before first payout.
13. **GST return defaults** — no invoice series registered with GSTN; first month return = zero filed + notice.
14. **Home/colony README exposed** under `docs/STATUS_REPORT.md` contains internal speculation & paths — pre-deploy scrub.
15. **App store shows app as "testing/temporary"** — unless AAB submission is actually completed.

---

## 6. Brutal bottom line
- This is **the best-engineered, least-operated company stack I have audited.** Engineering quality ≠ business health.
- Every flow ends at "the code works, but the world does not use it." Payments=4, attendance=0, RERA=0, EMI=0-seen, wallet-payouts=0-real, possession=0-ever.
- A single brave Phase 0 + one real buyer + one real salary run + one real RERA application will tell you more about this company than the next 150 sessions of 500-sweeps.
- **If the CEO wants this to go from prototype to business, fund Phase 0/1 before any more features.** Funding a new module before the MLM commission is paid is malpractice.
- **Track record of this team:** 1509 PHPStan-clean, 1133 tables, 2005 route sweep, 1990 GET routes, parallel agents, 43 GB disk reclaimed — these are feats of engineering strength applied to a product with **zero live customers.** The gap is not code. The gap is the world.
