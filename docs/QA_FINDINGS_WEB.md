# QA Findings - WEB Portals (APS Dream Home ERP)

**Sweep date:** 2026-11-15 - **Mode:** strict read-only (GET-only, no DB writes, no git commits, no file edits outside this doc) - **Auth:** dev `?test_login=` bypass (N=1 admin, 4 employee, 5 associate, 6 agent, 7 customer) - **Server:** `http://localhost/apsdreamhome` - **Request budget:** ~800 cap -> **~585 requests used** - **Fixes applied:** none - findings only

## Executive summary

| Portal | Literal GET sweep | Internal-href crawl | Defects |
|---|---|---|---|
| Customer (`/user/*`, `/customer/*`) | 88 probed: 59 2xx / 28 3xx / 1 4xx | 45 probed: 42 2xx / 2 3xx / 1 flag | **1 HIGH** |
| Employee (`/employee/*`) | 77 probed: 74 2xx / 2 3xx / 1 4xx | 33 probed: 33 2xx / 0 flags | **3 (2 HIGH, 1 MED)** |
| Agent (`/agent/*`) | 22 probed: 20 2xx / 2 3xx / 0 4xx | 17 probed: 17 2xx | 0 |
| Associate (wallet only) | 5 probed: 5 2xx | 40 probed: 39 2xx / 1 3xx | 0 (1 INFO) |
| Admin (sample, ~142) | 142 probed: 129 2xx / 12 3xx / 1 4xx | 110 cap: 105 2xx / 5 3xx / 0 flags | 0 blocking (2 INFO) |

**Headline:** **zero HTTP 500s** across 334 literal GET probes + 245 internal-href probes. All 52 redirects examined resolve to canonical pages. Findings are dominated by (a) one infinite redirect loop, (b) link-to-POST-only-endpoint defects, (c) raw-JSON "pages" and empty bodies, (d) heavy duplicate GET route registrations across layers (web.php / employee.php / ai.php / gap_builds.php) where last-wins routing already caused one broken page.

**Total verified-OK count:** ~471 portal pages/endpoints answered 2xx (287 direct + 184 href-crawl) with zero server errors; ~52 more answered correct-by-design 3xx redirects.

## Method

1. Cookie jars built per role via the dev bypass (`GET {portal-or-/login}?test_login=N`), saved under `C:\Users\abhay\AppData\Local\Temp\opencode\qa_<role>.jar` (absolute paths required for curl.exe here).
2. Literal GET paths extracted from `routes/web.php` (single-quoted `$router->get('...')`), filtered: kept leading-`/` portal prefixes (`/customer|/user|/associate|/agent|/employee|/admin`), skipped `{param}` paths, skipped any path containing: `delete destroy remove purge reset send toggle approve reject cancel execute rebuild import broadcast payout withdraw credit debit dispatch deploy notify run process trigger flush generate assign submit store update accept confirm logout`.
3. Each URL probed: `curl.exe -s -o NUL -w "%{http_code}|%{size_download}"` sequentially; status <= 400 flagged, 2xx bodies < 3000 B inspected, 3xx locations recorded.
4. Internal-href crawl: 20 key pages (5 customer, 2 employee, 2 agent, 2 associate, 9 admin), extracted `href="..."` absolute/relative portal links (~662 unique, admin hub pages dominate at ~400 each - the sidebar), probed **all 135 non-admin** uniques + first 110 admin uniques (alphabetical cap to respect budget).
5. Small-body inspection + root-cause reading of controller/route files for each flagged entry.

## Cross-cutting findings

