# APS Dream Home - Role-wise Sitemap (Pages + Connections)

This file maps **who uses which pages** and how navigation connects (dashboard -> menu -> subpages).

## 1) Global Entry Points

### Public site
- **Home**: `index.php`
- **Main navigation (legacy)**: `header.php`
- **Login / Register**: `login.php`, `register.php`
- **Primary dashboard router after login**: `dashboard.php`

### Modern MVC pipeline (front controller)
- **Front controller**: `public/index.php`
- **Route registration**: `routes/modern.php`, `routes/web.php`, `routes/api.php`
- **App controllers (MLM focused)**: `app/controllers/*`

### Admin panel
- **Admin login**: `admin/index.php`
- **Login processing**: `admin/process_login.php` -> `admin/admin_login_handler.php`
- **Admin dashboards**: `admin/*_dashboard.php`

## 2) Public Visitor (No login)

### Menu (from `header.php`)
- **Home**: `index.php`
- **Project dropdown**:
  - Gorakhpur:
    - `gorakhpur-suryoday-colony.php`
    - `gorakhpur-raghunath-nagri.php`
  - Lucknow:
    - `lucknow-ram-nagri.php`
    - `lucknow-project.php`
  - Kusinagar:
    - `budhacity.php`
- **Gallary**: `gallary.php`
- **Legal**: `legal.php`
- **Career**: `career.php`
- **About**: `about.php`
- **Bank**: `bank.php`
- **Resell**:
  - View: `property.php`
  - Add: `submitproperty.php`
- **Login**: `login.php`

## 3) Public Logged-in Users (site root)

> Note: there are multiple session styles in the codebase (`user_id`, `uid`, `uemail`, `user_logged_in`, etc.). Some dashboards depend on one style; this is an important refactor target.

### Dashboard router
- `dashboard.php` decides routing based on session:
  - `$_SESSION['admin_logged_in']` -> `admin/<role>_dashboard.php`
  - `$_SESSION['user_logged_in']` -> associate check -> `mlm_dashboard.php` else `index.php`
  - `$_SESSION['user_id']` -> redirects to `BASE_URL . 'dashboard/'` (intended MVC path)

### My Account menu (from `header.php`)
- **Profile**: `profile.php`
- **Your Property**: `feature.php`
- **Logout**: `logout.php`

## 4) Role: User / Customer (frontend dashboards)

### User dashboard
- **Dashboard page**: `user_dashboard.php`
- **Menu config source**: `includes/config/menu_config.php`
  - Dashboard: `/user_dashboard.php`
  - My Properties: `/property-listings.php`
  - Profile: `/profile.php`
  - Logout: `/logout.php`

### Customer dashboards
- **Admin-side customer dashboard (admin role)**: `admin/customer_dashboard.php` (uses `customer_logged_in` session)
- **Public-side customer pages in MVC pages folder**: `app/pages/customer_public_dashboard.php` (exists as a page template)

## 5) Role: Associate / MLM

### Simple MLM dashboard (public)
- `mlm_dashboard.php`
  - Shows referral code, direct referrals, commissions etc.

### Enterprise associate dashboard (public)
- `associate_dashboard.php`
  - Uses MLM tables: `associates`, `commission_transactions`, `team_hierarchy`, bookings/EMI-related tables
  - Typical sub-sections (in code): profile details, team/downline, commission/business, customers + EMI tracking

### Admin-side associate portal
- `admin/associate_portal.php`
- Additional admin MLM suite:
  - `admin/mlm_dashboard.php`
  - `admin/mlm_commissions.php`
  - `admin/mlm_payouts.php`
  - `admin/mlm_reports.php`

## 6) Role: Agent

### Public agent dashboard
- `agent_dashboard.php` (session: `agent_id`)

### Admin role agent dashboard
- `admin/agent_dashboard.php` (session: `admin_logged_in` + `admin_role == 'agent'`)

## 7) Role: Employee

