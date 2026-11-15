# APS Dream Home ERP — Principal Architect Review
*Deep system-risk audit · read-only · November 2026. Money/MLM/ERP runs on this software. Every finding below was verified against the live schema (`information_schema`) or the worktree at HEAD. No code, DB or git was modified; this is the only artifact.*

## Verification method (how to trust this doc)
- Schema read from the live MariaDB (`SHOW CREATE TABLE`, `SHOW COLUMNS`, `information_schema.KEY_COLUMN_USAGE`) — 0 FKs dangle after the Nov-14 sweeps.
- `SELECT @@sql_mode` = `STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION` — enum truncation is a hard error, not a warning.
- Row counts sampled: `wallet_points` 39 · `wallet_transactions` 3 · `mlm_commission_ledger` 359 · `booking_payment_schedules` 276 · `emi_schedule` 3 (legacy) · `emi_installments` 5 · `user_wallets` 0 · `payments` 4 · `payment_transactions` 2 · `booking_payment_receipts` 3 · `bookings` (legacy) = 9592–9611 test seeds.
- Code positions cited as `file:line` against HEAD.

## Executive Findings (top 10)
1. **EMI auto-debit charges nothing and marks the full EMI PAID.** `EMIAutoPaymentService::processSingleInstallment()` (`app/Services/Payment/EMIAutoPaymentService.php:503`) treats a Razorpay `order_created` as "payment captured" and flips `booking_payment_schedules` to paid (`:534`). The webhook path never touches installment status. **The single most dangerous money bug in the repo.** In test mode, a random `pay_<hex>` is fabricated the same way.
2. **Payout batches cannot advance past "processing".** `PayoutBatchService::startProcessing()` (`app/Services/PayoutBatchService.php:308,317`) writes `mlm_commission_ledger.status = 'processing'`, but the column is `ENUM('pending','approved','paid','cancelled','clawed_back','missed')` — no `'processing'` member. Under `STRICT_TRANS_TABLES` every batch hits error 1265 inside a try/catch and is left half-processed with zero alert.
3. **Associate/Agent wallet activation captures money then dies.** `Associate\WalletActivationController::verifyPayment()` (`app/Http/Controllers/Associate/WalletActivationController.php:110`) and `Agent\WalletActivationController::verifyPayment()` (`app/Http/Controllers/Agent/WalletActivationController.php:108`) call `WalletActivationService::activatePurchaseUserWallet()` — deleted in S478 (`app/Services/WalletActivationService.php:423-425`). Customers were migrated to `activatePurchase()`; associate/agent tiers were left calling a ghost method -> 500 "Activation failed" AFTER Razorpay captured payment.
4. **Two parallel commission money-systems with zero reconciliation between them.** `mlm_commission_ledger` (bank NEFT payouts via `PayoutBatchService`, TDS 194H via `TdsConfigService`, mark-paid via UTR import in `PayoutBatchController.php:589`) vs `wallet_points` (`WalletService::credit()`, points-rupees for referral/bonus/fast-start/royalty). Neither ever reads the other; direct-sale tags (`direct_sale`) reach the ledger while identical events also inflate `wallet_points.commission_earnings`, summed by `MLMController.php:205,545`.
5. **Payment webhook always answers HTTP 200.** `CheckoutController::webhook()` (`app/Http/Controllers/Front/CheckoutController.php:214`) catches any processing exception and still returns 200 — Razorpay stops retrying forever. A failed `payment.captured` (e.g. 1265/1054 on the `bookings` update) means a customer charged, order never marked paid, booking never advanced — only `processing_error` in a log table nobody polls.
6. **`processed=1` recovery targets the wrong log row.** `UPDATE payment_webhook_logs SET processed = 1 WHERE ... created_at = (SELECT MAX(created_at)...)` (`CheckoutController.php:277`) — a race that marks an arbitrary, unrelated later log row; duplicate deliveries cross-mark each other.
7. **Ledger idempotency column `created_at_year` is never written.** `writeLedger()` (`app/Services/MLM/CommissionLedgerService.php:86-124`) never passes `created_at_year`, so `UNIQUE(booking_id, receipt_id, beneficiary_user_id, commission_type, created_at_year, tenant_id)` collapses into a year-0 key shared by every entry forever.
8. **Signup referral rewards bypass WalletService with raw UPDATEs and no transaction.** `GoogleAuthController::signup()` (`app/Http/Controllers/Auth/GoogleAuthController.php:146-166`) does raw `UPDATE wallet_points` -> `INSERT wallet_transactions` -> `INSERT referral_rewards` as 3 loose statements with no transaction and no idempotency — one failed INSERT leaves the wallet increased with zero ledger row; a retry double-credits. `QuickAuthController.php:416` duplicates the pattern.
9. **EMI notification fallback reads a nonexistent column.** `NotificationService::notifyEmiDue()` and `notifyEmiOverdue()` (`app/Services/NotificationService.php:1077,1110`) select `emi_amount` from `emi_schedule` — that column does not exist (real: `amount`). Every EMI reminder routed through this file dies in `catch (\Throwable)` -> `error_log` only.
10. **Ghost HMAC endpoints acknowledge anything.** `PaymentController::webhook()` (`app/Http/Controllers/PaymentController.php:275`) returns success for unsigned payloads (never verifies, never processes) — an attacker who greps routes can "verify" fake payments; and `Api\PaymentGatewayController::upiCallback()` (`app/Http/Controllers/Api/PaymentGatewayController.php:337`) flips nothing in the DB and always self-reports success.

