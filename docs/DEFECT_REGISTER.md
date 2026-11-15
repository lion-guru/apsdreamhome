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

| D-014 | Customer portal | `/user/investment-plans` 500 — view referenced nonexistent class `App\Core\Security\CsrfProtection` (correct: `App\Core\Security\CSRF::getToken()`) | P0 | FIXED+VERIFIED | pending commit |
| D-015 | Customer portal sweep | 49 pages probed: all 200 except phantom paths I probed; `cron-alerts` & `upgrade-to-associate` 403 = intentional gates | P3 | VERIFIED | n/a |
| D-016 | Customer portal | `investment-plans` page now renders fully (needles + titles verified live) | P0 | VERIFIED | `e2062f118` |

## night-team assignments (2026-11-16)

- QA Web sweep → `docs/QA_FINDINGS_WEB.md` (running)
- CEO capability analysis → `docs/CEO_GAP_ANALYSIS.md` (**done** — see top findings: software is 70% feature-complete but ~5% operationally-complete; RERA 0/8, Razorpay blank, Play Console pending, EMI/TDS/Payroll never executed on real records, 0 lead→booking attribution). Operator summary: stop adding features; run Phase 0 (RERA → production deploy → Razorpay live → secrets rotation → offsite backup drill → Play Console upload), then ONE real buyer walk-through, ONE real salary run, ONE real registry handover.
- Thinker/architecture review → `docs/ARCHITECT_REVIEW.md` (running)
- Mobile/API sweep → `docs/QA_FINDINGS_API.md` (running)