### Public employee dashboard
- `employee_dashboard.php` (session: `uid` + `utype == 'employee'`)

### Admin role official employee
- `admin/employee_dashboard.php` (admin role: `official_employee`)
  - Quick links:
    - `my_tasks.php`
    - `tickets.php`
    - `documents_dashboard.php`
    - `attendance_dashboard.php`

## 8) Admin Panel Navigation (menus + dashboards)

There are **two main sidebar/menu implementations** in admin:

### A) Classic admin sidebar embedded in `admin/header.php`

This is the broadest menu map currently visible in code:
- **Dashboard**: `dashboard.php`
- **All Users**:
  - `adminlist.php`
  - `userlist.php`
  - `useragent.php`
  - `userbuilder.php`
- **State & City**:
  - `stateadd.php`
  - `cityadd.php`
- **Property**:
  - `propertyadd.php`
  - `propertyview.php`
  - `resellplot.php`
  - `viewresellplot.php`
- **Contact, Feedback**:
  - `contactview.php`
  - `feedbackview.php`
- **About Page**:
  - `aboutadd.php`
  - `aboutview.php`
- **Gallery**:
  - `addimage.php`
  - `gallaryview.php`
- **Site Management**:
  - `site_master.php`
  - `gata_master.php`
  - `plot_master.php`
  - `update_site.php`
  - `update_gata.php`
  - `update_plot.php`
- **Kissan Management**:
  - `kissan_master.php`
  - `view_kisaan.php`
- **Project Management**:
  - `projects.php`
  - `property_inventory.php`
  - `booking.php`
  - `customer_management.php`
  - `ledger.php`
  - `reminders.php`
- **Account**:
  - `financial_module.php`
  - `transactions.php`
  - `add_transaction.php`
  - `add_expenses.php`
  - `add_income.php`
  - `ledger.php`
- **CRM**:
  - `leads.php`
  - `opportunities.php`
- **Job Applicant**:
  - `admin_view_applicants.php`
  - `admin_add_job.php`
- **Associate (legacy spelling in menu)**:
  - `assosiate_managment.php`
  - `transactions.php`

### B) Updated wrapper sidebar: `admin/updated-admin-sidebar.php`

A smaller curated menu:
- Dashboard: `dashboard.php`
- Users: `adminlist.php`, `userlist.php`
- Property: `propertyview.php`, `propertyadd.php`
- Projects: `projectview.php`, `add_project.php`
- About: `aboutview.php`
- Contact: `contactview.php`
- CRM: `customer_management.php`, `booking.php`, `aps_custom_report.php`
- Logout: `logout.php`

## 9) Admin Role Dashboards (role -> dashboard -> quick links)

> Roles are mapped in `dashboard.php` and `admin/admin_login_handler.php`.

### Superadmin
- `admin/superadmin_dashboard.php`
  - Key controls (links in page):
    - `adminlist.php`, `manage_users.php`, `manage_roles.php` / permissions pages
    - `backup_manager.php`
    - `header_footer_settings.php`
    - `audit_access_log_view.php`, `activity_log.php`
    - AI/automation pages like `ai_admin_insights.php`

### Admin (general)
- `admin/dashboard.php` (main admin dashboard)
- `admin/admin_dashboard.php` (stats view)
- `admin/admin_panel.php` (admin+superadmin “panel” style page)

### Director
- `admin/director_dashboard.php`
  - `analytics_dashboard.php`
  - `admin_panel.php`
  - `documents_dashboard.php`
  - `support_dashboard.php`

### Manager
- `admin/manager_dashboard.php`
  - Includes `admin_header.php` + `admin_sidebar.php`
  - Main work expected via sidebar modules

### Office Admin
- `admin/office_admin_dashboard.php`
  - `attendance_dashboard.php`
  - `documents_dashboard.php`
  - `support_dashboard.php`
  - `tasks_dashboard.php`

### Sales
- `admin/sales_dashboard.php`
  - `leads.php`
  - `bookings.php`
  - `analytics_dashboard.php`
  - `documents_dashboard.php`

