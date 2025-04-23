# APS Dream Homes Developer & Admin Handbook

## 1. Project Overview
APS Dream Homes is an enterprise-grade real estate platform with advanced automation, AI, analytics, marketplace, and multi-cloud support.

## 2. Architecture
- **Backend:** Modular PHP, MySQL, RBAC, API-first
- **Frontend:** Bootstrap 5, FontAwesome, modern card-based dashboards, custom JS, onboarding tours, push notifications
- **Cloud:** Multi-cloud ready, auto-scaling, backup/restore scripts
- **APIs:** Internal/external, developer portal, monetization

## 3. Module Guide
- **Admin:** `/admin/` – Dashboards (modernized, card-based, AI-powered), analytics, compliance, onboarding, chat, rewards, feedback, app store, scaling, partner tools
- **Customer/Agent/Investor/Employee/Tenant/Builder/Superadmin:** Modernized, responsive dashboards with AI chatbot, suggestions panel, export/share, and card-based stats
- **AI/ML:** Fraud detection, legal review, app recommendation, sentiment analysis, AI chatbots, AI-powered suggestions
- **Marketplace:** App listing, reviews, partner certification, monetization
- **No-Code:** Drag-and-drop builder for workflows/dashboards
- **Payments:** Global, multi-currency, in-app purchases

## 4. Operations
- **Backups:** Use `scripts/backup.sh` and `scripts/restore.sh`
- **CI/CD:** `scripts/ci_cd.ps1` for linting, tests, and deployment
- **Health:** `admin/health_check.php` for self-healing and monitoring
- **Duplicate Scan:** Use `duplicate_files_report.csv` for codebase hygiene
- **Duplicate/Legacy Files:** All major dashboard and legacy duplicate files have been removed as of April 2025. Ongoing codebase hygiene is enforced.

## 5. Developer Onboarding
- Read `README.md` and this handbook
- Set up `.env` and database as per instructions
- Run all migration scripts in `/database/`
- Use onboarding tours and analytics dashboard for feature discovery
- All dashboards now use Bootstrap 5, FontAwesome, and card-based responsive layouts. Refer to any dashboard file for design patterns.

## 6. Coding & Database Standards
- Use prepared statements for all DB queries
- Modularize new features (separate admin, API, customer, and DB logic)
- Document new modules in this handbook and README
- Use RBAC and audit logging for all sensitive actions
- Follow modern dashboard UI/UX standards for all user-facing modules

## 7. Troubleshooting & Escalation
- Check `admin/health_check.php` for system health
- Review error logs and analytics dashboard for issues
- Restore from backup if needed
- Escalate to lead dev/ops for critical incidents

## 8. Demo Employee Data Setup

### Table: `employees`
- **Purpose:** Stores employee records for the platform, including demo users for testing.
- **Fields:**
    - `id` (INT, AUTO_INCREMENT, PRIMARY KEY)
    - `name` (VARCHAR)
    - `email` (VARCHAR, UNIQUE)
    - `phone` (VARCHAR)
    - `role` (VARCHAR, default 'employee')
    - `status` (VARCHAR, default 'active')
    - `password` (VARCHAR, bcrypt hash)
    - `created_at` (TIMESTAMP)

### Demo Data Import
- The file [`database/create_employees_table.sql`](database/create_employees_table.sql) creates the table and inserts 5 demo employees.
- All demo employees use the password: **Aps@128128** (bcrypt hash stored).

#### Steps to Import (XAMPP/Windows)
1. Open phpMyAdmin (`http://localhost/phpmyadmin`)
2. Select the database: `apsdreamhomefinal`
3. Use the Import feature to run:
   - `database/create_employees_table.sql`
   - (If not already run) `database/insert_sample_data.sql`

**Command Line (CMD):**
```
mysql -u root apsdreamhomefinal < database\create_employees_table.sql
mysql -u root apsdreamhomefinal < database\insert_sample_data.sql