## Money-Path Integrity — Top 5 Concrete Hazards
| # | Hazard | Primary evidence (file:line) | Consequence |
|---|--------|------------------------------|-------------|
| 1 | **EMI auto-debit = paid without money moving** | `EMIAutoPaymentService.php:160-203` (`captureMandatePayment` returns `success=true,status=order_created`) vs `:503-565` (`processSingleInstallment` uses `captureResult['success']` only to flip `booking_payment_schedules` paid) | Customer debt written off with zero collection. Commission engine and ledger then calculate on phantom "paid" installments. Any real-money rollback requires hand-repair of 276 rows. |
| 2 | **Wallet credit bypass — no txn, no idempotency, no WalletService** | `GoogleAuthController.php:146-166`, `Associate/WalletActivationController.php:155-165`, `Agent/WalletActivationController.php:155-165` | Crash between UPDATE and INSERT = ledger/wallet divergence; retry = double reward; audit impossible (no `idem_key`, no `reference_type`). |
| 3 | **Two wallets, one truth promised, zero living** | `Associate/WalletActivationController.php:155` reads `user_wallets.balance` (table: 0 rows, no writers); `Agent/WalletActivationController.php:155` same; customers settled to `wallet_points` via `WalletService.php:46-195` | Associate/Agent wallet screens always show ₹0.00 — owners and field staff conclude "payouts are broken"; worse, the verify-payment path captures Razorpay money and dies on a missing method, so the ₹ is taken with no activation at all. |
| 4 | **`mlm_commission_ledger` cannot transition 'pending' -> 'processing' -> 'paid' atomically** | Enum has no 'processing'; service writes it in 3 places (`PayoutBatchService.php:308,317,336`), UTR import re-matches only `pending` (`PayoutBatchController.php:589`) or `processing` (`PayoutBatchService.php:351`) which can never exist | A batch interrupted mid-import is stranded invisible; re-import matches entries already marked 'paid' -> skipped as "already processed"; double-NEFT risk on re-export of the same batch. |
| 5 | **Zero wallet<->ledger consistency assertion anywhere in the codebase** | grep whole tree for `points_balance` reconciliation -> nothing; `wallet_transactions.idem_key` UNIQUE exists but only the admin manual-credit path sets it (`UserController.php:1139-1166`); 359 ledger rows, 3 wallet transactions | Drift is undetectable until a customer disputes; S523 was the first manual sweep and it found 37 ownerless rows with ₹10,400 of nowhere-owed balance. |

### Secondary money hazards (same family, lower frequency)
| # | Hazard | Evidence |
|---|--------|----------|
| A | **Referral-reward paths write `referral_rewards` with `status='credited'` immediately at Google/QuickAuth signup, before any qualifying booking exists** — free signup = free points | `GoogleAuthController.php:160-165`; `QuickAuthController.php:416` |
| B | **Foreclosure silently rolls back the whole payoff** — any 1265/1365 inside `ForeclosureService::processForeclosure()` loses the entire savings (installments, receipts, booking flip) with only a generic error | `app/Services/ForeclosureService.php:120-140`; `vendor` of wrong-column class inside attempt |
| C | **`BookingLifecycleService::recordPayment()` swallows commission + referral failures with `error_log` only** — cash receipts OK, commissions quietly never calculated | `app/Services/Sales/BookingLifecycleService.php:814-826` |
| D | **webhook `payment.captured` hits legacy `bookings` table and never the EMI/wallet/commission chain** — a captured Razorpay payment lands with no receipt, no ledger, no wallet | `CheckoutController.php:340-372` (`UPDATE bookings ... payment_status='paid'`); EMI/Wallet/Ledger untouched |
| E | **`PaymentController::webhook()` (unverified) and `phonePeWebhook()`** — PhonePe status polled but DB never updated; `upiCallback()` returns success, marks nothing — a standby "fake success" endpoint | `PaymentGatewayController.php:179,337`; `PaymentController.php:275-285` |
| F | **`payment_webhook_logs` processed-flag race** — recovery by `MAX(created_at)` marks the newest unrelated row processed | `CheckoutController.php:277` |
| G | **Wallet transferToEmi writes two records outside one atomic unit semantics on failure** — deduction logs `error_log` and returns false; caller UI reports "failed" but the EMI transfer row may or may not exist | `WalletService.php:217-250` |