### Marketing
- `admin/marketing_dashboard.php`
  - `analytics_dashboard.php`
  - `leads.php`
  - `campaigns.php`
  - `documents_dashboard.php`

### IT Head
- `admin/it_dashboard.php`
  - `ai_dashboard.php`
  - `support_dashboard.php`
  - `documents_dashboard.php`
  - `compliance_dashboard.php`

### Operations
- `admin/operations_dashboard.php`
  - `tasks_dashboard.php`
  - `attendance_dashboard.php`
  - `documents_dashboard.php`
  - `support_dashboard.php`

### Legal
- `admin/legal_dashboard.php`
  - `documents_dashboard.php`
  - `compliance_dashboard.php`
  - `cases.php`
  - `support_dashboard.php`

### HR
- `admin/hr_dashboard.php`
  - `employees.php`
  - `leaves.php`
  - `attendance.php`

### Support
- `admin/support_dashboard.php`
  - `support_tickets.php`
  - `add_ticket.php`

### Finance
- `admin/finance_dashboard.php`
  - `expenses.php` (+ export)
  - Also typically uses finance/accounting pages from the sidebar (transactions/ledger)

## 10) How to extend this sitemap

- Add a new role dashboard:
  - Update `dashboard.php` role map and/or `admin/admin_login_handler.php` redirect map
  - Create `admin/<role>_dashboard.php`
  - Add the role’s pages to the chosen sidebar system (classic or updated)

- If you want a **complete admin sitemap**, the best sources are:
  - `admin/header.php` (largest menu list)
  - `admin/updated-admin-sidebar.php` (curated list)
  - `ADMIN_FUNCTIONALITY_MAPPING.md` (module list + risks)

## 11) Admin Dashboards → Linked Pages Index (high-signal)

यह section dashboard pages के अंदर मौजूद direct links (quick-links/buttons) का “index” है।
Note: कुछ dashboards केवल `logout.php` link रखते हैं; ऐसे dashboards नीचे grouped हैं।

### High-signal dashboards (many links)

#### `admin/enhanced_dashboard.php`
- Links:
  - `ai_dashboard.php`
  - `analytics_dashboard.php`
  - `associates_management.php`
  - `attendance.php`
  - `bookings.php`
  - `employees.php`
  - `leads.php`
  - `leaves.php`
  - `manage_roles.php`
  - `manage_users.php`
  - `projects.php`
  - `properties.php`
  - `reports.php`
  - `security_logs.php`
  - `settings.php`
  - `system_health.php`

#### `admin/superadmin_dashboard.php`
- Links:
  - `logout.php`
  - `adminlist.php`, `userlist.php`, `adminedit.php`
  - `manage_users.php`
  - `register.php`
  - `activity_log.php`, `audit_access_log_view.php`, `log_archive_view.php`
  - `backup_manager.php`
  - `header_footer_settings.php`
  - `2fa_setup.php`
  - `ai_admin_insights.php`
  - `fetch_permissions.php`, `fetch_settings.php`, `fetch_ai_settings.php`
  - (plus dashboard navigation links like `dashboard.php`, `admin_dashboard.php`, `employee_dashboard.php`)

#### `admin/mlm_dashboard.php`
- Links:
  - `mlm_associates.php`
  - `mlm_commissions.php` (also filters like `?status=paid` / `?status=pending`)
  - `mlm_payouts.php`
  - `mlm_reports.php`
  - `mlm_salary.php`
  - `mlm_settings.php`
  - `admin_dashboard.php`

#### `admin/advanced_crm_dashboard.php`
- Links:
  - `customers.php`
  - `leads.php`
  - `opportunities.php`
  - `reports.php`
  - `logout.php`

### Role dashboards (quick links)

#### `admin/sales_dashboard.php`
- Links: `leads.php`, `bookings.php`, `analytics_dashboard.php`, `documents_dashboard.php`

