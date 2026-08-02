# APS Dream Home — Deep Audit Fix Plan (feed this to OpenCode)

## Context for the agent
This is a PHP MVC (custom framework, not Laravel) Real Estate + MLM
platform called APS Dream Home, running on XAMPP locally, deployed via
Docker/Railway. Stack: PHP 8, MySQL, TailwindCSS, Chart.js, vanilla JS,
Flutter mobile app, Node.js WhatsApp microservice. Target users: real
estate buyers/agents/associates in Gorakhpur/UP region.

Work through the tasks below IN ORDER (priority-sorted). For each task:
1. Investigate the actual code before changing anything.
2. Make the smallest safe change that fixes the issue.
3. Run relevant tests (`phpunit`, `composer audit`, `npm audit`) after
   each change.
4. Commit each fix as a SEPARATE git commit with a clear message.
5. Do NOT refactor unrelated code while fixing one issue.
6. If a fix is risky (e.g. touches associates table / commission
   engines), STOP and summarize the plan before executing — ask for
   confirmation.

---

## PRIORITY 1 — Security

1. **Upgrade guzzlehttp/psr7** — 4 medium CVEs (host confusion, CRLF
   injection). Run `composer require guzzlehttp/psr7:^2.12.3`, then
   remove `"guzzlehttp/psr7"` from the `audit.ignore` list in
   composer.json (it is currently suppressing the warning instead of
   fixing it). Re-run `composer audit` to confirm zero findings.
   Test WhatsApp/HTTP integrations after upgrade since psr7 underlies
   Guzzle HTTP calls.

2. **Verify no secrets are tracked in git.** Confirmed clean during
   audit, but re-check before every deploy:
   `git ls-files | grep -iE "\.env$|firebase-service-account|vapid_private|mcp_servers.json"`
   should return nothing. If anything shows up, remove from git
   history with git-filter-repo (already installed, see `.git/filter-repo`).

3. **Rotate any credentials that were ever committed historically**
   (check `git log --all --full-history -- "*.env" "config/firebase-service-account.json"`).
   Even if not tracked now, if they were committed and later removed,
   treat those credentials as compromised and rotate them.

---

## PRIORITY 2 — Broken / silently-failing code paths

4. **Fix LocalizationService misuse in BaseController.php (~line 116-126).**
   Currently calls `LocalizationService::getInstance()` without ever
   calling `initialize()` first, so it throws + is caught + logged on
   EVERY request (log spam, dead feature). Either:
   (a) properly initialize it with real Database + Logger deps, or
   (b) if localization isn't actually used yet, remove the dead call
   entirely and delete/park LocalizationService until it's needed.
   Decide based on whether `lang/hi.php` / `lang/en.php` are actually
   wired into any view currently.

5. **Fix deprecated dynamic properties** (PHP 8.2+ warnings, spamming
   php_error.log):
   - `app/Services/LiveChatService.php` line 14 — declare `private $pdo;`
     (with correct type) as a class property.
   - `app/Services/AdminMenuService.php` line 50 — declare `private $db;`
     as a class property.
   Search the codebase for the same pattern elsewhere:
   `grep -rn "Creation of dynamic property" logs/php_error.log`
   and fix every occurrence, not just these two.

6. **Fix broken image references causing 404s:**
   - `/calc` (EMI Calculator): missing `breadcromb.jpg`
   - `/properties/submit`: missing `submit-property-banner.*`
   - `/news`: missing `assets/images/news/news-1.jpg` (and others)
   Either restore the missing assets or update the view/blade paths
   to point at assets that exist. Broken images on a real estate site
   directly hurt buyer trust — treat as high priority even though
   technically "just" a warning.

7. **Fix missing DOCTYPE/viewport/title on 15 pages** (from
   audit_results/audit_report.txt): `/colonies`, `/senior-developer`,
   `/employee/login`, `admin/network/tree`, `admin/network/genealogy`,
   `admin/invoices`, `admin/locations/states`,
   `admin/locations/districts`, `admin/locations/colonies`,
   `admin/resell-properties`, `admin/ai`. These are being rendered as
   raw partials without the layout wrapper. Wrap each in the standard
   `layouts/` template used elsewhere in `app/views/admin/`.

8. **Fix WebSocket connection failure on `admin/customers`**
   (`ws://localhost/ws/dashboard` fails). Check `websocket_server.php`
   / `websocket_broadcast_server.php` are actually running as a
   background service in production, and that the client connects to
   `wss://` (secure) in production, not hardcoded `ws://localhost`.
   Add a graceful fallback (polling) if the socket can't connect.

---

## PRIORITY 3 — Architecture / technical debt (read before touching)

