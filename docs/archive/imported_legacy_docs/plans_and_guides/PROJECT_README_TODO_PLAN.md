# APS Dream Home – Master Readme & TODO Planning Guide

## Project Identity
- **Name**: APS Dream Home – Real Estate Platform
- **Stack**: PHP (>=7.4) with custom MVC-lite routing, MySQL, Bootstrap-based frontend, tooling via Composer/NPM
- **Entry Points**:
  - Web root served from project root (`index.php`, `router.php`)
  - Admin tooling and diagnostics spread across dedicated PHP scripts under project root and `admin/`

## Directory Atlas (A–Z Overview)
- **`admin/`** – Legacy-style admin panel pages, diagnostics, and management tools. Contains >500 scripts for dashboards, maintenance, and data utilities. Many routes map directly via `router.php`.
- **`api/`** – REST-like endpoints returning JSON; aligned with routes `api/*` and tested via `test_mobile_api.php` etc.
- **`app/`** – PSR-4 autoloaded namespace `App\`. Holds controllers, models, services, helpers, and view layouts. Ensure any new classes follow directory conventions for Composer autoloading.
- **`assets/`** – Static frontend resources (CSS, JS, images, fonts). Build tools pull from here for bundling and optimization.
- **`backups/`** – Massive archival set of historical scripts/backups. Treat as read-only; keep out of runtime routing.
- **`database/`, `DATABASE FILE/`** – SQL dumps, migration helpers, and schema reorganizations. Use for bootstrapping or reference when extending schema.
- **`docs/`, `07_documentation/` and numerous `*_GUIDE.md` files** – Exhaustive documentation produced over project lifecycle. Refer for historical decisions, but this master guide consolidates day-to-day essentials.
- **`includes/`** – Core configuration (`config.php`), DB connection logic, and template helpers. Mandatory for runtime bootstrap.
- **`node_modules/`, `package.json`, `vite.config.js`** – Frontend tooling. Run `npm install` to manage front-end build pipeline.
- **`PHPMailer/`, `PHPMailer.php`** – Embedded email library; consider replacing with Composer-managed dependency.
- **`tests/`** – PHPUnit structure (Factories, Database seeders, Helpers). Requires MySQL test DB and bootstrap adjustments for full execution.
- **`vendor/`** – Composer managed dependencies.
- **Root PHP scripts (`*.php`)** – Individual landing pages, diagnostics, and alternate templates. Many are subject to auto-routing fallback.

## Application Flow & Routing
- **Bootstrap**: `router.php` initializes sessions, loads config, defines `$routes`, and invokes file-based or controller-based handling.
- **Routing Modes**:
  - **Explicit map**: `$routes` associates clean URLs (`/properties`) to PHP scripts (`properties.php`).
  - **Pattern routes**: Regex patterns for dynamic paths (e.g., `property/([0-9]+)` -> `property_details.php?id=$1`).
  - **Auto-discovery**: Fallback that attempts to locate PHP files by slug in project root or common subdirectories.
  - **MVC controller handling**: For select `admin/*` routes calling `handleMVCRequest()` to instantiate `App\Controllers\...` classes (ensure controller methods exist).
- **Front Controllers**: `index.php` delegates directly into `router.php`. `.htaccess` simplifies request rewriting to maintain clean URLs.

## Key Subsystems
- **Authentication**: Traditional PHP session-based login via `login.php`, `auth/` directory handlers, and admin scripts. No uniform middleware; security hardening required.
- **Property & Project Management**: `properties.php`, `projects.php`, related handlers, and admin dashboards manage CRUD operations (procedural style).
- **Form Handling**: `contact_handler.php`, `property_inquiry_handler.php`, `job_application_handler.php` process submissions, send emails (via PHPMailer), and integrate with database.
- **Analytics & Reporting**: Multiple dashboards (`analytics.php`, `system_monitor.php`, etc.) provide monitoring and data export utilities.
- **Utilities & Diagnostics**: Numerous scripts (`system_health_check.php`, `database_scan.php`, `comprehensive_test.php`) for maintenance and auditing.

## Database & Data Assets
- SQL definitions: `setup_*`, `create_*`, `*_schedule.sql` provide schema blueprints.
- Seeders: `seed_sample_data.php`, `setup_demo_data.php`, Test seeders under `tests/Database/Seeders` for automated population.
- Verification scripts: `check_*` PHP files validate schema integrity and table counts.

## Tooling & Deployment
- **Composer**: Dependencies defined in `composer.json`; run `composer install` / `composer dump-autoload`. Resolve PSR-4 warnings by relocating view-defined classes into `app/`.
- **PHPUnit**: Configured via `phpunit.xml`. Ensure `tests/bootstrap.php` successfully loads `Tests\TestCase` and test DB before running `vendor/bin/phpunit`.
- **CLI/Batch Scripts**: `.bat` and `.sh` files for deployment, database resets, and environment setup (`deploy_windows.bat`, `setup_database.bat`).
- **Monitoring**: Scripts like `system_monitor.php`, `app_performance_monitor.php` facilitate runtime checks.

## Current Documentation Landscape
- `README.md` – Marketing-style overview and broad roadmap.
- `TODO.md` – Aggressive roadmap including security, performance, testing, frontend upgrades.
- Specialized docs (e.g., `DEEP_SCAN_REPORT.md`, `ALL_ISSUES_FIXED.md`) – Provide historical resolutions and audits. Use this master guide for quick orientation and link to deeper docs when needed.

## Consolidated TODO & Planning Checklist
### Immediate (Bootstrapping)
- **[Config & Environment]** Finalize `.env` with DB credentials, mail settings, base URL. Confirm `includes/config.php` uses environment values.
- **[Database]** Import base schema from `database/` SQL files; run seeders for sample content.
- **[Routing Audit]** Validate each route in `$routes`; ensure target files exist or migrate logic into controllers.
- **[Autoload Fix]** Move class definitions out of `app/views/` into PSR-4-compliant directories to eliminate Composer warnings.

### Short Term (Stabilization)
- **[Security Hardening]** Implement CSRF tokens, input sanitization, security headers, and rate limiting for sensitive endpoints.
- **[Testing Pipeline]** Repair PHPUnit bootstrap, provision test DB, and expand coverage for critical flows (auth, property CRUD, forms).
- **[CI/CD]** Configure GitHub Actions or equivalent for PHP linting, static analysis (PHPStan), and test automation.

### Medium Term (Feature Alignment)
- **[User Management]** Establish RBAC, 2FA, and audit trail as outlined in `TODO.md`.
- **[Search Enhancements]** Upgrade property/project filters, support comparisons, integrate external map services.
- **[Frontend Modernization]** Leverage Vite + modern framework (Vue/React) with Tailwind or refined Bootstrap.

### Long Term (Strategic Enhancements)
- **[Real-time Features]** Implement WebSocket-based notifications, live chat, and presence tracking.
- **[Analytics]** Build dashboards for behavior tracking, custom reporting, and scheduled exports.
- **[Automation]** Introduce job queues, event dispatching, and scripted maintenance tasks.

## Linking & Extension Guidance
- **New Pages**: Place under project root or structured subdirectories; update `$routes` in `router.php` or create controller methods under `app/controllers/`.
- **New Controllers**: Create classes under `app/controllers/` with namespace `App\Controllers`. Update Composer autoload (`composer dump-autoload`) after adding files.
- **Shared Logic**: Centralize reusable code in `app/Services/`, `app/Helpers/`, or `includes/functions.php` (prefer OO approach moving forward).
- **Templates**: Use existing hybrid template system (`includes/hybrid_template_system.php`) to maintain consistent layout.
- **API**: Follow `api/` patterns; ensure authentication and rate limiting (future task) before exposing new endpoints.

## Verification & Maintenance Loop
- Run `comprehensive_system_test.php` and `system_health_check.php` after significant changes.
- Track progress in this master guide alongside `PROJECT_TODO_PLAN.md`/`TODO.md` to avoid duplication.
- Keep documentation synchronized: update this file when new directories/features are added or deprecated.

## Quick Start Commands
```bash
# Backend prerequisites
composer install

# Frontend assets
npm install
npm run build # or npm run dev

# Testing
vendor/bin/phpunit
```

## Status Tracking
| Area | Current Status | Owner/Notes |
|------|----------------|-------------|
| Routing | Partially verified | Audit `router.php` mappings vs physical scripts |
| Security | Needs hardening | Implement CSRF, headers, rate limits |
| Testing | Failing bootstrap | Fix autoload, setup test DB |
| Frontend | Legacy Bootstrap | Plan Vite + modern component library |
| Documentation | Extensive but fragmented | Use this file as living index |

---
**Maintain this master readme as the single source of truth for orientation, routing references, and prioritized tasks.** Update sections as progress is made or new modules enter the system.