#### `admin/marketing_dashboard.php`
- Links: `analytics_dashboard.php`, `leads.php`, `campaigns.php`, `documents_dashboard.php`

#### `admin/it_dashboard.php`
- Links: `ai_dashboard.php`, `support_dashboard.php`, `documents_dashboard.php`, `compliance_dashboard.php`

#### `admin/operations_dashboard.php`
- Links: `tasks_dashboard.php`, `attendance_dashboard.php`, `documents_dashboard.php`, `support_dashboard.php`

#### `admin/legal_dashboard.php`
- Links: `documents_dashboard.php`, `compliance_dashboard.php`, `cases.php`, `support_dashboard.php`

#### `admin/hr_dashboard.php`
- Links: `employees.php`, `leaves.php`, `attendance.php`

#### `admin/support_dashboard.php`
- Links: `support_tickets.php`, `add_ticket.php`

#### `admin/employee_dashboard.php` (official employee role)
- Links: `my_tasks.php`, `tickets.php`, `documents_dashboard.php`, `attendance_dashboard.php`

#### `admin/director_dashboard.php`
- Links: `analytics_dashboard.php`, `admin_panel.php`, `documents_dashboard.php`, `support_dashboard.php`

#### `admin/office_admin_dashboard.php`
- Links: `attendance_dashboard.php`, `documents_dashboard.php`, `support_dashboard.php`, `tasks_dashboard.php`

#### `admin/finance_dashboard.php`
- Links: `expenses.php?export=csv` (finance/accounting module pages आम तौर पर sidebar से navigate होते हैं)

### Dashboards that mostly link only Logout

- Examples: `admin/ceo_dashboard.php`, `admin/cfo_dashboard.php`, `admin/cm_dashboard.php`, `admin/coo_dashboard.php`, `admin/cto_dashboard.php`, `admin/super_admin_dashboard.php`, `admin/it_head_dashboard.php`, `admin/official_employee_dashboard.php`, etc.

## 12) Session Keys / Auth Flags Matrix (routing clarity)

यह matrix इसलिए जरूरी है क्योंकि codebase में multiple session styles coexist कर रहे हैं:

### Public-side (root) dashboards
- **`mlm_dashboard.php`**
  - Auth: `$_SESSION['user_id']`
- **`associate_dashboard.php`**
  - Auth: `includes/session.php` helpers (`isAuthenticated()` + `getUserType() === 'associate'`)
  - Session key used inside: `$_SESSION['uid']`
- **`agent_dashboard.php`**
  - Auth: `$_SESSION['agent_id']`
- **`employee_dashboard.php`**
  - Auth: `$_SESSION['uid']` + `$_SESSION['utype'] === 'employee'`
- **`user_dashboard.php`**
  - Auth: `$_SESSION['uid']` + `$_SESSION['utype'] === 'user'` (plus `enforceRole([...])` helper)

### Admin-side dashboards
- **Main admin login flow**
  - `admin/index.php` sets/uses: `$_SESSION['admin_session']['is_authenticated']`
  - `admin/admin_login_handler.php` also sets: `$_SESSION['admin_logged_in']`, `$_SESSION['admin_role']`, `$_SESSION['auser']` (varies across files)
- **Admin role dashboards (common style)**
  - Auth: `$_SESSION['admin_logged_in'] === true` + `$_SESSION['admin_role']` checks
- **Some legacy admin dashboards**
  - Auth: `$_SESSION['auser']` (older admin session key)
- **Customer dashboard (admin folder)**
  - Auth: `$_SESSION['customer_logged_in']`

### Routing hub
- **`dashboard.php`** redirects based on a mix of:
  - `$_SESSION['user_logged_in']`
  - `$_SESSION['admin_logged_in']` + `$_SESSION['admin_role']`
  - `$_SESSION['user_id']`

Practical implication: अगर किसी login flow में एक set of session keys set हुआ लेकिन किसी dashboard ने दूसरा set expect किया, तो redirect loops / unauthorized हो सकता है।