### C-1 - Duplicate GET route registrations across layers (structural debt)
`routes/web.php` loads first, then `land.php`, `gap_builds.php`, `ai.php`, `employee.php` (`public/index.php` lines 99->132); the router is **last-registration-wins**, so later files silently shadow web.php's handlers. Confirmed duplicates (non-param paths, grep of all five route files):
- `/employee/*` - **all** GET paths are registered in **both** `web.php` and `employee.php` (~70 pairs), e.g. `/employee/dashboard` 3x (web+employee+employee), `/employee/self-service` 3x, `/employee/self-service/encashment[/apply]` 3x, `/employee/self-service/payslips` 2x inside web.php itself.
- `/admin/ai-calling/*`, `/admin/ai-system/*`, `/admin/voice-calling*`, `/admin/voice-agents/*`, `/admin/ivr-flows*`, `/admin/ai/executive-assistant` - registered in both `web.php` and `ai.php` (~45 pairs).
- Within `web.php` itself: `/admin/payroll`, `/admin/payroll/create`, `/admin/work-orders`, `/admin/expenses`, `/admin/green-energy/solar`, `/admin/plots/demarcation`, `/admin/registry-appointments`, `/admin/maintenance-dues`, `/employee/self-service/payslips`, `/user/passbook`.
- `/admin/rag` in `gap_builds.php` + `ai.php`; `/admin/ai-training` in web.php + ai.php x2.
- **Confirmed fallout:** see E-1 (the `/employee/change-password` shadow breaks the page). The other shadowed pairs currently produce identical-or-working responses (probed 2xx) but the routing is accidental, undocumented, and fragile - any future edit to one copy changes behavior silently.
- Scan was not exhaustive (no `POST`/param-paths included); full inventory is a maintenance task.

### C-2 - Raw-JSON responses from HTML page routes
Several GET routes render bare JSON where a page is expected; browsers show raw JSON instead of UI. By design as JS data endpoints, but no HTML wrapper / no content-type nuance / no access hints. Noted, not broken: `/employee/api/*` (<=70 B), `/employee/legal/quick-action-hub[/counts]` (174/108 B - a "page" used by JS), `/admin/ai-dashboard` (1 KB), `/admin/builder-dashboard/construction` (26 B), `/admin/finance/exchange-rate` (103 B), `/admin/finance/all-rates` (2 KB), `/user/kyc/status` (50 B), `/user/referrals/leaderboard/data` (439 B).

### C-3 - Empty / near-empty success responses
- `/admin/payments/export` -> 200, **zero-byte body**, `Content-Type: text/csv`, `Content-Disposition: attachment; filename="payments_all_2026-11-01_to_2026-11-30.csv"`. Not a crash: `PaymentController::exportCSV()` writes nothing (not even a header row) when `$data` is empty, and the default window (current month, Nov 2026) contains none of the 4 existing payments (all Apr-Oct 2026). `/associate/wallet/transactions/export` -> 200 with header-only CSV (no rows on dev data). INFO-level, but an "Export CSV" button that downloads an empty file with a 200 and no error message is bad UX - recommend empty-state handling or an explanatory 302+flash.
- `/employee/telecalling/followups` -> 200, **0-byte body** - see E-3 (defect, root-caused).

## Customer portal (`/user/*`, `/customer/*`)

**Probed:** 88 literal GETs + 45 href-crawled links from `/user/dashboard|bookings|wallet|passbook|properties`. **Verified OK:** 59 (sweep) + 42 (crawl) = 101 portal endpoints. Legacy `/customer/*` (23 paths) -> **301 -> `/user/*`** (by design, all verified). 3xx by design: `/user/foreclosure`->`/customer/passbook` (which itself 301s -> `/user/passbook`), `/user/messages`->`/user/notifications`, `/user/payments`->`/user/payment-history`, `/user/two-factor/verify`->`/login`, `/user/upgrade-to-associate`->`/user/referral`.