9. **Audit and consolidate duplicate-purpose services** in
   `app/Services/`. Do NOT delete anything yet — first produce a
   report mapping which of these are actually called (grep for class
   usage across app/Http/Controllers and routes/):
   - Commission: CommissionService, CommissionAgreementService,
     CommissionPlanService, CommissionSimulator, HybridCommissionEngine
   - Payout: PayoutService, PayoutBatchService, AutoPayoutService
   - Salary/Payroll: SalaryService, SalaryCalculationService,
     PayrollService, PayrollBatchService
   - Audit: AuditLogService, AuditService, AuditTrailService
   - Near-duplicate names: ApiDocService.php vs
     APIDocumentationService.php; ConfigManager.php vs
     ConfigurationManager.php
   After the usage report, propose (don't auto-execute) a
   consolidation plan — merge unused ones into the active one, move
   dead ones to `_archive/dead_services/`.

10. **Investigate `associates` table churn.** Migrations folder shows
    repeated create/drop/consolidate scripts for this table
    (`create_associates_table.php`, `drop_associates_table.php`,
    `drop_associates_table_with_constraints.php`,
    `drop_conflicting_associate_tables.php`,
    `consolidate_database_tables.php`, `analyze_table_conflicts.php`).
    Run `analyze_table_conflicts.php` fresh against the current DB,
    report what it finds, and confirm the CURRENT live schema matches
    what the models/services expect (`Associate` model, `AssociateService`
    if present). Do not run any drop script without a fresh backup.

11. **Renumber/organize migrations** going forward. Adopt a strict
    naming convention (`YYYYMMDDHHMMSS_description.sql`) for all NEW
    migrations from now on so this doesn't happen again. Don't rename
    existing ones (breaks history) — just document the convention in
    `database/migrations/README.md` (create if missing) and enforce
    it going forward.

12. **Evaluate scope of blockchain/, metaverse/, iot/, edge/ view
    folders** in `app/views/`. Confirm with routes/web.php whether
    these are actually linked/reachable from the live site. If they
    are dead/experimental and not part of the product roadmap, move
    them to `_archive/` to reduce surface area and confusion for
    future contributors (including future AI agents working on this
    codebase).

13. **Consolidate the .env sprawl.** Currently there are 7 env-related
    files: `.env`, `.env.example`, `.env.local`, `.env.production`,
    `.env.production.example`, `.env.railway`, `.env.testing`. Confirm
    which are actually loaded where (local XAMPP vs Railway vs test
    runner) and document it in README. Remove any that are stale/unused.

14. **Raise test coverage on money-handling services first.**
    Currently only ~9 unit test files exist
    (tests/Unit/Services/CRMServiceTest.php, MoneyWorkflowServiceTest.php,
    PricingServiceTest.php, SalaryAndRoyaltyTest.php, TrackServicesTest.php,
    plus 4 more) against 200+ service files. Prioritize adding tests
    for: CommissionService (whichever one wins the consolidation in
    #9), PayoutService, WalletService, EMIAutomationService — these
    move real money and have the highest blast radius if broken.

---

## PRIORITY 4 — UI/UX (product-facing, for Gorakhpur/UP real estate buyers)

15. **Add context-aware login redirects.** Pages like `/mlm-dashboard`,
    `/user/dashboard`, `/user/properties`, `/user/inquiries`,
    `/user/profile` currently just show a bare generic login form when
    unauthenticated. Add a message like "Login to view your dashboard"
    / property name if they were trying to view a specific property,
    so the login page has context instead of feeling like a dead end.
    This especially matters for MLM associates arriving from shared
    referral links.

16. **Make WhatsApp the primary CTA on property/plot pages,** not just
    an inquiry form. This audience (tier-2/3 city, Hindi-speaking)
    converts far better via WhatsApp/call than web forms. The
    WhatsApp service already exists (`whatsapp-service/`) — surface a
    prominent floating/sticky WhatsApp button on every property, plot,
    and colony page, pre-filled with the property name/ID in the
    message.

17. **Default to Hindi for buyer-facing (non-admin) pages.**
    `lang/hi.php` and `lang/en.php` both exist — confirm the buyer
    site defaults to Hindi with an easy toggle, since admin/employee
    panels can stay English. Check `LocalizationService`
    (see task #4) is actually the thing meant to drive this, or if a
    simpler mechanism already handles it elsewhere.

18. **Build an associate-facing network/genealogy view.** Currently
    `admin/network/tree` and `admin/network/genealogy` appear to be
    admin-only (per audit_report.txt). MLM platforms retain associates
    far better when each associate can see their OWN downline tree,
    rank progress, and pending commissions in a simple visual way.
    Check `app/views/mlm/` and `app/Services/MLM/` for what exists
    and whether a read-only, scoped-to-self version can be exposed to
    associates.

19. **Fix all broken/missing images site-wide**, not just the 3 found
    in the last audit — re-run a fresh crawl (whatever produced
    `audit_results/audit_report.txt` originally, likely a Playwright
    script under `testing/` or `_archive/testing_screenshots`) and
    fix everything it flags. Broken images are a bigger trust problem
    for real estate than almost any other UI issue.

---

## Deliverable expected back from the agent
For EACH numbered task above: a short summary of what was found, what
was changed (with file paths), and what was tested. Group commits
per-task. Flag anything in Priority 3 that needs my (Abhaay's)
explicit sign-off before executing (especially #9 and #10 — service
consolidation and associates table changes can break production MLM
commission calculations if done wrong).