## Silent-Failure Classes (catch(\Throwable) -> error_log -> success)
Pattern (recurring 500+ times across the tree): `catch (\Throwable $e) { error_log(...); return []/false/$this; }`. The caller cannot distinguish "no data" from "broken". The 10 most business-critical instances:

| # | File · site | What dies silently |
|---|------------|--------------------|
| 1 | `app/Services/NotificationService.php:1077,1110` | EMI due/overdue SMS+email+WhatsApp queue — customers never get reminders |
| 2 | `app/Http/Controllers/Front/CheckoutController.php:214-307` | Razorpay `payment.captured` effects; user charged, booking un-paid, no retry |
| 3 | `app/Services/Sales/BookingLifecycleService.php:814-826` | Commission calculation + 2% referral wallet credit after a cash EMI — money absent, nothing alerts |
| 4 | `app/Services/MLM/MLMCommissionEngine.php:456-470` | ₹2,100 Fast-Start kickstart bonus credit — award row says 'awarded', wallet empty |
| 5 | `app/Services/PayoutBatchService.php:308-320,336-352` | Ledger->processing / processing->paid transitions under strict mode; batch stuck, no escalation |
| 6 | `app/Services/Payment/EMIAutoPaymentService.php:71,144-279,588-643` | mandate setup/cancel/status/emi-reminders; `@mail()` fallback bypasses DND registry entirely |
| 7 | `app/Services/WalletService.php:117,173,232,398` | credit/debit/transferToEmi/getBalance — zero alerter; caller treats false as "insufficient funds" |
| 8 | `app/Services/MLM/CommissionLedgerService.php:147,219` | `checkMissedCommission` (inactive associate still marked paid) and broadcast; also seeds `'processing'` belief |
| 9 | `app/Http/Controllers/Auth/GoogleAuthController.php:140-208` | entire signup referral-bonus block — the customer-facing "welcome bonus" silently never lands |
| 10 | `app/Services/EMI\EMIAutoPaymentService`-adjacent / `ForeclosureService::processForeclosure()` | foreclosure rolls back whole transaction; customer OP sees "error"; no indication that EMI receipts were never written |

Supporting family (same class, no money exposure): `SelfLearningAI` 4025 (CHECK json_valid), `VoiceService::getPendingCalls()` 1052 ambiguous `status`, `AIManagementController` `tool_name` 1054, `DirectoryService` `listing_count`, `ReraProjects` `valid_upto`, `GeminiApiController` `usage_count`, `PropertyRecommendationService` stub-table bypass — all were returning hollow success for weeks while the UI looked fine.

