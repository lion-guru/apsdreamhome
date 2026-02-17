APS Dream Home Admin Panel — Role-Based Dashboards
==================================================

This project now supports fully automatic, role-based dashboards for all major employee and admin roles.

How it works:
-------------
- After login, only admin/official users are allowed to log in from the admin panel (index.php/process_login.php), and are redirected to their dedicated dashboard based on their role (see process_login.php and admin_login_handler.php for logic).
- Each dashboard is a modern, Bootstrap-based PHP file with features and quick links tailored to the role.
- The superadmin dashboard consolidates all management controls, analytics, settings, and logs for future-proof admin operations.

Roles and Dashboards:
---------------------
- admin → admin_dashboard.php (Admin Dashboard)
- super_admin / superadmin → superadmin_dashboard.php (Super Admin Panel)
- official_employee → employee_dashboard.php
- finance → finance_dashboard.php
- hr → hr_dashboard.php
- it_head → it_dashboard.php
- legal → legal_dashboard.php
- marketing → marketing_dashboard.php
- sales → sales_dashboard.php
- support → support_dashboard.php
- operations → operations_dashboard.php
- office_admin → office_admin_dashboard.php
- director → director_dashboard.php

To add a new role:
------------------
1. Add the role to the process_login.php and admin_login_handler.php redirection logic.
2. Create a new dashboard file (copy any existing dashboard as a template).
3. Update permissions as needed in the database and/or SuperAdminController.

Progress & Next Steps:
----------------------
- [x] Admin login is now restricted to admin/official users via /admin/index.php only.
- [x] Role-based redirection after login is implemented in process_login.php/admin_login_handler.php.
- [x] Session and role checks are enforced in admin dashboards (dashboard.php, dashboard-modern.php, etc.).
- [ ] Next: Review and enhance session/role protection in each dashboard file as needed.
- [ ] Next: Regularly update this file with any new roles, dashboards, or logic changes.

Security:
---------
- Role-based access is enforced in each dashboard file and throughout the admin panel.

For more details, see the code comments in process_login.php, admin_login_handler.php, and each dashboard file.
