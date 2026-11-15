# QA Findings — Mobile / API Surface (2026-11-15)

**Scope:** `routes/api.php` literal GET routes under `/api/v2/mobile/*` and `/api/v2/*`, probed live against localhost (`http://localhost/apsdreamhome`). Read-only methodology: GET requests only + safe logins. **No DB writes, no mutating POSTs, no commits.**

**Tokens (one per role, reused across probes):**
- customer — `testuser@example.com` (userId 49), token prefix `55a54b85…`
- agent — `agent1@apsdreamhome.com` (userId 2, role=associate, rank=Sr. Ass.), token prefix `0983d0d7…`
- associate — `testassociate@example.com` (userId 53), token prefix `bf35661f…`

**Probe volume:** ~500 requests total (3 logins, 235 literal GET paths, 219 re-probes capturing bodies for empty-payload analysis, 25 parameterised GET probes with known ids (colonies 1/2, plot ids 51/305/4633, booking ids 1/5/9592, doc id 1), 12 unauthenticated control probes, 12 targeted re-probes). Slightly above the ~400 target; disclosed honestly.

Legend for severities: **CRIT** = unauthenticated/unauthorised data exposure · **HIGH** = authed-customer data exposure of admin/financial/PII · **MED** = broken (500/HTML) · **LOW** = contract hygiene.

---

## 1. Headline: the mobile API has an authorisation layer that does not exist

`ApiAuthMiddleware` validates that a Bearer token belongs to *someone*. It performs **no role scoping, no tenant scoping, no ownership checks**. As a result, every endpoint registered with that middleware is reachable by the most ordinary customer account. The app pretends to have an admin/mobile gate by relying on the client to hide admin screens; the server does not enforce it.