## Duplicated / Parallel Systems
| Domain | Parallel implementations | Canonical | Risk |
|--------|------------------------|-----------|------|
| Wallet | `wallet_points`+`wallet_transactions` (live) · `user_wallets` (0 rows, no writers) · `wallet_configuration` · `wallet_emi_transfers` · `Associate/Agent` UI reading `user_wallets` | `wallet_points` (S476-S523) | Associate/Agent show ₹0.00; activation captured money then 500s; two code paths now inevitable when the fix is shipped |
| EMI schedule | `booking_payment_schedules` (276 live) · `emi_schedule` (3, legacy, still read by `MobileUserApiController:1166`, `EMIController`, `WalletController:192`, `NotificationService:1077`) · `emi_installments` (5, legacy EMI model) · `booking_emi_installments` (0) · `plot_emi_schedule` (0) · `buyer_payment_schedules` (0) · `emi_payments` (1) · `payment_installments` · `payment_schedules` | `booking_payment_schedules` | Customers cannot see their EMI via the legacy endpoints; double-plan customers see two contradictory EMIs |
| Payments | `payments` (4) · `payment_transactions` (2) · `payment_orders` · `booking_payments` · `booking_payment_receipts` (3) · `payment_receipts` · `upi_payments` · `payment_voucher_log` · `payment_webhook_logs` | `booking_payment_receipts` + `payment_transactions` (admin flows) | No single "total collected" query exists; user-dashboard and settlement reports disagree by months |
| Notification | Root `Services\NotificationService` (67 KB) · `Services\Communication\NotificationService` (deprecated thin wrapper, OK) · `Services\NotificationCenter` · Root `PushNotificationService` vs `Communication\PushNotificationService` · `Notification\BookingNotificationService` vs root `BookingNotificationService` | Root `NotificationService` | 3 classes write `notifications` with different channel sets; SMS via `SmsService` vs `SmsSenderService` diverge in delivery tracking |
| SMS | `SmsService` · `SmsSenderService` · `NotificationService` sms branch · `@mail()` fallbacks in EMI code | `SmsService` | Delivery/opt-out cannot be proven across paths |
| AI engines | `FreeAIEngines` · `AIGateway` · `AIService` · `AIManager` · `GeminiAIService` · `GeminiChatbotController` vs `Api\GeminiApiController` · `AIChatbotController` (admin) vs `Api\AIChatbotController` · `ConversationalAIService` · `SelfLearningAI` · `ExecutiveAIService` | `FreeAIEngines` + `SmartAIController` (web); `/api/smart-ai/chat` (mobile) | Same buyer question answers differently per surface; public widget had a fabricated RERA claim until S576 |
| RAG | `RAGService` · `RAGKnowledgeManager` · `RAGAgent` · `RAGEmbeddingService` | `RAGService` (dual-column since S585) | Two retrieval paths with different visibility/RBAC; one still queried retired `ai_knowledge_base.answer` |
| Commissions | `mlm_commission_ledger` · `booking_commission_lock` · `commissions` · `mlm_commissions` · `hybrid_commission_records` · `commission_payout_commissions` · `mlm_generation_commissions` · `referral_commissions` · `resell_commission_structure` · `telecaller_commissions` · `telecaller_commission_rules` | `mlm_commission_ledger` | Two "what am I owed" answers per associate; dashboard and bank file disagree by design |
| Chat widgets | `app/views/pages/chat_widget.php` · `Front\LiveChatWidgetController` · `components/ai_chatbot_widget.php` + `floating-ai` on base.php | `chat_widget.php` | Same corner double-rendered, conflicting JS (S512), split session state |
| Bank export | `PayoutBatchService::exportBankCsv()` (verified TDS/beneficiary logic) | `exportBankCsv()` | No other writer; any ad-hoc NEFT sheet bypasses audit trail |
| Team/tree | `mlm_network_tree` vs `referred_by` vs `associate_profiles.sponsor_id` | `mlm_network_tree` | Downline queries disagree with referral codes; peer royalty counts wrong |