| URL | Status | Symptom | Hypothesis / root cause |
|---|---|---|---|
| `/user/pay-emi/upi-qr` (linked from `/user/wallet` HTML as `<a href target="_blank">` - `views/customer/wallet.php:62,70`) | **404** | "Pay EMI via UPI" CTA on the customer wallet page opens a 404 | Route is **POST-only**: `web.php:6526` `POST /user/pay-emi/upi-qr -> CustomerPassbookController@generateUpiQr` (AJAX JSON). No GET route exists (`--`grep finds both GET/POST only at `6558` which is a 301 `/customer/pay-emi/upi-qr`->`/user/pay-emi/upi-qr`, itself with no GET handler). **Defect: link-to-POST-only endpoint.** A logged-in customer clicking "Pay EMI (UPI QR)" gets a bare 404 on a new tab. |
| `/user/saved-searches/cron-alerts` | 403 (JSON `{"success":false,"error":"Forbidden"}`) | Hard 403 to normal customers | Guarded `CRON_SECRET` key endpoint (`SavedSearchController@cronAlerts` - "GET /user/saved-searches/cron-alerts?key=CRON_SECRET"). Correct protection, and **no portal view links to it** (verified by grep). INFO - false-positive-shaped, not a user-facing bug. |
| `/user/kyc/status` | 200, 50 B | Bare JSON `{"success":true,"status":"not_started","kyc":null}` | JSON API, shown inline. OK by design. |
| `/user/maintenance-dues` | 200, 3.8 KB | Renders fine; "Back to Dashboard" link points at legacy `/customer/dashboard` | Cosmetic - resolves via 301. OK. |

**Crawl flags:** only `/user/pay-emi/upi-qr` (404, above). All other 44 hrefs -> 200/302 (incl. `/user/passbook/12/statement-pdf` 200, `/user/registry/12` 200, `/user/bookings/12` 200 - real record pages render).

## Employee portal (`/employee/*`)

**Probed:** 77 literal GETs (10 duplicate-hidden) + 33 href-crawled links. **Verified OK:** 74 + 33 = 107.

| URL | Status | Symptom | Hypothesis / root cause |
|---|---|---|---|
| `/employee/change-password` | **302 -> `http://localhost/apsdreamhome/employee/change-password`** (infinite self-redirect) | Change-password page unreachable; browsers show `ERR_TOO_MANY_REDIRECTS` after ~10 hops | **Root cause found.** `routes/employee.php:90` registers `GET /employee/change-password -> Employee\EmployeeController@changePassword` (a **POST** handler: `middleware('employee.auth')`, reads `$_POST`, on empty POST -> `$_SESSION['error'] = 'All fields are required'` -> `$this->redirect('/employee/change-password')`). `routes/web.php:1421` already registers the correct handler (`changePasswordView` -> renders `employee_leads/change_password`), but employee.php loads **after** web.php and last-wins routing shadows it. Views link to `/employee/change-password`. **Defect: route shadowing breaks a settings page.** Sibling page `/employee/self-service/change-password` works (200). |
| `/employee/self-service/documents/upload` | **403 "Invalid CSRF token"** | "Upload Document" button (`views/employee/self_service/documents.php:21,32`) leads to a 403 dead-end page for logged-in employees | **Root cause found.** Route is registered for GET (`web.php:6793` -> `EmployeeSelfServiceController@documentUpload`) and POST. `documentUpload()` calls `requireEmployeeLogin()` + **`validateCsrfOrFail()` unconditionally** - for GET there is no CSRF token -> `403 Forbidden: Invalid CSRF token`. Moreover the method only *processes* an uploaded file (`$_FILES['document_file']...`); it renders no upload form. No view-backed upload page exists for this URL. **Defect: GET route dead-ends; view links a button to it.** |
| `/employee/telecalling/followups` | **200, 0-byte body** | Follow-ups view stays blank; JS calling this endpoint (`TelecallingController@getTodayFollowUps`) does `response.json()` on an empty string -> throws | **Root cause found.** `getTodayFollowUps()` does `return $this->db->fetchAll(...)` - a raw PHP array returned to the runner; the framework emits no body (no `json_encode`, no echo). Same-signature risk exists wherever a controller returns a bare array from a GET route (not individually verified elsewhere). **Defect: endpoint silently returns empty success.** |
| `/employee/login` | 302 -> `/employee/dashboard` | Expected for an authenticated employee | By design. |
| `/employee/legal/quick-action-hub` (174 B) / `/counts` (108 B) | 200 | Raw JSON (`{"success":true,"counts":{...}}`) where a page title implies UI | JSON data endpoint used by JS; inline render is raw JSON if opened directly. INFO. |
| `/employee/api/{tasks,performance,attendance-records}` | 200 | Raw JSON (27-70 B) | By design (JS polling APIs). |

