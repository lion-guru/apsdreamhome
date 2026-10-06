> **COMPLETED (2026-10-06):** Churn-stop verified (snapshots identical), full review done, all work committed + pushed (see Session 171-172 below).

## Session 172: Full-Tree Commit — Both Workstreams Reviewed + Pushed (2026-10-06)

### Trigger
User: "ab sara kaam tumhi karo" — both tabs' work to be reviewed, committed and pushed by this session. Prior tab closed; second-actor churn verified stopped (380-entry snapshots identical across lint+master+probe window).

### Review (before commit)
- Full `app/` lint: **3067 files, 0 errors**
- `testing/master_test_runner.php` **6/6** ✅
- Re-verified Sessions 165–170 live: 14 pages + flag-check API + explain (real EXPLAIN rows) + widget save→delete→404 cycle — all green
- Reviewed each staged hunk: HR/Salary controllers (verified own workstream), routes/web.php via filtered patch (2 removals + 4-line investment dependency + 2 new routes only)
- Excluded from commit: `.env.production` (live secrets), 36 root scratch scripts, `storage/logs/*.json` test reports
- DB changes (migrations already applied live) are not in git by nature: `feature_flags`+rows, `user_dashboard_layouts`, `salary_structures.da`, `salary_plans`, `leave_accrual_log`, payout columns, Plot Development menu row

### Commit
- `a9ed96b6d` (HR round, 41 files — pushed earlier) + this session's full-tree commit (pushed, `ls-remote` verified)

### Key Lessons (carried, continued)
_435. **Count `?` placeholders programmatically, never by eye** — an Elvis `?:` in params inflates visual counts; flash-SQL + direct-CLI both agreed while eyes kept miscounting 7 vs 8.
_436. **`$employeeId` vs `$employee_id` is a silent null** — PHP undefined variable binds NULL into the INSERT; flash-SQL looks perfect while the value is missing. Check names, not counts.
_437. **Never `git checkout -- <file>` in a churn tree** — restores index version and wipes newer worktree edits; revert via edit tool instead.
_438. **PowerShell `Set-Content -Encoding UTF8` writes BOM** — breaks `namespace` parsing; use .NET UTF8-no-BOM writes for repo files.
_439. **Clock skew mimics stale code** — shell IST vs PHP Berlin vs MySQL SYSTEM are the same instant in different zones; verify with `has_debug`-style live markers before theorizing caches/servers.

## Session 171: HR/Salary/Employee Deep-Audit + Fixes (2026-10-06)

### Trigger
"do next" autonomous gap-hunt across HR/salary/employee/customer/associate/agent areas.

### Fixes (all probe-verified, committed in a9ed96b6d + this session)
- generatePayslip prefers approved structures (+DA bucket); LOP unions both leave tables; portal id-normalize; paid-guard + versioning revert
- HR salary-structure page → canonical table (+`da` col/migration, ghost cleanup); salary payments mark-paid/cancel lifecycle; payment_view blank-key fix
- `salary_plans` table created; contract number/ctc/placeholder/typo fixes; tracker rewired to ledger + enum whitelist
- Arrears: FK-1452, generated-col 1906, single-active retire, preview GET/POST, DA math (8/8)
- F&F: structure-NULL, terminated enum, assets POST-id, downloadForm16 var+path, Form16 id-map (7/7)
- Attendance phantom-col cluster + employees.id JOINs + datetime + headcount; backoffice leaves JOINs (5/5)
- OT approve/mobile-403/weekly off-by-one; DB session TZ lock; 15 dead POST forms tokened (OT/advances/reimb/arrears/shift/F&F)
- ESS: 8 CSRF forms, profile/payslip 500s, webroot uploads, proof form + HR proofs table, Form16 id-map, decl/tax 6/6
- Documents cluster (JOIN/webroot/keys/proofs) + UploadValidator ×7 namespace fatals; ESS payslip privacy gate; report/CSV phantom keys
- Payouts table aligned via migration (5/5); accrual idempotency guard + pro-rate fix (3/3); periods UI + reopen (4/4); batch-process 6/6; plans/contract/tracker misc 8/8; payslip PDF 6/6

### Verification
- Lifecycle probes all green; HR 35/35, salary 13/13, customer 20/20, associate 19/19, agent 9/9, workflow 15/15, master 6/6, logs clean, 0 leftovers

## Session 169: Session-166/167 Re-verification — Health/DB/API-Docs Pages + Investment API Full Cycle (2026-10-06)

### Trigger
"do next" — autonomous continuation. Second-actor churn at 347 modified files; re-verified Session 166 pages (Database Monitor, System Health, API Docs) and Session 167 mobile investment API before they could silently regress.

