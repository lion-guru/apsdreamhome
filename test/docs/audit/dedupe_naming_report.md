# APS Dream Home — Dedupe & Naming Audit (Git-based)

## Scope
- Audited Git history and working tree for:
  - Duplicate routes and conflicting controllers
  - Header/Footer variants and layout stacking
  - Asset path inconsistencies (`public/public` issues)
  - File naming and module organization

## High-Impact Findings
- Routes
  - Duplicate “careers” index routes observed historically:
    - HumanResources\CareerController@index
    - Career\CareerController@index
  - Marketing dashboard endpoints coexisted:
    - `/marketing/dashboard` (page)
    - `/api/marketing/dashboard` (API)
  - Current head at [routes/web.php](../../routes/web.php) keeps a single careers index at top-level HR module and separates API routes clearly.

- Header/Footer & Layouts
  - Variants present:
    - [layouts/header.php](../../app/views/layouts/header.php)
    - [layouts/header_new.php](../../app/views/layouts/header_new.php)
    - [layouts/footer.php](../../app/views/layouts/footer.php)
    - [layouts/footer_new.php](../../app/views/layouts/footer_new.php)
  - Issue: `header_new.php`/`footer_new.php` previously included full HTML skeleton leading to nested layouts. Fixed by converting them to clean fragments and relying on [layouts/base.php](../../app/views/layouts/base.php) for skeleton.

- Asset Paths
  - Many references included “`BASE_URL/public/assets/...`” causing “`/public/public/...`” at runtime.
  - Normalized home page assets to `BASE_URL . '/assets/...` in [pages/index.php](../../app/views/pages/index.php).
  - Remaining occurrences exist in:
    - [layouts/header.php](../../app/views/layouts/header.php#L14-L24)
    - legacy dashboards and microsite partials

## Git History Highlights
- 9b1956ab “[Frontend Cleanup] Complete frontend structure optimization”
  - Cleaned HomeController + removed duplicate admin routes
- b9f7efbe “[Auto-Fix] Max Level Deep Scan Complete - Critical Issues Resolved”
  - Fixed 3 route duplications
- 3823ae60 “[Auto-Fix] Critical Controller Fixes: routes and removed broken duplicates”
  - Route cleanups

## Canonical Naming Proposal
- Modules use PSR-4 namespaces under `App\Http\Controllers\{Module}`
- Prefer singular module folders and consistent route prefixes:
  - HR/Careers: `/careers/*` → `HumanResources\CareerController`
  - Marketing UI: `/marketing/*` → page actions
  - Marketing API: `/api/marketing/*` → JSON endpoints
- Views:
  - Public: `app/views/pages/...`
  - Admin: `app/views/admin/...`
  - Layouts: `app/views/layouts/...` (single base, fragment headers/footers)

## Immediate Remediation (Applied)
- Home page assets normalized and premium header/footer preserved:
  - [pages/index.php](../../app/views/pages/index.php)
  - [layouts/header_new.php](../../app/views/layouts/header_new.php)
  - [layouts/footer_new.php](../../app/views/layouts/footer_new.php)
- HomeController renders without double layout:
  - [HomeController.php](../../app/Http/Controllers/HomeController.php#L9-L24)

## Remaining Cleanups (Safe Batch)
- Replace remaining `BASE_URL . 'public/assets'` with `BASE_URL . '/assets'` in:
  - [layouts/header.php](../../app/views/layouts/header.php#L14-L24)
  - `app/views/customers/dashboard_modern_backup.php`
  - `app/views/projects/microsite/partials/hero.php`
  - `app/views/team/dashboard.php`

## Verification
- Use `php verify-progress.php` for critical checks.
- Manual open: http://localhost/apsdreamhome/public for homepage visual.

## Final Notes
- Keep one careers index route under HumanResources module.
- Keep `/api/*` and page routes strictly separated.