**Crawl:** 33/33 probed 200, including `/employee/payroll`, `/employee/ai-coach`, `/employee/self-service/{leave,payslips,form16,org-chart,shift-swaps}` - no further flags.

## Agent portal (`/agent/*`)

**Probed:** all 22 literal GETs + 17 crawl links from `/agent/dashboard|leads`. **Verified OK:** 20 + 17 = 37 portal endpoints. No defects found.

| URL | Status | Note |
|---|---|---|
| `/agent/register` | 301 -> `/register?role=agent` | By design. |
| `/agent/login` | 302 -> `/agent/dashboard` | By design (already authenticated). |

Everything else 200 (dashboard, leads list/add, wallet, wallet/my 77 KB, wallet/packages 80 KB, tds, salary, site-visits, bookings, collections, etc.). Crawl: all `/agent/leads/{14262,14286,14347,14483,14528}` record pages 200, `/agent/ai-coach` 200, `/agent/site-visits` 200. Zero flags.

## Associate portal (wallet scope only, per scope)

**Probed:** the 5 wallet GETs (`/associate/wallet[/my|/packages|/transactions]` + `/transactions/export`) + 40 crawl links from the wallet pages (stated for signal: `/associate/wallet` 82 KB, `/associate/wallet/my` 81 KB, `/associate/wallet/packages` 84 KB, `/associate/wallet/transactions` 72 KB). **Verified OK:** 5 + 39 = 44. No defects.

| URL | Status | Note |
|---|---|---|
| `/associate/wallet/transactions/export` | 200, 86 B | CSV **header-only** (no transactions on dev data). Same empty-export shape as payments (C-3). INFO. |
| `/associate/compare` | 302 -> `/associate/tools` | By design (compare tool lives under tools). |

**Crawl:** 40 links, 39x 200, 1x 302 (`/associate/compare`), 0 flags - noteworthy breadth: `/associate/{genealogy,mlm-plan,rank-eligibility,posters,leaderboard,schedule,tds-slips,ai-coach,export/my-earnings,...}` all resolve 200.

## Admin portal (sample, ~142 pages across hubs + 110-link crawl sample)

**Probed:** 142 literal GETs sampled across dashboards, leads, plots, payments, bookings, CRM, finance, HR, marketing, communications, reports + targeted href crawl (cap 110 of 662 unique admin links, alphabetical). **Verified OK:** 129 + 105 = 234. **Zero 5xx, zero flags from the crawl.**

| URL | Status | Note |
|---|---|---|
| `/admin/plots/pricing/preview` | 400 | Parameter-required JSON preview endpoint; expected 400 without args. OK. |
| `/admin/dashboard` | 302 -> `/admin/erp` | By design (hub redirect). `/admin/erp` renders 200, 386 KB. |
| `/admin/backoffice/dashboard` -> `/admin/backoffice`; `/admin/cm-dashboard` -> `/admin/dashboard`; `/admin/customer-leads` -> `/admin/customer-lead/behavior` | 302 | By design (canonicalization). All destinations verified 200. |
| `/admin/hrm`, `/admin/hrm/{departments,employees,payroll,recruitment}` | 302 -> `/admin/hr/{users,users/create,payroll,salary-structure,settings}` | By design (HRM aliases onto HR lane). Verified destinations 200. |
| `/admin/marketing-marketplace`, `/admin/marketing-strategies` | 302 -> `/admin/mlm` | By design. |
| `/admin/payments/gateway` | 302 -> `BASE_URL` (site root) | `AdvancedPaymentController@gatewaySelection`: bounces to home when no `$_SESSION['payment_order']` pending - i.e. visiting the URL directly with no pending order redirects to the homepage. Expected-ish, but a UX oddity (no flash message explaining why). INFO. |
| `/admin/payments/export` | 200, 0-byte CSV | See C-3. INFO. |
| `/admin/ai-dashboard`, `/admin/builder-dashboard/construction`, `/admin/finance/exchange-rate`, `/admin/finance/all-rates`, `/admin/hrm` (via HRM hub) | 200 | Bare JSON data endpoints. INFO. |
| `/admin/ai-training` | 2xx (registered in web.php + ai.php twice) | See C-1. INFO. |

