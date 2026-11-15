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
| D-021 | Config (service credentials) | 7 sites static-called non-static `ServiceConfigService::get/getApiConfig` (every call fataled → silent env/default fallback) — DB-configured Razorpay/Twilio/S3/Leegality/GSTN/TIN/exchange-rate settings could never load. All converted to `getInstance()->…`; every class instantiates OK | P1 | FIXED+VERIFIED | this batch |

## night-team assignments (2026-11-16)

- QA Web sweep → `docs/QA_FINDINGS_WEB.md` (done — 0×5xx, 334 literal routes, 1 HIGH: employee change-password shadowed by POST-only handler)
- CEO capability analysis → `docs/CEO_GAP_ANALYSIS.md` (done — 70% feature-complete / ~5% operationally-complete; RERA 0/8, Razorpay blank, Play Console pending, zero lead→booking attribution)
- Thinker/architecture review → `docs/ARCHITECT_REVIEW.md` (done — EMI money-gate bug, payout enum broken, activation-capture leak, money-system duplication)
- Mobile/API sweep → `docs/QA_FINDINGS_API.md` (done — Bearer-token admin gate bypass, PII exposure, unscoped payouts)