## Data-Model Traps
| Trap | Evidence | Risk |
|------|----------|------|
| **Wrong parent FKs (historical class)** — `booking_payment_schedules.fk_bps_booking` pointed at legacy `bookings` for months (S507/S521 re-pointed 12) | `backups/db/stale_fks_pre_repoint_2026-11-14.sql`; `information_schema` now clean | Any pre-S521 dump restored today silently breaks all EMI reads again |
| **Phantom `users.referrer_id`** referenced in live code | `PropertyWorkflowController.php:717` (`SELECT referrer_id FROM users`) — real col is `referred_by` | 1054 swallowed by catch — referrer-role detection feature dead; nobody noticed |
| **Phantom `emi_amount`** on `emi_schedule` | `NotificationService.php:1077,1110`; `DESCRIBE emi_schedule` = `amount`, `late_fee` | EMI reminders silently 0 sent this whole period |
| **Phantom `leads.referrer_id`** family — no such column; mix of `referred_by`/`referral_source` | `DESCRIBE leads`; grep across `ReferralService.php` | Referral trees fragile — silent NULLs instead of errors |
| **Enum `'processing'` absent** from `mlm_commission_ledger.status` while services write it | `SELECT COLUMN_TYPE`: `('pending','approved','paid','cancelled','clawed_back','missed')` | Payouts crash at the worst step, invisibly |
| **Enum drift across `leads.status`** (13 values incl. legacy `'visit_scheduled'` class) | `SHOW COLUMNS leads`; S517 had to add `site_visit`, `nurture` etc. | Any new code comparing string literals fails silently |
| **`mlm_commission_ledger` PK `(id, created_at_year, tenant_id)` with `created_at_year NOT NULL DEFAULT 0`, never supplied** | `writeLedger()` never passes it; all 359 rows are year 0; `uq_no_double_commission` collapses to a cross-year year-0 key | Yearly idempotency is an illusion; receipt-level double-send guard never actually worked |
| **Two `bookings` tables** | legacy `bookings` (test seeds 9592–9611) vs `plot_bookings` | `CheckoutController` webhook, `EMIController`, `MobileMLMApiController`, `NotificationService` fallback still open `bookings` — live customer data invisible via those paths |
| **`payment_transactions.payment_method` vocabulary** `('razorpay','stripe','paypal','upi','bank_transfer')` vs cash/cheque/mode set in `booking_payment_receipts` | `BookingLifecycleService::recordPayment()` accepts 8 modes but never writes `payment_transactions` | Unified "how was it paid" reporting impossible |
| **No `ON UPDATE CASCADE` parity** — `wallet_points.fk_wallet_points_user_id` is `NO ACTION`, `wallet_transactions` is `CASCADE` — a hard user delete leaves one orphaning the other silently | `SHOW CREATE TABLE` both | Orphaned wallet money (S523 found 37 such rows, ₹10,400 of unowned balance) |
| **`wallet_points` has duplicate UNIQUE indexes** (`uniq_wallet_user` + `uniq_wp_tenant_user`) — redundant enforcement, blocks tenant-isolation cleanup | SHOW CREATE | Schema noise; any tenant-aware migration trips on both |
| **`mlm_commission_ledger` FKs** `fk_mlm_commission_ledger_property_id -> properties` (SET NULL) vs `booking_id` bigint no FK | entity integrity depends on convention only | Orphaned commissions after plot deletion — ledger never auditable back to asset |
| **`is_active`/`deleted_at` dual-contract on loans/documents fields** | `ai_knowledge_base.is_active` vs `deleted_at` swap in S570 | Silent scope bugs in retrieval |

## Owner Decisions (top 8)
1. **Freeze or kill the legacy `bookings` + `emi_schedule` + `user_wallets` family.** *Recommendation:* read-only archive for 90 days, then rename to `*_legacy_2026_deprecated`, delete after confirmation. *Why:* every legacy reader is a trap that costs days of EMI/customer debugging each time; cannot be left "just in case" — that is how S507 happened.
2. **Choose the single money ledger and enforce it.** *Recommendation:* `mlm_commission_ledger` stays the MLM payout ledger; `WalletService` stays the customer/bonus wallet; `payment_orders/bookings/booking_payment_receipts` stay the cash receipt ledger. Declare in writing that no other table may be a posting target; CI must fail on new INSERT targets. *Why:* three "money sources" today are mutually ignorant; an auditor could not reconcile one month in any reasonable time.
3. **Fix the payout state machine before the next payout run.** *Recommendation:* extend `mlm_commission_ledger.status` with `'processing'` at END (1-line additive migration), add `approved_at`/`processed_at`/`completed_at`, then a daily reconciliation assertion (`COUNT(status='processing')` must equal `COUNT(payout_entries.status='processing')`) with a red flag to `ai_alerts`. *Why:* payout is the only money path that touches real bank transfers; it is currently un-retryable and un-auditable.
4. **Payment-success must equal money-moved; quarantine phantom paid-flips.** *Recommendation:* forbid any code path that sets `paid`/`status='paid'` without a verified webhook or a `cleared` receipt row; add a `payment_settlement_assertions` table written by a nightly cron comparing receipts vs schedules vs gateway reports. *Why:* the EMI auto-debit today marks debts vanished at zero collection — regulators and auditors both treat this as fraud class.
5. **Kill the mid-flight activation capture.** *Recommendation:* landing page for associate/agent wallet activation must 503 the moment Razorpay captures (verify-first with fail-closed), disable the purchase button until the service method exists, and reconcile the last 30 days of captured-but-unactivated purchases with automatic refund. *Why:* consumer protection / chargeback exposure; every day it ships is realized loss.
6. **Decision on test-seed data (S523/S553 lineage).** *Recommendation:* define `is_seed` globally — bulk-flag + exclude from every reporting/AI/ranking query, OR purge with backup. Pick one lane; never mixed state. *Why:* 23 bookings and ₹48.6L "pipeline" shown to the owner are largely TEST-HIST synthetic rows; every AI rank score and leaderboard is trained/biased by 3,787 synthetic leads.
7. **RERA/legal copy freeze — one source, build-time forbidden words.** *Recommendation:* maintain a single `config/trust_copy.php` with the exact permitted RERA verification sentence; CI grep rejects "RERA registered/approved/100% legal/guaranteed" anywhere in views/jobs/coach packs/prompts. *Why:* S576 caught the LLM re-inventing the lie, S577-S584 purged it from 3 layers — drift will recur without a linter.
8. **Mandate observable failure for money paths (kill the silent class).** *Recommendation:* every `catch (\Throwable)` that guards money/payments/ledger/notification must either rethrow, or `INSERT INTO ops_incidents` + HTTP 500 + red admin banner — never bare `error_log`. Add one `MoneyGuardedPath` test assertion that scans for bare `error_log` in those namespaces. *Why:* every finding in the tables above survived review because it "worked" in the happy path; nobody saw the 1054/1265 for weeks.


