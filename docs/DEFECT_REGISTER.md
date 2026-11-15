# APS Dream Home — Defect & Improvement Register (living document)

**Owner:** Senior Dev / QA desk · **Started:** 2026-11-16 night session  
**How to use:** every defect gets one row. Status: `OPEN` → `FIXED` → `VERIFIED`. Fixes reference the commit hash.

Legend: P0 = breaks a business flow; P1 = wrong data/silent failure; P2 = cosmetic/UX.

| ID | Area | Symptom | Severity | Status | Commit |
|---|---|---|---|---|---|
| D-001 | Associate wallet | `/associate/wallet/transactions` + `/withdrawal` 500 (`user_wallets` missing) | P0 | FIXED | `81ddb876b` |
| D-002 | Associate wallet views | 4 wallet pages rendered blank (layout/ob_start inversion) | P0 | FIXED | `81ddb876b` |
| D-003 | Associate layout | dead duplicate chat widget (#aiChatWidget, undefined toggle) on every page | P1 | FIXED | `0571ebd47` |
| D-004 | Public home | fabricated hero stats (5000+ sold vs DB 33) | P1 | FIXED | `cd02a5abd` |
| D-005 | Chat widgets | AI replies render raw markdown; user msg not HTML-escaped | P2 | FIXED | `7f57c95b3` |
| D-006 | Admin login | debug error_log lines leaked session_id/cookie params | P2 | FIXED | `8ddc038cd` |
| D-007 | Admin pages | dead `/websocket-test` route 404 | P2 | FIXED | `675c654b0` |
| D-008 | Mobile API | MLM payouts/incentive queries hit nonexistent columns | P0 | FIXED | `d24b3c167` |
| D-009 | AI chat | SmartAI associate context read nonexistent columns | P1 | FIXED | `64b17bb1b` |
| D-010 | AI conversations | FK 1452 orphan user_id inserts | P1 | FIXED | `0ea77d890` |
| D-011 | Secret hygiene | 22 plaintext token-shaped rows in settings/api_keys | P1 | FIXED (purged) | `secret_purge_rotated.php` |
| D-012 | Retention | ab_events/visitor_page_views backlog 497 rows | P2 | FIXED | manual purge + dump |
| D-013 | Email lane | SMTP down silently (owner rotation pending) | P0 | BANNER ADDED | `08fb66f3d` |

---

## NEW findings go below (added live by the night-session agents)

| D-014 | Customer portal | `/user/investment-plans` 500 — view referenced nonexistent class `App\Core\Security\CsrfProtection` (correct: `App\Core\Security\CSRF::getToken()`) | P0 | FIXED+VERIFIED | `e2062f118` |
| D-015 | Customer portal sweep | 49 pages probed: all 200 except phantom paths I probed; `cron-alerts` & `upgrade-to-associate` 403 = intentional gates | P3 | VERIFIED | n/a |
| D-016 | Customer portal | `investment-plans` page now renders fully (needles + titles verified live) | P0 | VERIFIED | `e2062f118` |
| D-017 | Customer portal | `/user/pay-emi/upi-qr` 404 — wallet card's "Pay via UPI" CTA targeted a POST-only route. Added GET `/user/pay-emi/upi-qr`, login-gated VPA page (QR, upi:// deep link, back-to-wallet) | P1 | FIXED+VERIFIED (200 + QR needs needles) | this batch |
| D-018 | Payments (EMI money math) | Auto-debit marked installments PAID off a Razorpay `order_created` with no money moved; webhook never settled EMIs. Fix: money-gate (only captured/paid/authorized/completed settles) + capture path persists `payment_orders` + webhook `payment.captured` settles installment and inserts receipt. Sacrificial E2E (HMAC-signed fake capture): installment 36 flipped pending→paid, receipt inserted, then reverted byte-identically | P0 | FIXED+VERIFIED (E2E) | this batch |
| D-019 | Payments (webhooks) | `payment_webhook_logs` insert wrote columns that don't exist (`event_id`, `signature`, `signature_verified`, `processed`, `ip_address`) — silent 1054 swallow on EVERY webhook call since deployment. Handler now writes `gateway/event_type/payload/status/tenant_id` (live schema) and marks `status=processed|failed` keyed on insert's returned id | P1 | FIXED+VERIFIED (E2E 200 + status=processed) | this batch |
| D-020 | API (authorization) | `/api/v2/mobile/admin/*`, `/api/v2/admin/*`, township ops, cash-drawer closed had NO admin check — any valid customer Bearer token read revenue/P&L/cash/processlist/PII (QA CRIT). New shared `ApiAdminRoleMiddleware` (401/403) applied to 42+ routes; live probes: customer→403 everywhere, admin→200 everywhere | P0 | FIXED+VERIFIED (live) | this batch |
| D-021 | Config (service credentials) | 7 sites static-called non-static `ServiceConfigService::get/getApiConfig` (every call fataled → silent env/default fallback) — DB-configured Razorpay/Twilio/S3/Leegality/GSTN/TIN/exchange-rate settings could never load. All converted to `getInstance()->…`; every class instantiates OK | P1 | FIXED+VERIFIED | `977ef08f1` |
| D-022 | MLM/payouts (money) | `PayoutBatchService` marks ledger rows `status='processing'` (5 sites); the live `mlm_commission_ledger.status` enum never contained it → every payout batch 1265-rolled-back; payouts could never complete settlement. Migration `widen_mlm_commission_ledger_status_s596` appends `'processing'` (idempotent; 359 rows unchanged; rolled-back UPDATE through `pending→processing→pending` accepted by strict mode) | P0 | FIXED+VERIFIED | `16b6e4d02` |
| D-023 | API (data exposure) | `MobileMLMApiController::getGlobalPayoutHistory()`/`getPayoutHistory()` — dead handlers, no routes, no callers, unscoped `SELECT … mlm_payouts LIMIT 50`; would leak every associate's payouts if wired. Removed; `/payouts/history` + `/mlm/payouts` re-verified caller-scoped (`WHERE associate_user_id = ?`) | P1 | FIXED+VERIFIED | `16b6e4d02` |
| D-024 | Wallet (money) | Agent + Associate `verifyPayment()` called `activatePurchaseUserWallet()` — removed in S478 because user_wallets was dropped/degraded — so a real Razorpay capture was followed by a hard 500 ("Activation failed") with the money already taken. Swapped to canonical `activatePurchase()` (same call the customer path uses) | P0 | FIXED+VERIFIED (lint + master 11/11; live E2E blocked on Razorpay order flow) | `c9b0f9bb5` |
| D-025 | API (data exposure) | `GET /api/v2/mobile/voice/session/{id}` exposed full AI-call transcript + lead PII to any Bearer token; zero callers in the client. Gated to admin roles; live probes: customer → 403, admin → 200 | P1 | FIXED+VERIFIED (live) | `20ffcdc7d` |
| D-026 | API (payout mutation probe) | Note: `POST /api/v2/mobile/payouts/process` is caller-scoped (`WHERE associate_user_id = ?`) and is genuinely used by the Flutter auto-payout page — NOT a mass-payout trigger and NOT gated (plain auth correct). An empty-payload POST probe against both my test accounts flipped 0 rows (no pending payouts) | INFO | VERIFIED SAFE (0/0 rows) | n/a |
| D-027 | API / legal desk | `GET /api/v2/mobile/legal/documents/{id}` + `/preview` 500'd on every call — `LegalApiController` called `LegalDocumentService::getDocumentById()`, a method that existed nowhere (500 pages delivered as `application/json`). The wrapper now implements `getDocumentById`/`getDocuments` (LSP-safe)/`getUploads` against live `legal_documents` + `legal_document_uploads`; legal desk probes all clean: `documents` 200 `[]`, `documents/1` 404, `preview` 404, `categories`/`templates` 200 seeded | P1 | FIXED+VERIFIED (live) | `7aff52745` |
| D-028 | Admin / payments export | `/admin/payments/export` downloaded a **0-byte file**. Two causes: (a) export read legacy `booking_payments` (2 seed rows, May–Jun 2026; silently empty since June), (b) empty `$data` emitted zero bytes incl. no header row. Rewritten onto canonical `payments` ledger + plot_bookings→plots→colonies→users join, status filters real (`completed`/`pending,processing`/`refunded,partial_refund`), header row always written. April export now carries real rows; Oct/Nov export returns header-only 174B instead of 0B | P1 | FIXED+VERIFIED (live + master 11/11) | this batch |

## night-team assignments (2026-11-16)

- QA Web sweep → `docs/QA_FINDINGS_WEB.md` (done — 0×5xx, 334 literal routes, 1 HIGH: employee change-password shadowed by POST-only handler)
- CEO capability analysis → `docs/CEO_GAP_ANALYSIS.md` (done — 70% feature-complete / ~5% operationally-complete; RERA 0/8, Razorpay blank, Play Console pending, zero lead→booking attribution)
- Thinker/architecture review → `docs/ARCHITECT_REVIEW.md` (done — EMI money-gate bug, payout enum broken, activation-capture leak, money-system duplication)
- Mobile/API sweep → `docs/QA_FINDINGS_API.md` (done — Bearer-token admin gate bypass, PII exposure, unscoped payouts)