### Findings
- All Session 166 pages live: **database, database+search, health, api-docs — all 200** with content needles (`Database Monitor`, `System Health`, `API Documentation`).
- Investment API full cycle green: plans (4 plans, first active id=9 min ₹5000) → user investments → empty-payload 400 → create (inv #8, ₹5000) → cancel (refund ₹4500 = 90%, matches 10% early-cancel <365d lock-in rule) → DB row `cancelled`, no orphan.
- `invest()` moves no wallet money (insert-only) — probe create/cancel is side-effect-clean by design; verified row state directly.
- No app code changes needed. No regressions from the 347-file churn in these areas.

### Verification
- S169 probe **11/11 PASS** (4 pages + login + 6 API checks)
- `testing/master_test_runner.php` **6/6** ✅ (14.1s)
- Temp probe scripts deleted, no commit

### Key Lessons (carried, continued)
_428. **Refund math is the business-logic proof** — asserting `refund == 90%` validates the lock-in rule end-to-end; a bare `success:true` would not.
_429. **Read the service before probing money-adjacent endpoints** — `invest()` is insert-only (no wallet movement), so create→cancel probing is safe; never assume, always read first.

---
## Session 168: Session-165 Re-verification Probe + Transient Lint Scare (2026-10-06)

### Trigger
"do next" — autonomous continuation. Re-verified Session 165 deliverables (Feature Flags GUI, Audit-Log Views, IT Support Toolbox) via authenticated HTTP probe since second-actor churn was ongoing.

### Findings
- All Session 165 pages live and correct: **11/11 PASS** (login 302 → feature-flags ×3, audit-log ×5, it-support, flag-check API `"enabled":true`).
- Probe initially failed: session cookie not preserved across curl requests (no cookie jar) → all pages 302. Fixed harness with `CURLOPT_COOKIEFILE/JAR`.
- 3 wrong content needles in first probe draft (guessed titles); corrected against actual views: `Audit Event Detail`, `User Timeline`, `Audit Statistics`.
- Transient `php -l` failures on 4 app/Services files (`SystemConfigService`, `PasswordStrength/RememberMe/TwoFactor`) — all passed on immediate re-run. Second-actor mid-edit states, not real bugs. `add_methods2.php` (root scratch) genuinely broken but deliberately untouched (second-actor clutter per Session 166 note).
- No app code changes needed.

### Verification
- Session-165 probe **11/11 PASS** (all 200 + content needles, full layout)
- `testing/master_test_runner.php` **6/6** ✅
- `php -l` clean on probe script; temp probe + cookie files deleted, no commit

### Key Lessons (carried, continued)
_425. **Cookie jar is not optional for authed probes** — `CURLOPT_COOKIEFILE/JAR` on every request including login; a 302-everywhere result means the harness lost the session, not that auth is broken.
_426. **Read the view for the needle, don't guess the title** — controller `page_title` ('Audit Log Detail') vs view `$page_title` ('Audit Event Detail') can differ; grep views/ for `page_title` first.
_427. **Re-lint before fixing lint errors** — a file failing `php -l` at 10:01 and passing at 10:02 was never broken; second-actor bulk-writes create transient parse states. Evidence (_415) again.

---
## Session 170: Full-Track Re-verification Sweep (18/18) + Sidebar 258-OK During Active Churn (2026-10-06)

### Trigger
"do next all" — churn still active (367 modified + 175 untracked, up from 359+173). Probe-only round, zero app edits.

### Findings
- Own track all green: **feature-flags ×3, audit-log ×5 (detail #1 + user timeline), activity-log, it-support, database, health, api-docs, query-analyzer, plots/development, dashboard/customize — 15/15 pages** ✅
- APIs: **flag-check `"enabled":true` + widget save→delete cycle (probe row cleaned)** ✅
- Full sidebar: **258 OK, 0 FAIL** (2 small-200s = known by-design: careers empty-state, ai-calling JSON) ✅
- `testing/master_test_runner.php` **6/6** ✅ — no regressions from churn in any owned area

### Verification
- Combined sweep **18/18 PASS**; temp scripts in temp-dir only, deleted after; no commit (standing gate holds)

### Key Lessons (carried, continued)
_430. **Re-probe churn-adjacent pages on every autonomous round** — cheap HTTP checks catch silent regressions while second-actor bulk-edits are in flight; probe-only, never touch their files.

---
## Session 167: Withdrawal Money Bugs + Probe-Harness Fix (16/16) + Mobile Investment API + Flutter Rewrite (2026-10-03)

### Fixes (all probe-verified)
| # | Fix | Files |
|---|-----|-------|
| 1 | Associate withdrawal 4 bugs (own unprobed code): `notes`→`remarks`, `type`→`transaction_type`/`transaction_category`, INT `reference_id`, missing `TenantAwareTrait`, phantom-`type` summary query | `Associate/WalletActivationController.php` ✅ |
| 2 | Customer withdrawal never deducted (pre-existing money bug) → deduct-at-request + bank-verify + ledger; admin reject role-routed refund (`users.role`) | `WalletController.php`, `Admin/MlmRewardsController.php` ✅ |
| 3 | CSRF 302s on withdrawal POSTs (router runs before controller skip) | `routes/router.php` exclusions, `WalletController::skipCsrfProtection` ✅ |
| 4 | Workflow probe 10/15→**16/16**: `areq()` missing `CURLOPT_HEADER` (truncated bodies) + inquiry form-vs-JSON; duplicate login block left untouched (second-actor) | `testing/workflow_probe.php` (harness-only) ✅ |
| 5 | Mobile investment API (4 endpoints, Bearer pattern) + probe 3/3 | `Api/MobileUserApiController.php`, `routes/api.php` ✅ |
| 6 | Flutter screens rewritten to codebase conventions (singleton `ApiService`, no provider pkg); deleted wrong-pattern files; reverted main/pubspec | `investment_{plans,customer_investments,form}_page.dart`, `app_router.dart` ✅ |
| 7 | Withdrawal approval UX: colored modals + context + spinners + Esc/Ctrl+Enter | `admin/withdrawal/{index,view}.php` ✅ |

### Verification
- Withdrawal full-cycle probe **13/13**, workflow **16/16**, master suite **6/6**, `flutter analyze` on touched files **0 errors** ✅

### Verification (Session 167 sweep, 2026-10-06)
- Page sweep **16/16 PASS** (6 admin + 4 associate + 5 customer + cleanup) + withdrawal detail view PASS
- Agent wallet pages **4/4**; activation direct-probe **7/7** (L1+L2 paid, 30% clamp, idempotent); purchase endpoint **4/4** (400-path + pending row)
- Investment web cycle probe **5/5** (invest→active→cancel→90%-refund-math→cleanup, insert-only by design); simulator preset round-trip **6/6** (save→listed→DB→load-JSON→delete→gone)
- Simulator Apply probe **9/9** (referral 2→2.5→restore, wallet L1/L2 bulk→restore, audit written+cleaned); mark-read probe **5/5** after schema fix
- Mobile investment API full cycle **8/8** (login→401-no-token→400-empty→create→active→cancel→cancelled→cleanup; no app changes this round, master re-run skipped deliberately)
- CSV exports + leaderboard JSON **7/7** (admin withdrawals CSV, associate transactions CSV w/ seeded row, leaderboard `/data`); route-concat warts cleaned (2 spots); master **6/6** re-verified
- Caught own bug: `markAllInquiriesRead` wrote `status='read'` but enum has no such value — added `is_read` column (+index) instead; view shows unread count/badge/button. Also found live `/user/inquiries` renders hardcoded 2024 demo rows (shadow route) → wired to real DB query
- `testing/master_test_runner.php` **6/6** ✅ (re-verified after sweep fixes)
- Temp probe scripts deleted; **commit NOT done** (churn active, per pending instruction)

### Key Lessons (carried, continued)
_411. **Probe every money path you write** — `php -l` + master suite never hit the new endpoints; full-cycle probe (request→deduct→reject→refund→approve→holds+cleanup) caught all 4 own-bugs.
_415. **A 200 with an empty body is a distinct failure mode — assert needles, not just status.** Admin page returned 200-empty (wrong layout include); only the content-needle check caught it.
_416. **Exit-255 with no output means uncatchable compile error — use a shutdown handler.** `error_get_last()` in `register_shutdown_function` named the `view()` signature collision instantly; logs had nothing.
_417. **A child method name that collides with a base method is a compile error, not an override** — `view($id)` vs `view($view,$data=[])`. Grep base-class method names before naming controller actions (`show` is the codebase convention).
_418. **Count placeholders against params, not vibes** — `$params` already held `user_id` while `$filterParams` re-added it (HY093). Rule: filter arrays hold ONLY filter values.
_412. **Harness-vs-app: direct-call control first** — endpoints returned success=true via curl while probe showed HTTP-200-empty; `git diff` on harness confirmed second-actor rewrite; fixed harness-only.
_413. **Match the codebase's state religion first** — ChangeNotifier in a riverpod codebase compiles nowhere; 5-min sibling read saves a full rewrite. Backend-first, probe endpoints, then bind UI.
_414. **Hexdump beats rendered text** — `]`→`}` byte corruption invisible in Read output; byte inspection found it instantly.

---
## Session 162: Admin Sidebar Multi-Tab Menu Diagnosis + Session/UX Hardening (2026-10-03)

### User report
Admin logged in, many pages open in tabs → sidebar menu "changes / shows different menus".

### Diagnosis (browser-verified, not assumed)
- Server menu is **stable**: curl probe (7 pages) + agent-browser 5-tab fingerprint — all pages identical **289 links, same hash, 5/5 hubs open**, no offline badge. No bug in `AdminMenuService::getMenuItems()` per page.
- Real cause #1 (reproduced): sidebar fold state lived in **`localStorage['adminSidebarSections']` (shared across ALL tabs)** — collapsing a section in tab A folded it in untouched tab B after reload (`closedSecs=1/28`, same link hash). Looks exactly like "menu changed".
- Real cause #2 (latent): role read order differed in 3 places — menu service `admin_role`-first (`RBACManager::getUserRole()`), guard + hub-collapse `role`-first. Agree only when both keys equal; diverge after GodMode switch.
- Real cause #3 (transient, observed once): concurrent Chrome pollers + single PHP session file → one tab's render briefly missed session state (banner absent once, present on deterministic re-probe). Classic file-session race under many tabs.

### Fixes (all probe-verified)
| # | Fix | Files |
|---|-----|-------|
| 1 | Fold state `localStorage` → **`sessionStorage`** (per-tab) + one-time orphan migration + **Reset View** button | `layouts/admin.php`, `public/assets/admin/js/admin.js`, `admin/layouts/rbac_sidebar.php` ✅ |
| 2 | Role-read unified to `RBACManager::getUserRole()` | `Admin/AdminController.php` (guard), `BaseController.php` (`hasRole`/`isAdmin`), `rbac_sidebar.php` (collapse) ✅ |
| 3 | GodMode `'superadmin'` typo → `'super_admin'` (allowlist + dashboard map; old value set a nonexistent role = empty menu) | `Admin/GodModeController.php` ✅ |
| 4 | Impersonate stores + restores **both** role keys (mixed sessions restore faithfully); admin-sidebar banner + shared portal banner partial with Back-to-Admin | `Admin/UserController.php`, `rbac_sidebar.php`, `components/impersonation-banner.php` (new), 4 portal layouts ✅ |
| 5 | Explicit `Cache::delete("admin_menu_perms_{userId}")` on grant/revoke (pattern-clear not guaranteed per layer) | `Services/AdminMenuService.php` ✅ |
| 6 | "Acting as" badges in admin header (impersonate / GodMode role / GodMode impersonating / tenant switch) | `layouts/admin.php` ✅ |
| 7 | Read-only pollers release session lock (`session_write_close`) — `/api/notifications`, `/unread-count`, `/api/chat/poll|widget|history` | `NotificationController.php`, `Front/LiveChatWidgetController.php` ✅ |

### Verification
- Per-tab isolation proven: collapse in tab B → tab A reload `0/28`, tab B reload `1/28`; Reset → `0/28` + storage cleared ✅
- Impersonation cycle probe: impersonate→portal banner→admin banner→stop→banner gone, all 302/200 exact ✅ (also proves `stopImpersonation` reachable — second-actor `requireAdmin` guards + unify cooperate)
- `php -l` 14 files clean, `node --check` clean, `testing/master_test_runner.php` **6/6** (×2 runs) ✅
- Note: workdir carries large unrelated uncommitted changes (second actor) — this session touched only the files above; **no commit** (not requested).

### Key Lessons (carried, continued)
_407. **Fold/filter state shared across tabs reads as "menu changed"** — any `localStorage` UI state leaks between tabs by design; per-tab view state belongs in `sessionStorage`. Prove with cross-tab fingerprint (hash links + count closed sections), not screenshots.
_408. **Dual session role keys need ONE resolver on both read and write paths** — `role` vs `admin_role` order differed per file; `RBACManager::getUserRole()` is the single source. Audit writes too (impersonate stored role-first, guard read role-first — the pair accidentally worked until unify flipped one side).
_409. **Pollers must `session_write_close()`** — everyN-second fetchers on every tab serialize on the session file and rewrite it with stale reads at shutdown; read-only endpoints should drop the lock right after auth.
_410. **A view-partial edit can land inside a loop — diff-review placement, not just syntax** — my banner first rendered once per section (php -l clean, visually plausible); only `git diff` placement review caught it. Count rendered occurrences in the probe.
_411. **Second-actor `requireAdmin` sweep + my unify interact** — their guards assume the guard passes mid-impersonation; unify (admin_role-first) is what keeps `stopImpersonation` reachable. Verify combined behavior with the full impersonate→stop cycle probe, not either change alone.

---
## Session 161: Portal Nav Links + P3 Referral Tiers to DB + Admin Tier Edit (2026-10-02)

### Work Done

| # | Feature/Fix | Root cause / Design | Resolution |
|---|-------------|---------------------|-----------|
| 1 | Portal navigation for new pages | Threads/wallet/packages reachable only by direct URL (widget buttons aside); associate/agent wallets are a different system (`user_wallets` vs `wallet_points`) — deliberately untouched | Customer menu: Message Threads (Main) + My Wallet & Activate Wallet (Finance); cross-link banner on `user_inquiries` view ✅ |
| 2 | P3: `referral_tiers` DB table | Last hardcoded rates (Bronze/Silver/Gold/Platinum thresholds + bonuses in `getTiers()`) | Table (idempotent setup + seed matching current values) + `getTiers()` DB-first with hardcoded fallback + per-request static cache + `clearTiersCache()` ✅ |
| 3 | Admin tier editing | No UI to tune tiers without deploy | Per-tier edit forms on existing tiers page → `ReferralController@updateTier` (clamped, audited via `auditManual`, flash + redirect); existing `tiers()`/`getUserTier()` untouched ✅ |

### Bugs caught by probes (all fixed, all probe-verified)
| # | Bug | Fix |
|---|-----|-----|
| P | New `getTiers()` static cache served stale rows after HTTP edit within one process (probe caught it) | `clearTiersCache()` on write; web requests are fresh-process anyway ✅ |

### Verification
- Tier probe: **8/8** (page + forms render, DB-driven read, HTTP edit applies + reflects + audited, restore, cleanup) ✅
- Nav probe: **4/4** (customer sidebar contains all 3 new links) ✅
- `testing/master_test_runner.php`: **6/6** ✅

### Key Lessons (carried, continued)
_405. **Static caches need explicit invalidation on write, even when "safe"** — web requests are fresh processes (stale impossible), but CLI probes/long workers share one process. `clearTiersCache()` on every write path; probe with clear-then-read to simulate fresh requests.
_406. **Don't wire menus across different wallet systems** — customer `wallet_points` (activation/EMI/referral) vs associate/agent `user_wallets` are separate ledgers; adding activation links to associate/agent menus would promise features their wallet backend lacks. Scope nav to the system that serves it.

---
## Session 160: Social Login Everywhere + Inquiry Resurrection + Threads + Ref Cookie + Claim-Booking + Simulator + Audit (2026-10-01)

### Work Done

| # | Feature/Fix | Root cause / Design | Resolution |
|---|-------------|---------------------|-----------|
| 1 | Social login on ALL register views + core_login fix | Associate/agent had OAuth; customer/core/unified/register had zero; core_login "Google" button actually opened OTP (mislabeled) | Real `/auth/google` + `/auth/facebook` buttons on all 4 register views; core_login split into Google/Facebook OAuth + Email/Phone OTP rows ✅ |
| 2 | Web inquiry resurrected (`/property/interest`) | Handler inserted row but: company-table-only, no seller notify, no user/ref linkage, no thread | Dual-table resolve (`properties` + `user_properties`, FK-safe NULL + listing_id), `user_id`/`listing_type`/`listing_id`/`referral_code` cols (self-heal DDL), seller push, auto chat thread for logged-in buyer + known owner ✅ |
| 3 | Dead `/property/inquire` (compare-page link → stub → /contact) | Handler flashed a message and redirected; GET route never existed | GET renders real inquiry form view; POST delegates to fixed interest handler; GET route added ✅ |
| 4 | 30-day `aps_ref` cookie net | `?ref=` died on browse-first-register-later + Google/OAuth hop + OTP flow | Set in `public/index.php` on every hit (sanitized); read in core/smart-OTP/Google/associate/agent registrations + inquiry; pre-fill preserved ✅ |
| 5 | Claim-My-Booking flow | Office/associate bookings create password-less stub users; customers couldn't log in, bookings orphaned, `referred_by` never set | `ClaimBookingController` (form → OTP → verify+password → safe link by phone → referral backfill from `associate_id` → login); 4 routes; prospective-only 2% via normal pipeline ✅ |
| 6 | Entire OTP system revived | `OTPService` + 7 services call `$this->tVal()` which **does not exist** in `ServiceTenantTrait` → every sendOTP/verifyOTP fatal'd (all OTP flows dead) | Added `tVal()` alias (= `tenantParams()`) to the trait; class-level private `tVal()` overrides keep precedence (verified DailyOperationsService) ✅ |
| 7 | `otp_verifications.purpose` ENUM rejected new flow | ENUM lacked `claim` → 1265 truncation throw | Idempotent ALTER adds `claim` ✅ |
| 8 | Simulator sweep tabs (P1) | Admin could change knobs but not preview impact | `referralSweep()` (replay paid bookings @ X% vs baseline) + `walletSweep()` (replay activations @ L1/L2, margin table) in `CommissionSimulator`; 2 modes + result blocks on existing simulator page; read-only ✅ |
| 9 | Config audit (P2) | `service_configs.set()` had no who/when trail | `service_config_audit` table (setup script) + best-effort logging in `set()` (skips no-ops, never throws) + `getAuditHistory()` + `/admin/service-configs/history` view ✅ |
| 10 | Web buyer-seller threads (P2) | Mobile had chat; web had nothing after inquiry | `inquiryThreads` (sent + received w/ unread) + `inquiryThreadDetail` (participant-gated, marks read) + `inquiryThreadReply` (push) in `Front\UserController`; 2 views; 3 routes ✅ |

### Bugs caught by probes (all fixed, all probe-verified)
| # | Bug | Fix |
|---|-----|-----|
| P | `user_properties` has `name`, not `title` — my inquiry/thread queries 1054'd on user listings | `name AS title` alias (2 sites) ✅ |
| Q | Probe forgot `csrf_token` on thread-reply POST (real form sends it) | Probe sends token like the view ✅ |
| R | Probe asserted 200 for stranger thread access — correct behavior is 302-away | Assert 302 + no leak ✅ |
| S | Curl jar files never persist in this env (silent) → session lost between probe requests | Manual `PHPSESSID` capture + `Cookie:` header in probes ✅ |
| T | Probe asserted 404 for unknown property; `BaseController::response()` sets codes correctly — but my query died first on the phantom `title` col (same root as P) | Fixed by P ✅ |

### Verification
- Inquiry probe: **11/11** (company row + tracking fields, 400/404 paths, inquire form renders, cleanup; user-listing path skipped — table empty) ✅
- Thread probe: **11/11** (listing → inquiry → auto-thread buyer→seller → seller list/detail → reply → buyer sees → stranger 302-blocked → cleanup) ✅
- Claim probe: **14/14** (form, unknown-phone reject, OTP row, wrong-OTP reject, verify→dashboard, account+password+link+referral-backfill+wallet, no retroactive commission, cleanup) ✅
- Simulator probe: **8/8** (admin page + both sweeps render numbers, clamp behavior) ✅
- Config-audit probe: **6/6** (create/change audited, no-ops skipped, history reader, knobs live, cleanup) ✅
- History page: **200 + renders** ✅
- `testing/master_test_runner.php`: **6/6** ✅ | `flutter analyze` touched page: **No issues found** ✅

### Key Lessons (carried, continued)
_397. **Dead handlers hide behind live routes** — `/property/inquire` had a route + view link but the handler was a flash-and-redirect stub; the listing modal's sibling endpoint worked, masking it. Grep every route's handler body, not just its registration.
_398. **FK-constrained id columns can't carry cross-table ids** — `property_inquiries.property_id` FKs to `properties.id`; stuffing a `user_properties` id risks 1452-fail OR silent wrong linkage. Pattern: `property_id=NULL` + `listing_type`/`listing_id` sidecar columns.
_399. **A missing trait method fatals every caller, silently for years** — `tVal()` absent from `ServiceTenantTrait` while 60 call sites across 8 services used it; each died only when its path executed. Prefer adding the alias once over editing 60 sites; verify class-level overrides keep precedence.
_400. **Curl cookie jars are unreliable in this PHP build** — jar file never materializes despite `Set-Cookie` arriving (verified via verbose). Probes must capture `PHPSESSID` from response headers and send explicit `Cookie:` headers.
_401. **Probe the access-control negative with the right status** — a correct block is a 302-away, not a 200-without-content. Asserting 200 on a block check false-reds a working gate.

---
## Session 159: Universal Referral Program + Wallet Activation Packages + Mobile API/UI (2026-10-01)

### Work Done

| # | Feature/Fix | Root cause / Design | Resolution |
|---|-------------|---------------------|-----------|
| 1 | Employee Agent removed from public registration | Employee Agent is HR/Admin-hired, not self-serve; public option invited unvetted in-house signups | `core_register.php`: freelancer-only + Careers-portal note; `RegisterController` forces `freelancer` ✅ |
| 2 | Terms alert firing on role-switch (not submit) | Role cards were `<button>` without `type` → default `submit` → form validation ran on every role click | `type="button"` on all role cards (`core_register.php`, `smart_register_role.php`) ✅ |
| 3 | Social login invisible on register pages | Google/Facebook buttons only on login views | Added Google+Facebook buttons + CSS to `associate_register.php`, `agent_register.php` ✅ |
| 4 | Customer→customer referrals earned nothing | No commission path for customer referrers on real business | `ReferralService::processCustomerReferralCommission()` — 2% on booked+paid bookings, wallet credit, idempotent; wired into `BookingLifecycleService::recordPayment()` ✅ |
| 5 | Associate activation paid on registration (fake-reg risk) | Bonus on signup invites fake accounts | `processAssociateActivationBonus()` pays ₹500 only on first_booking / first_team_member (distinct ledger types); wired into `createBooking()` + `createNetworkTreeEntry()` ✅ |
| 6 | Wallet activation packages (new revenue stream) | Wallet features open to all, no commitment filter, no referrer incentive | `wallet_activation_packages` (Basic ₹499/Pro ₹1499/Premium ₹2999) + `wallet_activation_purchases` + `wallet_points` cols; `WalletActivationService` (purchase/Razorpay-order/activate/referral-pay); `WalletActivationController` + 2 views + 4 web routes ✅ |
| 7 | Referral earnings invisible on dashboards | Earnings scattered across ledger, no per-user breakdown | `ReferralService::getReferralEarningsBreakdown()` + reusable `components/referral_earnings_widget.php` on user/associate/agent dashboards (controllers pass data) ✅ |
| 8 | Mobile APIs for wallet/referral | Flutter had no endpoints for new flows | 8 endpoints in `MobileUserApiController` + routes (`wallet/activation/*`, `wallet/balance`, `referral/earnings|leaderboard|share-url`); 12 constants + 8 methods + 4 URL helpers in Flutter `AppConstants`/`ApiService` ✅ |
| 9 | Flutter UI pages | New backend flows unreachable from app | `wallet_activation_page.dart` (packages + razorpay_flutter checkout + verify) + `referral_earnings_page.dart` (summary/breakdown/recent/share/leaderboard); routes `/wallet-activation`, `/referral-earnings`; home Tools + profile More-Features links ✅ |

### Bugs caught by probe-first discipline (all fixed, all probe-verified)
| # | Bug | Fix |
|---|-----|-----|
| A | `WalletActivationService` used `Payment\RazorpayService` with wrong args (`price, description, userId` vs `amount, currency, receipt, notes`) + `verifyPaymentSignature` (nonexistent there) | Switched to canonical `Gateway\RazorpayService`; returns `order_id` + `key_id` for Flutter checkout ✅ |
| B | `activatePurchase` used undefined `$packageId` in wallet UPDATE | `$purchase['package_id']` ✅ |
| C | `Class "App\Services\PDO" not found` fatal in `WalletActivationService::getActivePackages` | Missing `use PDO;` (custom autoloader doesn't fall back to global — same class as lesson 152); added to `WalletActivationService` + `UserRegistrationService` (my new method was its only `PDO::` user) ✅ |
| D | Phantom ledger columns `reference_id`/`reference_type`/`trigger_type` (DESCRIBE: don't exist) | Ledger writes use real cols only (`booking_id`/`notes`); trigger split into distinct types `associate_activation_booking`/`_team` ✅ |
| E | `wallet_transactions.transaction_category` is strict ENUM (`referral\|commission\|bonus\|emi_transfer\|withdrawal\|adjustment`) — custom categories would 1364/throw | All wallet credits use `'referral'`; detail rides in description/notes ✅ |
| F | Flutter `ApiService` methods landed outside the class (insert anchor matched `AuthInterceptor` instead of class close) | Re-anchored insert before `ApiService` closing brace; `flutter analyze` 0 errors ✅ |

### Verification
- `testing/master_test_runner.php`: **6/6** ✅ (before + after)
- Targeted wallet probe: **12/12** (packages→purchase→activate→referral ledger+wallet→breakdown→cleanup, 0 rows left) ✅
- Targeted associate-activation probe: **10/10** (both triggers, idempotency, wallet=₹1000, breakdown keys, cleanup) ✅
- `flutter analyze`: **0 errors** project-wide (263 pre-existing infos only; my files clean) ✅
- Scratch scripts removed from `scripts/` (own probes only); Temp probes live outside repo ✅

### Key Lessons (carried)
_381. **DESCRIBE every table you INSERT into, even "known" ones** — `mlm_commission_ledger` has no `reference_id`/`trigger_type`; `wallet_transactions.transaction_category` is a strict ENUM. Two phantom-column bugs + one enum violation caught only because the probe asserted DB state, not HTTP status.
_382. **`use PDO;` is mandatory in namespaced files here** — the custom autoloader resolves `PDO::` to `App\Services\PDO` and fatals instead of falling back to global (lesson 152 again, new file same trap). Any new service/controller with `PDO::` needs the import; audit with grep before first run.
_383. **Canonical gateway is `Gateway\RazorpayService`, not `Payment\RazorpayService`** — different signatures AND return shapes (`['success','data']['id']` vs raw apiCall). Grep all `createOrder` call sites before wiring a new payment flow.
_384. **Anchor scripted inserts on unique structural markers, then verify placement** — my ApiService insert matched `class AuthInterceptor` and landed top-level (methods invisible to analyzer). Re-anchor on the class closing brace + verify with `flutter analyze` immediately.
_385. **Pay referral rewards on business events, never on registration** — 2% on paid bookings, ₹500 on first booking/team-member, wallet rewards on activation purchase. Registration-time payouts invite fake accounts; activation-gated payouts filter for commitment.

---
## Session 163: Admin Menu Permission System + Role-Wise Menu Audit + CMS/Location Visibility Fix (2026-10-03)

### User report
- "Admin sidebar menu changes on multi-tab" → fixed in Session 162.
- "Kisi user ko extra menu permission de sakte hain — uska workflow verify/fix" — grant/revoke cycle verified end-to-end.
- "Content CMS aur Location menu kabhi dikhta kabhi gayab + view not available pages" — CMS/Location pages now stable 200, Menu Permissions page now accessible to admin (was 302 redirect).

### Fixes (all probe-verified)
| # | Fix | Files |
|---|-----|-------|
| 1 | **Extra menu grant workflow** — Admin can now grant custom menu items to any user via `/admin/menu-permissions` (previously super_admin only). Custom grants now **ADD** missing items to user's tree (was only restricting). Grant→verify→revoke cycle tested. | `AdminMenuPermissionController.php` (role gate), `AdminMenuService.php` (`mergeCustomGrantedItems`), `menu-permissions/index.php` (CSRF) ✅ |
| 2 | **Role-wise menu audit** — All 10 roles verified: admin 289 items, manager 59, employee 42, telecaller 40, ceo 66, sales_manager 45, accountant 16, associate 44, agent 43, customer 32. Every role sees only work-relevant sections. | `AdminMenuService.php` (role-perm INNER JOIN), `RBACManager.php` (role matrix) ✅ |
| 3 | **CMS + Location menu stability** — 13 CMS/Location URLs all return 200 with correct titles. Location parent "Locations" (id=82) was OFF but children active; now children render correctly. "Menu Permissions" page was 302 (super_admin-only gate); now admin can access. | `AdminMenuPermissionController.php` (role gate), `admin_menu_items` (is_active) ✅ |
| 4 | **Custom menu grants ADD items** — `AdminMenuService::mergeCustomGrantedItems()` merges granted items the user's role doesn't include (previously INNER JOIN dropped them). Grant probe: manager(4) sees "Pages Content" after grant, gone after revoke. | `AdminMenuService.php` (`mergeCustomGrantedItems`) ✅ |

### Verification
- Grant cycle probe: grant→list→revoke all 200 ✅
- Role menu probe: 10 roles, correct item counts, 0 overlaps ✅
- CMS/Location probe: 13/13 URLs 200 with correct titles ✅
- Menu permissions page: 200 (was 302) ✅
- `php -l` clean, `node --check` clean, `testing/master_test_runner.php` **6/6** (×3 runs) ✅

### Key Lessons (carried, continued)
_412. **INNER JOIN drops custom grants** — `getMenuItemsByRole()` uses INNER JOIN on `admin_role_menu_permissions`. If a user's role lacks a permission, the item never appears in the tree — even if a custom grant adds it. Fix: merge granted items AFTER the role query.
_413. **Admin should be able to grant menus** — Locking grant UI behind super_admin was a UX blocker; admins need to give work-relevant menus to team members. Added `ROLE_ADMIN` to the gate in 6 endpoints.
_414. **Verify at sidebar render time, not just API** — API said grant succeeded; manager's sidebar didn't show the item. Probe must check `AdminMenuService::getMenuItems(role, userId)` end-to-end.

---
## Session 164: Workflow Probe Fix + Full Test Suite Verification (2026-10-04)

### Trigger
`testing/workflow_probe.php` was failing with empty login token despite HTTP 200, cascading 401s on authed endpoints, and wrong count expectations for colonies/properties browse.

### Root Cause
Transient environment issue (second-actor concurrent DB edits / session race) — probe code itself was correct (`$d['data']['token']` path, header-size split + BOM strip, `data.properties` shape). Standalone verification `check_structure.php` proved token flow worked.

### Fix
No code changes needed — the probe was correct. Transient failure resolved (likely second-actor DB commit completed). Verified by re-running probe and master suite.

### Verification
- `testing/workflow_probe.php`: **15/15 PASS** (login→properties→favorites→inquiry→colonies→dashboard→notifications→payment-history→profile + DB integrity)
- `testing/master_test_runner.php`: **6/6 PASS** (Unit: 138ms, Commission: 167ms, Payouts: 152ms, Tenant Scoping: 242ms, Workflow: 5.1s, Production Smoke 24 routes: 7.6s)
- `php -l` clean on all project files
- `node --check` clean
- Temp debug files cleaned: `check_structure.php`, `debug_req2.php`, `check_workflow_login.php`, `check_login.php`, `test_workflow_probe.php`

### Key Lessons (carried, continued)
_415. **Transient second-actor interference mimics app bugs** — concurrent DB edits / session races can make probes fail intermittently. Always re-run deterministically before fixing code.
_416. **Probe correctness verified by independent minimal script** — `check_structure.php` (absolute bootstrap, header-size split + BOM strip, `data.token`) proved the token flow works; workflow_probe logic was already correct.
_417. **Full master suite is the final gate** — 6/6 suites passing across unit, integration, workflow, and 24 production smoke routes confirms system health.
---
## Session 165: Feature Flags GUI + Audit-Log Views + IT Support Toolbox (2026-10-03)

### User report
"What did we do so far?" / "do next" � continue building: User mgmt (CRUD/roles/impersonation/audit) already exists; missing pieces were Feature Flags GUI, audit-log views, and a no-coding GUI for the IT fixer.

### Fixes (all probe-verified)
| # | Fix | Files |
|---|-----|-------|
| 1 | **FeatureFlagService + Controller syntax repair** � stray `(End of file�)` trailer lines + `);`?`];` typo on `$flagData` array; bad `use App\Http\Controllers\AdminController` import removed | `Services/FeatureFlagService.php`, `Admin/FeatureFlagController.php` ? |
| 2 | **Strict-mode bool fix** � `Database::insert()` binds PHP `false` as `''''` which MySQL strict rejects for TINYINT `enabled`; service now casts `!empty(...) ? 1 : 0` (+ int cast rollout) | `Services/FeatureFlagService.php` (`setFlag`) ? |
| 3 | **Feature Flags admin GUI** � index (stats/filter/toggle), create, edit (+info panel, delete, code snippet) | `views/admin/feature-flags/{index,create,edit}.php` ? |
| 4 | **Routes + 4 seeded flags** � 9 routes; `wallet_auto_credit` ON/100, `ai_chat_v2` ON/100, `new_checkout_flow` OFF/10%, `maintenance_mode` OFF/100 | `routes/web.php`, `feature_flags` table ? |
| 5 | **Audit-log views (were 404-shell)** � index (filters+pagination), detail (event/actor/changes/related), user_timeline, entity_timeline, stats (7/30/90d) � against real `audit_logs` schema (1466 rows) | `views/admin/audit-log/*.php` (5 files) ? |
| 6 | **IT Support Toolbox** � one GUI for the fixer: DB/tables/users/audit/disk/PHP health, 7 tool cards (flags, audit, activity, users, godmode, cache, tickets), error-log tail; reuses existing tools, no duplication | `Admin/ItSupportController.php`, `views/admin/it-support/toolbox.php`, route `/admin/it-support` ? |

### Verification
- Page probe **7/7 PASS** (feature-flags �3, audit-log, audit stats, activity-log, user timeline � all 200 in full layout) + IT probe **3/3** (toolbox, audit detail #1, flag check API `"enabled":true`)
- `php -l` clean (11 files), `testing/master_test_runner.php` **6/6** ?, temp probe scripts deleted

### Key Lessons (carried, continued)
_418. **PHP `false` ? `''''` binding breaks strict-mode TINYINT inserts** � `Database::insert()` binds bools as empty string; MySQL strict rejects it. Cast bools to `1/0` at the service layer; don''t touch the shared wrapper.
_419. **A lint-clean controller means nothing without its views** � `AuditLogController` existed with 5 actions and zero view files; probe pages by rendered content needle, not just HTTP 200.
_420. **Don''t rebuild the fixer toolbox � aggregate it** � GodMode/cache/tickets/users already existed; the gap was a single landing page with health + links + log tail.

---
## Session 166: DB Monitor + Health Dashboard + Full Sidebar UI Review (2026-10-03)

### User report
"do next all" � build everything remaining: DB monitoring, health dashboard, API docs, full UI review.

### Survey (no duplication)
cron-health, query-analyzer, monitoring, developer, dev-tools, backups, storage, api-docs views+routes ALL already exist. Real gaps: DB monitor, unified health, UI-wide broken-page hunt.

### Fixes (all probe-verified)
| # | Fix | Files |
|---|-----|-------|
| 1 | **Database Monitor (read-only)** � MySQL version/uptime/threads/slow-queries + top-100 tables by size (data/index MB, est rows) + search | `Admin/DatabaseMonitorController.php`, `views/admin/database/index.php`, route `/admin/database` ? |
| 2 | **System Health dashboard** � 9 checks: DB, cron freshness, job queue depth, email/SMS backlog, scheduler, backup age, failures-today, disk, PHP; PASS/WARN/FAIL + deep-links | `Admin/HealthController.php`, `views/admin/health/index.php`, route `/admin/health` ? |
| 3 | **BASE_PATH latent bug** � constant only conditionally defined per-controller; HealthController derived root from `STORAGE_PATH` instead (CronHealth `run()` has same latent issue, untouched) | `Admin/HealthController.php` ? |
| 4 | **hasFlash missing on BaseController** � only `admin/plot-import/import.php` used it (sole caller codebase-wide) ? fatal ? `/admin/plots/import` 500. Added symmetric `hasFlash()` | `BaseController.php` ? |
| 5 | **6 bulk-write-corrupted files repaired** � 4� `(End of file�)` trailer (SystemConfigService, PasswordStrength/RememberMe/TwoFactor services), 2� truncated class brace (Api Employee/LoanController), 1� duplicated `getTenantId()` (RememberMeService) | 6 files ? |
| 6 | **Full sidebar probe 266 URLs: 256 full-OK, 0 FAIL** (was 2�500). 3 shell-flags all false-positive/by-design: `/admin/sites` (status dropdown text), `/admin/careers/manage` (legit empty state), `/admin/ai-calling/health` (JSON endpoint) | probe ? |
| 7 | **Toolbox extended** � System Health, Database, Cron, Backups, API Docs cards (now 12 tools) | `Admin/ItSupportController.php` ? |

### Verification
- New pages **5/5 PASS** (database, database+search, health, it-support, api-docs � all 200 full-layout)
- Full `app/` lint sweep: **3066 files, 0 errors**
- `testing/master_test_runner.php` **6/6** ?, temp scripts deleted, no commit
- Note: project root has ~80 second-actor scratch scripts (`check_*`, `fix_*`, `debug_*`, `add_methods2.php` broken) � untracked clutter, deliberately untouched

### Key Lessons (carried, continued)
_421. **Probe the whole sidebar, not just your pages** � 266-URL sweep found 2�500 + 6 corrupted files no single-page test would catch; flag tiny/stub bodies, then hand-verify each flag.
_422. **Same corruption signature = same bulk-write cause** � `(End of file�)` trailers and truncated braces across unrelated files point to one bad writer; grep the signature repo-wide and fix the family, not the instance.
_423. **BASE_PATH is not global in this codebase** � defined ad-hoc inside 3 controllers; any new controller using it fatals. Derive root from `STORAGE_PATH` or `dirname(__DIR__, 4)`.
_424. **A view calling a nonexistent `$this->helper()` fatals the whole page** � `hasFlash` had zero definition and exactly one caller; grep callers before assuming a helper exists.

---
## Session 167: Plot Development Tracking + Menu Wiring (2026-10-04)

### Trigger
"do next" � autonomous continuation. Found orphan view/controller for plot development tracking: `PlotManagementController::development()` + `views/admin/plots/development.php` existed but had no route and no menu entry; also had UTF-8 BOM artifact (`﻿`).

### Fixes (all probe-verified)
| # | Fix | Files |
|---|-----|-------|
| 1 | **Route added** � `/admin/plots/development` ? `PlotManagementController@development` | `routes/web.php` ? |
| 2 | **BOM removed** � stripped `﻿` artifact from view | `views/admin/plots/development.php` ? |
| 3 | **Menu wired** � inserted "Plot Development" under "Plots Inventory" (parent_id=20) with `fa-hard-hat` icon, `plots.development` permission | `admin_menu_items` table ? |

### Verification
- New page **PASS**: 200, 235K full render, stats cards + project table render
- Full sidebar probe: **267 URLs ? 256 full-OK, 0 FAIL** (3 pre-existing false-positive shell flags unchanged)
- `php -l` clean, `testing/master_test_runner.php` **6/6** ?
- Temp scripts deleted, no commit

### Key Lessons (carried, continued)
_425. **Orphan features = view + controller + no route + no menu** � the "plot development" feature was 90% done but invisible; 3-minute fix (route + menu + BOM) made it discoverable.
_426. **UTF-8 BOM shows as `﻿` in source** � byte-level check (`bin2hex`) catches it; `php -l` passes because PHP treats it as output.
_427. **Menu permission key pattern** � use `section.action` (`plots.development`) so RBAC can gate it later without code changes.

---
## Session 168: QueryAnalyzer Explain Fix + Debug Cleanup + Wrong-Base-Class Family Found (2026-10-06)

### Trigger
"do next" + "apna sara kaam kar lo" � finish own track: remove temp debug lines left from QueryAnalyzer investigation, resolve the still-500 explain endpoint.

### Fixes (all probe-verified)
| # | Fix | Files |
|---|-----|-------|
| 1 | **Explain 500 root-caused: wrong base class** � `use App\Http\Controllers\AdminController;` bound `extends` to the tiny 2.8KB front controller (only `isLoggedIn`), not `Admin\AdminController`; `validateCsrfOrFail()` genuinely missing there. Removed the bad import (same trap as Session 165 FeatureFlag fix) | `Admin/QueryAnalyzerController.php` ? |
| 2 | **Temp-debug reverted** � all `BC:`/`QAC:`/`EXPLAIN` error_logs removed from BaseController + QueryAnalyzerController; Autoloader debug re-commented. Kept the real fixes: TenantAwareTrait, perf-schema columns, requireAdmin+CSRF on explain, CSRF token in test_login | 3 files ? |
| 3 | **Query-analyzer API fully green 7/7** � page + slow/table/index/missing/processes/explain (was 5/7 + explain 500) | probe ? |
| 4 | **4 `?php`-corrupt controllers repaired** (missing `<`, namespace fatal) | News, PropertyImage, Project, PropertyWorkflow controllers ? |
| 5 | **Second-actor churn note** � BaseController carries their RBACManager unify (hasRole/isAdmin); kept, untouched. Root scratch-file clutter untouched | � |

### Found, NOT fixed (needs per-role probes before flip)
- **10 more controllers with the same wrong import** (`use App\Http\Controllers\AdminController;` inside `Admin/`): AgentAgreement, AgentCommission, CampaignTemplate, CronHealth, DashboardWidget, InvestmentAnalytics, ListingSettings, SystemConfig, WorkspaceHub, Withdrawal. Flipping base class upgrades requireAdmin to strict RBAC � each needs manager/employee-role probe first.

### Verification
- Query-analyzer probe **7/7 PASS** (explain 200 with real EXPLAIN rows)
- `testing/master_test_runner.php` **6/6** ?, `php -l` clean, temp + stray log files deleted, no commit (churn active: 359 modified + 173 untracked)

### Key Lessons (carried, continued)
_428. **A `use` line can silently rebind your parent class** � inside `Admin/`, `use App\Http\Controllers\AdminController;` hijacks `extends AdminController` to the tiny front controller. Sibling-namespace resolution (no import) is the correct pattern here; grep the import repo-wide when one instance bites.
_429. **Temp-debug in shared constructors is log-spam for every request** � BaseController debug lines fired on ALL pages; revert the same session, verify with `git diff` that only intended lines remain.
_430. **Uncaught-exception message beats log-chasing** � temporary in-method try/catch echo named the 500 instantly after log files proved unreadable/missing.

---
## Session 169: Wrong-Base-Class Family Flip (10 controllers) + DashboardWidget Rewrite + web.php BOM (2026-10-06)

### Trigger
"do next all fix" + second tab still churning � fix the 10-controller wrong-import family found in Session 168, probe-verified, zero contact with second-actor lines.

### Fixes (all probe-verified)
| # | Fix | Files |
|---|-----|-------|
| 1 | **9� bad-import removed** (one line each, EOL-preserving script, `php -l` clean) � AgentAgreement, AgentCommission, CampaignTemplate, CronHealth, InvestmentAnalytics, ListingSettings, SystemConfig, WorkspaceHub, Withdrawal now extend the real `Admin\AdminController` | 9 controllers ? |
| 2 | **DashboardWidgetController full rewrite** � was triple-broken: wrong import + Eloquent model (no illuminate in vendor) + `response()->json()` (no such helper) + view called nonexistent `renderWidget()` with JS var inside PHP (fatal). Rewrote 4 actions to `$this->db` + `jsonResponse`, added `getLayout($id)`, remapped GET route, fixed view to client-side `widgetTemplates` map | controller + `dashboard/customize.php` + route ? |
| 3 | **`user_dashboard_layouts` table created** (idempotent; table never existed) | DB ? |
| 4 | **HY093 on save fixed** � `Database::update` builds named SET placeholders; WHERE must be named too (`id = :wid`) | same controller ? |
| 5 | **`routes/web.php` BOM stripped** � 3-byte BOM was prefixing EVERY response incl. JSON (`json_decode` returned null codebase-wide for strict parsers); `<?php` now at byte 0 | `routes/web.php` ? |

### Verification
- Targeted **10/10 PASS** as admin (was 9/10 + 500), incl. previously-dead `/admin/dashboard/customize`
- Widget full cycle **7/7** (page?save?listed?get-one?widgets?delete?404-gone, test row cleaned)
- Staff-role probes (telecaller + employee): only 200s + dashboard-302s (correct RBAC gating), **0�500**; redirect targets verified ? `/admin/dashboard`, not login
- Full sidebar re-probe: **258 OK, 0 real FAIL** (2 flags = known by-design small pages)
- `testing/master_test_runner.php` **6/6** ?, temp files deleted, no commit (churn ongoing)

### Key Lessons (carried, continued)
_431. **A view can 500 with fully-rendered content** � `$this->renderWidget("<js-var>")` fatals at the END of the view; buffered content flushes WITH the 500 status. File-logging catch (not echo) inside the action names it instantly.
_432. **BOM in an always-included file poisons every response** � `routes/web.php` BOM prefixed all JSON; symptom is `json_decode ? null` with HTTP 200. Check first-3-bytes of bootstrap/router/routes files when JSON parses fail globally.
_433. **Named-vs-positional placeholder mixing (HY093)** � this `Database::update` builds named SET params; pass WHERE params named too.
_434. **302-to-dashboard vs 302-to-login distinguishes gate from auth-fail** � staff 302s to dashboard = correct RBAC; to login = broken session. Always assert the target.

---
## Session 170: Sessions-165/166 Re-verification Sweep (No Regressions) + Full Lint Re-sweep (2026-10-06)

### Trigger
"do next all" � autonomous continuation during active churn (357 files). Re-verified churn-sensitive areas without touching second-actor code.

### Findings
- Session 166 pages: **database, database+search, health, api-docs � all 200** with needles ?
- Session 165 pages: **feature-flags �3, audit-log �5 (incl. real-entity timeline + detail #1), it-support � all 200** ?
- Full `app/` lint re-sweep: **3066 files, 0 errors** (matches Session 166 baseline exactly) ?
- No app code changes needed. No regressions from churn in these areas.

### Verification
- Re-verify probe **13/13 PASS**; temp scripts in temp-dir only, no commit

### Key Lessons (carried, continued)
_430. **Re-probe churn-adjacent pages on every autonomous round** � 13 cheap HTTP checks catch silent regressions while second-actor bulk-edits are in flight; probe-only, never touch their files.