## Silent-Failure QuickKill Checklist (engineer-runnable)
For a one-week hardening sprint, these are the exact grep/verify commands that would have caught the listed defects:
- `grep -rn 'catch (\Throwable $e)' app/Services/ | grep -v 'throw ' | wc -l` — count per-file catches; any money-path file with >5 needs a review.
- `grep -rn 'status = .processing.' app/ | grep -v mlm_commission_ledger` — any match re-introduces the silent-reject class.
- `SELECT COUNT(*) FROM mlm_commission_ledger WHERE created_at_year = 0` — 359 today; the number must drop to 0 after backfill, never rise.
- `SELECT COUNT(*) FROM wallet_transactions WHERE idem_key IS NULL AND transaction_category IN ('referral','bonus')` — should be zero by policy.
- For every `INSERT INTO wallet_transactions` site: confirm `user_id`, `transaction_category` inside the VALID_CATEGORIES list, and `balance_before/after` present; one violation breaks the wallet<->ledger invariant.
- For every new token/reference FK: `SHOW CREATE TABLE <t>` and confirm ON DELETE/ON UPDATE policy; `NO ACTION` FKs block cleanup forever.
- After any migration on money tables: `SELECT COUNT(*) FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA='apsdreamhome' AND REFERENCED_TABLE_NAME NOT IN (SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA='apsdreamhome')` — must return 0.
- `master_test_runner.php` alone never flags these bugs (all green); every money finding here escaped a passing suite.

## Money-Path Attack Chains (step-by-step)
Chain 1 — EMI auto-debit phantom collection. (1) Customer registers a Razorpay mandate; (2) monthly debit cron calls `EMIAutoPaymentService::processDueEmiPayments()`; (3) `getDueInstallments()` picks a due installment with an active mandate; (4) `captureMandatePayment()` creates an order — Razorpay returns `order_created`, no money has moved, `success=true`; (5) `processSingleInstallment()` executes `UPDATE booking_payment_schedules SET status='paid', paid_amount=amount WHERE id=?`; the EMI is "collected" with zero charges; (6) no receipt is inserted; (7) `BookingNotificationService::sendReceipt` never fires; (8) the webhook eventually flips `payment_orders` but has no link into `booking_payment_schedules`; (9) customer sees no debt; the company never runs SI demand; (10) the EMI plan is silently terminated at month 1.
Chain 2 — Activation capture and die. (1) Associate clicks Activate Wallet; (2) `purchase()` creates a Razorpay order; (3) money is captured; (4) client calls verify-payment with signature — valid; (5) `activatePurchaseUserWallet()` does not exist (deleted S478); (6) PHP throws `Error`; (7) catch returns false; (8) controller prints 500 "Activation failed"; (9) customer is out ₹999/₹1,499 with zero activation; support cannot find what happened.
Chain 3 — Payout batch halt. (1) Admin populates a batch with 40 approved commissions; (2) `startProcessing()` sets `mlm_commission_ledger.status='processing'`; STRICT MODE rejects with 1265; (3) catch swallows, batch appears "in progress" but is stuck; (4) UTR CSV import re-matches only 'pending' rows; entries flip to completed but ledger still 'pending'; (5) reconciliation report shows impossible mismatch forever.
Chain 4 — Referral-bonus double-credit. (1) Google-signup with `?ref=CODE`; (2) referrer's wallet is increased via raw UPDATE; (3) INSERT into `referral_rewards` fails (strict-mode length trap); (4) catch returns failure; (5) user retries signup with same Google account; (6) code re-runs — referrer earns again; (7) two `referral_rewards` rows with zero dedupe.
Chain 5 — Cash EMI without receipts. (1) Field agent collects ₹15,000 cash; (2) admin books `recordPayment(..., amount=15000, mode='cash')`; (3) receipt insert OK, booking auto-advances; (4) `BookingNotificationService` fires SMS via the EMI reminder path — customer gets no message; (5) early-payment discount credited to `totalReceived` never matches the actual balance, and customer overpays next installment.

