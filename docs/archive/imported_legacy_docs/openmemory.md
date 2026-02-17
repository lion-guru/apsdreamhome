
## Overview

APS Dream Home is a large PHP/MySQL codebase that combines:
- Public real-estate website pages (legacy PHP pages at repo root)
- Admin ERP panel (`admin/`) with role-based dashboards and many operational modules
- A modern MVC-style pipeline (`public/index.php` + `app/`)
- An MLM/associate system (network tree, commissions, payouts)
- Multiple header/footer/template implementations (static + unified + DB-driven “dynamic templates”)

## Architecture / Entry Points

### Public site (legacy pages)
- `index.php` (homepage)
- `properties.php`, `projects.php`, `property-details.php`, `contact.php`, etc.
- `login.php`, `register.php`, `logout.php`

### Modern MVC pipeline
- Root `.htaccess` and `public/.htaccess` rewrite non-file requests to `public/index.php`
- `public/index.php` bootstraps `app/core/App.php`
- `app/core/App.php` loads route files in `routes/` (modern/web/api)

### Admin panel
- `admin/index.php` (admin login)
- `admin/process_login.php` -> `admin/admin_login_handler.php`
- Admin requests are routed by `admin/.htaccess` to `admin/index.php` when not direct file

## Routing Systems (Multiple)

### 1) Apache rewrite
- Root `.htaccess`: routes most requests to `public/index.php`
- `public/.htaccess`: routes most requests to `public/index.php`

### 2) MVC router (App pipeline)
- Core: `app/core/App.php`
- Router implementation lives under `app/core/Routing/` and related core
- Route registrations are in `routes/modern.php`, `routes/web.php`, `routes/api.php`

### 3) Dispatcher + static Route definitions (MLM focused)
- `includes/dispatcher.php`
- `app/core/routes.php` contains `Route::get(...)` style definitions for MLM/admin-MLM endpoints

## Templates / Layout Systems

### Public site headers/footers
- Legacy static header: `header.php`
- Unified templates: `includes/unified_header.php`, `includes/unified_footer.php`

### DB-driven Dynamic Template System
- Integration helper: `includes/dynamic_templates.php`
- OO templates: `templates/dynamic_header.php`, `templates/dynamic_footer.php`
- Include templates used by many pages: `includes/templates/dynamic_header.php`, `includes/templates/dynamic_footer.php`
- Admin UI: `admin/dynamic_content_manager.php`
- Storage tables: `dynamic_headers`, `dynamic_footers`, `site_settings`, `site_content` (and related)

### Admin panel wrappers
- Classic: `admin/header.php` (contains header + sidebar markup)
- Updated wrapper stack:
  - `admin/updated-admin-wrapper.php`
  - `admin/updated-admin-header.php`
  - `admin/updated-admin-sidebar.php`
  - `admin/updated-admin-footer.php`

## Dashboard & Role Map

### Primary dashboard entry
- `dashboard.php` redirects users to correct dashboard based on session + role.

### Admin/official roles (admin panel)
- Login sets `$_SESSION['admin_logged_in']` + `$_SESSION['admin_role']`
- After login: redirect to `admin/<role>_dashboard.php` (examples: `superadmin_dashboard.php`, `finance_dashboard.php`, `employee_dashboard.php`, `agent_dashboard.php`, etc.)

### User / customer / associate dashboards (public side)
- `user_dashboard.php` uses role-based menu config: `includes/config/menu_config.php`
- `mlm_dashboard.php` for MLM summary
- `associate_dashboard.php` for enterprise associate dashboard
- `agent_dashboard.php` (agent session based)
- `employee_dashboard.php` (employee session based)

## Key Folders

- `admin/`: Admin ERP panel (many modules + role dashboards)
- `app/`: MVC-like code (controllers/models/services/views)
- `routes/`: MVC route registrations
- `includes/`: shared utilities, security, DB, template helpers, managers
- `public/`: MVC web root
- `assets/`: legacy static assets
- `api/`: legacy API endpoints
- `database/`: schema/migration/tools
- `docs/`: documentation
- `backup/`, `archive/`, `*_archive/`: snapshots/legacy copies

## Key Docs (authoritative references)

- `PROJECT_MAPPING.md` - complete page-by-page mapping
- `SYSTEM_FLOW.md` - flow diagrams and routing details
- `ROLE_WISE_SITEMAP.md` - role-wise dashboards, menus, connected pages, session keys
- `ADMIN_FUNCTIONALITY_MAPPING.md` - admin modules, risks, security notes
- `DYNAMIC_TEMPLATE_SYSTEM.md` - dynamic header/footer system
- `DEEP_PROJECT_ANALYSIS.md` - comprehensive architecture analysis
- `FUTURE_PLAN.md` - development roadmap, priorities, what's next

## User Defined Namespaces
- 