### 1.1 Unauthenticated data exposure (no Authorization header at all)
| Endpoint | Result | Severity |
|---|---|---|
| `GET /api/v2/mobile/plots/all` | **200 — full plot inventory, 673 plots with prices, colony names, facing, corner flags, pagination** (`"total":673`) | CRIT |
| `GET /api/v2/mobile/township/operations` | **200 — STP/water/genset/security/CCTV/waste/horticulture/lighting/RWH desk telemetry for the live township** (water pump #2 optimal, DG Set #1, security checked-in, gate_boom_barrier_hit incident high, NVR-5 32Ch, Pit-5 optimal_ready) | CRIT |

Both routes are registered in `routes/api.php` **with no middleware at all**:
```php
$router->get('/api/v2/mobile/plots/all', 'Api\MobilePropertyApiController@getAllPlots');
$router->get('/api/v2/mobile/township/operations', 'Api\MobileTownshipOpsApiController@getOperations');
$router->get('/api/v2/mobile/township/operations/{type}', 'Api\MobileTownshipOpsApiController@getUtilityDetail');
```
(`/api/v2/mobile/registry/desk`, `/api/v2/mobile/cash-drawer/today` also have no middleware on the route line but bail out with `401` at controller level — protected by accident, not by design.)

### 1.2 Authenticated-but-unscoped: customer token sees admin/financial data
Probed **as the customer test account** on endpoints registered with `ApiAuthMiddleware` but protected by no role check anywhere in the controller:

| Endpoint | Response (customer token) | Severity |
|---|---|---|
| `GET /api/v2/mobile/admin/dashboard-stats` | `{"total_revenue":0,"total_bookings":20,"total_users":67,"total_leads":994}` — real business KPIs | HIGH |
| `GET /api/v2/mobile/admin/daily-sales` | income row, count 20, revenue 0.00 | HIGH |
| `GET /api/v2/mobile/admin/sales-trend` | monthly sales | HIGH |
| `GET /api/v2/mobile/admin/lead-conversion` | full funnel: total 994, won 87, lost 94, per-stage counts (new 175, contacted 131, qualified 134, proposal 110, negotiation 85, closed_won 87…) | HIGH |
| `GET /api/v2/mobile/admin/emi-collection` | `total_amount_due: 44,360,640.08`, today_dues, collections history, earnings | HIGH |
| `GET /api/v2/mobile/admin/top-associates` | **Associate PII**: names, emails, phones, booking counts (122895 Shweta Gupta 919183618801, 122890 Meena Verma 918757914683…) | HIGH |
| `GET /api/v2/mobile/admin/colony-performance` | colony P&L counts | HIGH |
| `GET /api/v2/mobile/colony-pipeline/dashboard` | colony pipeline: 8 colonies, 766 plots, pricing row counts, layout status, cost entries | HIGH |
| `GET /api/v2/mobile/cash-drawer/today` | `opening_cash: 5000`, cash receipts/expenses, denominations | HIGH |
| `GET /api/v2/mobile/cash-drawer/today` (no auth) | controller-level 401 only — see §1.4 | MED |
| `GET /api/v2/mobile/registry/desk` | `eligible_bookings`: BK-GKP-2026-089, plot A-108 Ayodhya Enclave, ₹18,50,000 fully paid — **PII + financials of buyers** | HIGH |
| `GET /api/v2/mobile/township/operations` | same desk telemetry as §1.1 but "works" only because both are unauthenticated | CRIT |
| `GET /api/v2/mobile/plots/all` | same as §1.1 | CRIT |
| `GET /api/v2/admin/query-analyzer/processes` | **Live DB processlist** — raw SQL text of other sessions (`SELECT ID,USER,HOST…` digest) | CRIT |
| `GET /api/v2/admin/query-analyzer/slow-queries` / `missing-indexes` / `table-stats` / `index-stats` | performance_schema digests of production queries | HIGH |
| `GET /api/v2/mobile/voice/session/1` | full AI call-session row (agent_name, duration 145s, outcome, sentiment, created_at) — **voice/session/{id} is not scoped to the caller** | HIGH |
| `GET /api/v2/mobile/demand-letters` | list shape with `total:0` (auth ok) | LOW |
| `GET /api/v2/mobile/maintenance-dues` | summary block (auth ok) | LOW |

Root cause: `Api\AdminMobileController` / `Api\MobileAdminApiController` / `Api\QueryAnalyzerController` / `MobileTownshipOpsApiController` / `MobileFeatureEndpointsController` rely solely on `ApiAuthMiddleware`; the by-design gate is "hide the admin screens in Flutter". Comment at `AdminMobileController.php:22` says it verbatim: *"Mobile admin API uses Bearer token auth - stateless"*. No `$GLOBALS['api_user_id']` role lookup exists anywhere in these classes.

### 1.3 Per-user scoping broken: MLM payouts leak globally
`MobileMLMApiController::getPayoutHistory()` calls `getGlobalPayoutHistory()`, which runs `SELECT id, gross_amount, status, payment_mode, created_at FROM mlm_payouts ORDER BY created_at DESC LIMIT 50` — **no `beneficiary_user_id` filter**. Any associate/customer hitting `GET /api/v2/mobile/mlm/payouts` (same as `/mlm/payouts/history`) receives the last 50 payout transactions of **all** associates. (Both returned empty arrays for our test tokens only because `mlm_payouts` happens to hold a single probe row; the query is unscoped by construction.)

### 1.4 Routes protected only by accident at controller level
`/api/v2/mobile/registry/desk`, `/api/v2/mobile/nri-desk`, `/api/v2/mobile/plot-transfers`, `/api/v2/mobile/demand-letters`, `/api/v2/mobile/maintenance-dues`, `/api/v2/mobile/cash-drawer/today`, `/api/v2/mobile/voice/schedule` have **no route middleware**; they answer `401` only because the controller independently bails out. Any future refactor that moves the bail-out breaks them silently. Fix the route layer, don't rely on the fallback.

---

## 2. Hard failures (500 / HTML-shaped 500)

| Endpoint | Status | Symptom | Hypothesis |
|---|---|---|---|
| `GET /api/v2/mobile/legal/documents/1` | **500** | Router fatal → HTML error page, `Content-Type: application/json` | `LegalApiController@getDocumentDetail` → `docService->getDocumentById(1)` throws (service/table mismatch) and the exception escapes the try, so the global 500 handler paints the HTML page |
| `GET /api/v2/mobile/legal/documents/1/preview` | **500** | same | `previewDocument()` → `getDocumentById` → same root cause + `$doc['content']` likely missing |

Both produce **HTML pages served with a JSON content-type** — every mobile client doing `response.json()` will crash on a bare HTML literal. The 500 handler is not returning JSON for API routes.

---

## 3. Contract hygiene (LOW)

| Endpoint | Symptom |
|---|---|
| `/api/v2/mobile/campaign-templates`, `/api/v2/mobile/voice-uploads` | 403 but `Content-Type: text/html` with a JSON body (`{"success":false,"error":"Admin access required"}`) |
| All `/api/v2/mobile/stamp-duty/*`, `/api/v2/mobile/property-tax/*`, `/api/v2/mobile/user/favorites/check` etc. | 400 with `{"success":false,"error":"…required"}` for undocumented required params — validation is fine, but the 400 body carries no `param` hint; mobile cannot self-diagnose |
| `/api/v2/verify-referral` | 400 missing `ref` — acceptable, but same no-hint pattern |

---

## 4. `success:true` with empty payload — is the payload phantom?

~62 literal endpoints returned 200 + `{"success":true,"data":[]}` (or equivalent `[]`/`false`). Cross-checked against the live DB:
- `user_id = 49` has **0 notifications** → `/api/v2/mobile/user/notifications` empty is *correct*, not a bug.
- Test tokens belong to users with **no `agent_profiles`, `employee_profiles`, or MLM payout rows** (`mlm_payouts` = 1 probe row) → the agent/employee/self-service/payout empty arrays are *correct* for these accounts and cannot be judged phantom without fixtures.
- Endpoints where empty data IS suspicious for this seed (expect bulk rows): `/api/v2/mobile/properties/featured`, `/api/v2/mobile/marketplace/premium` (`len=36` — actually `{"success":true,"data":[],"count":0}`), `/api/v2/mobile/colonies/search` (`len=26` — but no `q=` was passed; validation-like response), `/api/v2/mobile/sync` (`len=60`, shape?).
### Flagged empty-payload candidates (can't disprove; needs owner decision)
- `/api/v2/mobile/admin/query-analyzer/missing-indexes` — **should not be empty**: the schema is littered with missing FK indexes (every 1054/per the perf telemetry). Empty `[]` for a customer token strongly suggests the endpoint either swallowed a failure or role-filtered to zero rows.
- `/api/v2/mobile/search-history` — `len=90`: `{"success":true,"data":[]}` but root is a list shape mismatch (response envelope for search-history is non-standard; 90 bytes is more than `{"data":[]}`).
- `/api/v2/mobile/self-service/reimbursement` — `len=284`: success + 284 bytes is not an empty envelope; needs body inspection — likely `{"userId":0,…}` misshapen response rather than a list.

**Rule-of-thumb finding:** endpoints return 200 + empty correctly for empty DB, but the pattern `success:true + data:[]` is the codebase's universal "swallowed error" idiom (see §5), so no individual empty response can be trusted without a row-count cross-check. Recommend CI probe asserting row counts > 0 for each seeded fixture.

---

## 5. Suspicious swallows: `catch (\Throwable|\Exception)` → `success:true`

Found 301 `catch` blocks in `app/` whose following 12 lines contain `'success' => true`. Most outer catches actually degrade to `false` (`handleApiError` in `HasJsonResponses` returns `success:false`); the **dangerous set** is inner "log-and-continue" that reports success despite failure, and flat `catch → echo success:true` copies. Top 12 by money/customer-data severity:

| # | File:line | Pattern | Blast radius |
|---|---|---|---|
| 1 | `app/Http/Controllers/Api/MobileUserApiController.php:3730` | `catch` around **wallet refund** in `investmentCancel` — logs error, still sets investment `status='cancelled'`, returns `success:true, data:{refunded: <whatever flag>}` | **Money** — customer cancels investment, refund insert fails, investment is cancelled anyway, no wallet credit, no alert |
| 2 | `app/Services/Sales/BookingLifecycleService.php:816` | `calculateCommission()` after `recordPayment()` — failure logged, payment completes, commission never credited | **Money** — payments land with no commission ledger rows; MLM/associate payouts drift |
| 3 | `app/Services/Sales/BookingLifecycleService.php:825` | `processCustomerReferralCommission()` 2% referrer wallet credit — failure logged, payment succeeds | **Money** — referral rewards silently dropped |
| 4 | `app/Http/Controllers/Front/BookingController.php:637` | `INSERT INTO plot_locks` fails → returns `success:true, lock_id:0`, "proceeding without reservation lock" | **Money/race** — plot double-booking possible if `plot_locks` migration is missing; user proceeds unprotected |
| 5 | `app/Http/Controllers/Api/MobileUserApiController.php:1336` | `updateNotificationPreferences()` — `catch (\Throwable $e) { echo json_encode(['success'=>true]); }` | **Data loss lie** — preferences write fails, UI shows "saved" |
| 6 | `app/Http/Controllers/Api/MobileUserApiController.php:1352` | `updateUserPreferences()` — identical `catch → success:true` | **Data loss lie** — same for general preferences |
| 7 | `app/Http/Controllers/Api/MobileUserApiController.php:1416` | `getPaymentHistory()` — `catch → success:true` with fully-zeroed `payments:[]` and `stats:{total_paid:0,…}` | **Money confusion** — real DB error masquerades as "no payments yet"; customer believes they owe/paid zero |
| 8 | `app/Http/Controllers/Api/MobileUserApiController.php:1433` | `getBlogPosts()` — `catch → success:true, data:[]` | Content gone, no signal |
| 9 | `app/Http/Controllers/Api/MobileUserApiController.php:93` | `uploadDocument()` — inner `catch` on document-locker insert swallowed; outer `catch → handleApiError` (good) **but** the success path fires before the locker write is verified | **Document loss lie** — file uploaded to disk, DB row absent, user sees "uploaded" |
| 10 | `app/Http/Controllers/Front/BookingController.php:466` | e-sign status lookup fails → synthesises `['success'=>true,'status'=>'pending']` | **Legal lie** — e-sign state reported as pending; booking proceeds on a lie |
| 11 | `app/Http/Controllers/Api/MobileMLMApiController.php:265→275` | `getGlobalPayoutHistory()` swallows, returns `[]`; then `getPayoutHistory()` wraps in global (unscoped) query | **Financial PII** — see §1.3; empty array + unscoped query both silently wrong |
| 12 | `app/Services/Sales/BookingLifecycleService.php:342` | salary-structure dual-write `associate_profiles` update swallowed (`catch {}` empty) | **Payroll drift** — salaried flag half-written; payslip logic branches on stale flag |

Also worth noting (not in top 12 but systemic): `Front/BookingController.php:637`-style `"if table doesn't exist, skip locking"` comments; `app/Http/Controllers/Api/MobileUserApiController.php:1523` returns fabricated hardcoded stats (`'projects'=>4,'plots'=>5000,'families'=>500`) on failure — marketing copy served as live data.

---

## 6. Parameterised GET probes (25 probes with known ids, all tokenised as customer)

| Endpoint | Status | Notes |
|---|---|---|
| `/api/v2/mobile/colonies/1`, `/colonies/1/plots`, `/colonies/1/stats`, `/colonies/1/health` | 200 | fine |
| `/api/v2/mobile/colonies/2/properties` | 200 | 51 KB payload — fine |
| `/api/v2/mobile/colony-pipeline/plots/1`, `/pricing/1`, `/detail/1`, `/map/geojson/1`, `/costs/1`, `/plots/stats/1` | 200 | `/layout/1` and `/map/1` → **404** "No current layout for this colony" (valid empty state) |
| `/api/v2/mobile/bookings/1`, `/bookings/5` | 200 `{"success":true,"data":false}` | customer sees **false**, not someone else's row — ownership check works |
| `/api/v2/mobile/plots/51`, `/plots/305` | 200 | public inventory, fine |
| `/api/v2/mobile/careers/1`, `/blog/gorakhpur-real-estate` | 200 | public, fine |
| `/api/v2/mobile/properties/1` | 404 "Property not found" | consistent |
| `/api/v2/mobile/loans/1` | 200 | **16 KB payload returned to a customer for loan id 1** — verify ownership on CompanyLoanService (not proved broken; not proved safe). Flag for review. |
| `/api/v2/mobile/legal/documents/1`, `/preview` | **500** | see §2 |
| `/api/v2/mobile/messages/2` | 200 `{"data":[]}` | no leak (empty), but message scoping by `otherUserId` should be re-tested with a real thread |
| `/api/v2/mobile/voice/session/1` | 200 | **session returned to unrelated customer** — scope missing |
| `/api/v2/mobile/user/support/tickets/1` | 200 `{"data":false}` | fine |
| `/api/v2/mobile/self-service/profile` | 200 | returns own profile incl. aadhaar/pan/bank fields when set — fine for owner |

---

## 7. Route-family summary (first pass, 235 literal routes)

| Family | Probed | Failed (400+ / non-JSON) |
|---|---|---|
| admin (v2/admin/query-analyzer etc.) | 11 | 0 (but §1.2 exposure) |
| mobile/admin | 7 | 0 (§1.2 exposure) |
| mobile/agent | 14 | 0 |
| mobile/associate | 9 | 0 |
| mobile/mlm | 22 | 0 |
| mobile/self-service | 21 | 0 |
| mobile/user | 17 | 1 (favorites/check 400 param) |
| mobile/properties | 6 | 1 (search 400 param) |
| mobile/wallet | 3 | 0 |
| mobile/legal | 3 | 0 (but §2 500s on param routes) |
| mobile/stamp-duty | 4 | 3 (param hints) |
| mobile/property-tax | 3 | 2 |
| mobile/careers, mobile/blog, mobile/rera | 7 | 0 |
| mobile/colonies, colony-pipeline | 4 | 0 |
| mobile/voice | 3 | 0 (but session scoping §1.2) |
| everything else (about…wallet…) | ~75 | ~0 |
| 400-validation responses | 12 | (count includes validation-only 400s) |
| 401/403 | 4 | 1 (wrong content-type) |
| **Total** | **235** | **16** (12 validation-400, 2 wrong-ctypes, 1 401 mislabel, 1 403 caption) |

---

## 8. Coverage notes & limitations

- **Not probed:** POST/PUT/PATCH/DELETE routes (mutating); 71 param routes beyond the 25 above; WebSocket/realtime channels; `MySQL FULL PROCESSLIST` contents (observed count only, not dumped); export-CSV/payout endpoints that mutate.
- **Error paths never forced:** 500s were observed (legal/documents), but no route was fed malicious input; findings cover *observed* state only.
- **"Empty data" is not proof of absence** — list endpoints that should be non-empty for this fixture should be asserted with row-counts; today's evidence can't distinguish "empty-by-design" from "swallowed-SQL" for all 62 cases listed.
- **Auth layer assumed:** all conclusions sit behind `ApiAuthMiddleware` = Bearer→userId only; no server-side role claim was verified beyond the 401/403 responses themselves.
- **Environment**: single localhost deployment, `testuser@example.com` = userId 49 (role=customer). No second tenant sampled; tenant isolation conclusions (e.g. `tid>1` branches in QueryAnalyzer) were read from code, not probed.
- **Artifact hygiene:** temp bodies and CSVs lived in `%TEMP%\opencode` and were deleted after inspection; this document is the only file written.
- **Recommended next actions (owner decisions, not QA):**
  1. Add a role gate (single shared middleware) to all `/admin/*` and `/mobile/admin/*` routes, or move them behind the web-session admin bundle.
  2. Fix `/api/v2/mobile/plots/all`, `/township/operations*` route registration to require auth (or explicitly whitelist for public).
  3. Scope `mlm/payouts[/history]` to the caller.
  4. Scope `voice/session/{id}` to the caller or admins.
  5. Give `LegalApiController::getDocumentDetail/previewDocument` a real try/catch or fix the underlying docService failure (currently the whole Legal desk is 500).
  6. Make the 500 handler emit JSON for `/api/*` paths.
  7. Pay down the 301 swallow sites; start with §5 rows 1–4.