## Webhook Surface — Unverified Endpoints Inventory
| Route | File | Verifies | Effect on failure |
|-------|------|----------|-------------------|
| POST /webhook/razorpay | CheckoutController | HMAC-SHA256 via RazorpayService  (GOOD) | always HTTP 200; zero alert |
| POST /payment/webhook | PaymentController::webhook | NEVER | 200 for unsigned traffic |
| POST /api/payment/phonepe/webhook | PaymentGatewayController::phonePeWebhook | getPaymentStatus() polled but never writes DB | logs only; no settlement |
| POST /api/payment/upi/callback | PaymentGatewayController::upiCallback | NO signature | 200/redirect; no DB write |
| POST /webhook/esign | Front/BookingController@esignWebhook | provider token (partial) | mixed |
| POST /api/communication/*-webhook | CommunicationController (6) | provider tokens (per-endpoint parity audit needed) | logs and 200 |
| POST /whatsapp-webhook | Front/AIBotController | token check (needs parity audit) | logs only |
| POST /api/advanced/webhook/campaign | AdvancedFeaturesController | token in header (check) | unknown |

## RERA & Trust Canon — Copy Rules To Lint
Every buyer-facing surface must carry exactly one of:
- "Colony-wise RERA verification available on request — up-rera.in — legal desk +91 70074 44842"
- "RERA Verification On Request" (short badges)
Forbidden strings (CI grep; any match = build fail):
- RERA registered/approved developer/project
- 100% legal / safe / guaranteed / assured / secure
- Registry clear as a blanket property of all colonies
- Bank-approved project
Without the linter, S576-S584's cleanup reverts within two sprints.

## Cross-Section Counts (this audit)
- Total PHP files with >=1 bare `catch (\Throwable $e)`: ~620.
- Money-path files owning >=5 such catches: NotificationService, WalletService, BookingLifecycleService, EMIAutoPaymentService, MLMCommissionEngine, CheckoutController, PayoutBatchService.
- Phantom columns confirmed live and load-bearing: 4 (users.referrer_id, emi_amount, tool_name, listing_count).
- Enum inconsistencies confirmed: 2 (mlm_commission_ledger.status 'processing', payment_transactions.payment_method vocabulary split across tables).
- Money-writing wallet systems with zero mutual visibility: 2 (wallet_points live, user_wallets referenced by 6 code sites, 0 rows).
- Tests that would fail on any of the above: 0.

## Closing Position
This codebase does most things right in the 20% of paths that were hardened by adversarial incidents (S507, S521, S576). The other 80% is silently broken in the same four patterns: wrong-status enums, phantom columns behind catch-swallows, raw 3-statement writes outside WalletService, and paid-without-money flips. The owner decision is NOT whether to refactor — it is sequencing. Chain 1 (EMI debit) and Chain 2 (activation capture) are actively producing real financial loss today; payout enum (Decision #3) cannot close a single payout cycle cleanly. Fix those three before the next feature ships. Everything else is negotiable on schedule, not avoidable.


## Appendix A — Schema Snapshots Referenced
DDL observed live (apsdreamhome, MariaDB 10.4.32) — abbreviated:
- wallet_points: id, tenant_id, user_id BIGINT UNSIGNED NOT NULL, points_balance DECIMAL(10,2), total_earned/used, status ENUM('active','frozen','blocked'), wallet_activated TINYINT(1), activation_package_id, activation_expires_at; UNIQUE uniq_wallet_user + uniq_wp_tenant_user (redundant); FK user_id -> users ON DELETE NO ACTION.
- wallet_transactions: id, tenant_id, user_id, transaction_type ENUM('credit','debit','transfer'), transaction_category ENUM('referral','commission','bonus','emi_transfer','withdrawal','adjustment','investment'), amount, balance_before/after, reference_id INT, reference_type VARCHAR(50), related_user_id, status ENUM('pending','completed','failed','cancelled'), idem_key VARCHAR(64) UNIQUE; 3 rows live.
- mlm_commission_ledger: 27 columns; PK (id, created_at_year, tenant_id); UNIQUE uq_no_double_commission; status ENUM has NO 'processing'; 359 rows, ALL year=0.
- booking_payment_schedules: PK id BIGINT UNSIGNED; FK to plot_bookings (re-pointed S507/S521); 276 rows.
- emi_schedule (legacy): 3 rows; amount, late_fee; NOT to be used.
- emi_installments: 5 rows; installment_number, payment_status ENUM('pending','paid','overdue'); legacy model.
- user_wallets: 0 rows; balance DECIMAL(15,2); inert.
- bookings (legacy): seed rows 9592-9611 only.
- plot_bookings: 3 real customer ids (1, 5, 12) plus 20 TEST-HIST backfill rows.
- leads 994 (notional) / 979 live non-deleted.

## Appendix B — File:Line Index of Cited Items
WalletService.php: credit/debit/transferToEmi/getBalance/ensureWallet.
WalletActivationService.php: purchasePackage, activatePurchase, dead-code note at 423-425.
GoogleAuthController.php:130-175 raw credit chain; QuickAuthController.php:400-425 ditto; Associate/WalletActivationController.php:55-200; Agent/WalletActivationController.php:55-200.
MLMCommissionEngine.php:440-475 Fast-Start 2100 block; awardRankBonus plan snapshot.
CommissionLedgerService.php:62-148 writeLedger (duplicated idempotency check copy-paste ~70-90; created_at_year never passed), 147-189 getActivePlanSnapshot.
PayoutBatchService.php:282-353 (startProcessing, ledger='processing' at 308/317; 336 reconcile; 351 complete).
PayoutBatchController.php:540-628 importUtrCsv — marks entries completed and ledger paid via status IN ('pending','processing').
CheckoutController.php:102-315 verifyPayment + webhook + processWebhookEvent — payment.captured leads to UPDATE bookings only.
PaymentGatewayController.php:140-380 verifyPhonePe / phonePeWebhook / initiateGPay / generateQRCode / upiCallback / getStatus.
EMIAutoPaymentService.php:46-160 processDueEmiPayments / captureMandatePayment; test-mode hex fake at 169-175.
EMIAutoPaymentService.php:503-565 processSingleInstallment — marks paid without money.
NotificationService.php:1060-1135 notifyEmiDue / notifyEmiOverdue — fallback 1054.
BookingLifecycleService.php:650-830 recordPayment — early-discount adjust, notification, commission, referral.
BookingLifecycleService.php:990-1030 clawback; 1260-1330 calculateCommission delegation.
PropertyWorkflowController.php:710-740 referrer role lookup.
WalletController.php:185-260 transfer / withdrawal / request — direct fetchOne instead of WalletService.
PayoutBatchService.php:226-400 exportBankCsv — TDS and beneficiary resolution logic.
GoogleAuthController.php referral per role: customer 100 / associate 200 / else 250.

## Appendix C — Second-Order Effects (downstream of the bugs)
1. Commission math on phantom EMI payments skews the canonical rates engine — the MLM plan v2 calibration (S455) is applied to wrong inputs; payouts drift high on EMI-heavy bookings.
2. Google-signup referral-reward leak silently inflates the points economy; "wallet balance" stops meaning anything for cohorts.
3. Ledger year-0 idempotency means a single double-send permanently suppresses a legitimate receipt-based commission; discovered only on customer dispute.
4. booking_commission_lock holds 0 rows in live DB — the rank-lock mechanism for direct-sale commission is entirely unused.
5. KPI "23 bookings / 48.6L pipeline" is dominated by the 20 TEST-HIST backfill rows; every AI rank or owner dashboard trained on it is wrong.
6. The Agent/Associate wallet capture-and-die: if 40 purchases happened in the last 30 days, that is 40-60k of captured revenue with liability attached.
7. wallet_points.status includes 'frozen' and 'blocked' but WalletService::credit() never checks it — blocked users may still be credited.

## Appendix D — Rollback / Restore Notes for Recovery
- The S522 TEST-HIST backfill rows are tagged; owner may drop with a single DELETE ... WHERE booking_number LIKE 'TEST-HIST-%' after sign-off.
- Pre-fix state dumps live in backups/db/ — pre_historical_backfill.sql, stale_fks_pre_repoint_2026-11-14.sql, payment_transactions_pre_fk_fix.sql, payments_wallet_now_s561.sql, ai_knowledge_chunks_pre_reindex_s555.sql.
- For the payout defect: no shipped fix yet; do NOT run UPDATE mlm_commission_ledger SET status='processing' before the enum migration ships together with the service class.
- For the activation defect: last 30 days of captured-but-unactivated purchases need a manual sweep — Razorpay settlements joined by order_id against wallet_activation_purchases; refund in bulk via console.
- For the EMI debit defect: stop the mandate-debit cron until fixed; currently it destroys customer receivables monthly with zero recovery.