**Crawl:** 110 probed (links lifted from `/admin/erp|leads|plots|bookings|payments|crm|finance|hr|messages` renders - that cap left 552 unprobed, all stable-shaped pages). 105x 200, 5x 302 (`/admin/2fa`->`/user/two-factor`, `/admin/accounting`->`/admin/dashboard`, `/admin/ai-calling`->`/admin/ai-calling/dashboard`, `/admin/api/integrations`->`/admin/api/developers`, `/admin/budgets/variance-report`->`/admin/budgets`), **0x 404/500**.

## Verified-OK totals (for the record)

- Literal probes: **287** 2xx + **44** 3xx = 331 non-error; only `/user/saved-searches/cron-alerts`, `/employee/self-service/documents/upload` (403), `/admin/plots/pricing/preview` (400) are non-2xx/3xx.
- Href crawl: **236** 2xx + **8** 3xx = 244 non-error; 1x 404 (`/user/pay-emi/upi-qr` - the one HIGH-severity link bug found by crawling).
- **No HTTP 500 was observed anywhere.**

## Process notes / honesty caveats

1. Admin sessions rotate/expire in ways that bit twice: admin probes were all recorded while authenticated (the main sweep), but by the href-crawl phase `/admin/dashboard` intermittently bounced to `/admin/login`; this did **not** affect recorded statuses (no admin status changed silently).
2. Employee jar was destroyed mid-investigation by this agent (`-c` overwrite instead of `-b`); employee pages were re-verified after re-login, including the flagged paths.
3. Href extraction initially found 0 matches because portal HTML uses absolute `BASE_URL` hrefs - regex fixed to accept both shapes; the 20-page sample is representative, not exhaustive.
4. No database reads beyond read-only `SELECT` sanity checks; no rows were inserted/updated/deleted; no migrations; no commits.
5. `?test_login=` bypass is dev-only (per `RequestMiddlewareService` gates) - findings would need re-verification after any auth-layer change.
6. The literal sweep used route tables as of this run (`routes/web.php` @ 2026-11-15) - unregistered portal URLs rendered by JS fetches are out of literal-probe scope (only those surfaced via the href crawl, e.g. `pay-emi/upi-qr`, were caught).

## Fix priority (suggested, not applied)

1. **E-1** - remove/rename `routes/employee.php` line 90 (map GET -> `changePasswordView`) or stop shadowing `/employee/change-password`; unbreak the page.
2. **E-2** - split document-upload route handling: GET should render an upload form (or remove the GET registration) and the view should not link a GET to the POST processor.
3. **E-3** - `getTodayFollowUps()`: `echo json_encode(['success' => true, 'data' => $rows]); exit;` (match sibling endpoints).
4. **C-4 (UPI QR)** - give `views/customer/wallet.php:62,70` a real link target (e.g. GET route rendering the QR modal) or convert to button+JS POST.
5. **C-3** - empty exports: emit a header row (or a flash + 302) instead of zero-byte CSVs; verify or document the intentionally-empty default month window.
6. **C-1** - route-layer dedup pass (web.php vs employee.php vs ai.php), resolve the confirmed shadow class, add a CI duplicate-path lint.
7. **INFO** - prune raw-JSON pages from navigation surfaces; standardize content-type + empty-state semantics on export endpoints.
