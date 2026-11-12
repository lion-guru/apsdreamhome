-- APS Dream Home - seed users for the Visual Regression CI job
--
-- WHY (S463): the Visual Regression job boots a FRESH MariaDB and imports
-- only the schema, so there is not a single row in `users`.  The dev
-- `?test_login=N` bypass picks a user BY ROLE
-- (App\Http\Controllers\Auth\AdminAuthController):
--     test_login=1 -> super_admin/admin/manager
--     test_login=5 -> associate
--     test_login=6 -> agent
--     test_login=7 -> customer
-- and falls back to a synthetic admin when the role has no row - which
-- means, in CI, the customer / associate / agent dashboards could never
-- render and the suite spent 60 s per page on a login timeout.
--
-- This file inserts exactly the four accounts the suite needs.  It is
-- IDEMPOTENT (each insert is guarded by a NOT EXISTS) and safe to re-run.
--
-- The password is the project's standard dev password (Aps@2026); the
-- visual suite logs in through the dev bypass, so the hash is never even
-- checked in CI.

SET @pw := '$2y$10$nk57HywLwr.n.79krSTtLeu.9/9mzT1T6FR0KiGRNJ6rUgl8neMHu';

INSERT INTO users
  (tenant_id, name, email, phone, password, role, status, registration_status, mlm_rank, created_at, updated_at)
SELECT 1, 'Visual Admin', 'visual-admin@apsdreamhome.local', '9000000001', @pw,
       'super_admin', 'active', 'approved', 'associate', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM users WHERE email = 'visual-admin@apsdreamhome.local');

INSERT INTO users
  (tenant_id, name, email, phone, password, role, status, registration_status, mlm_rank, created_at, updated_at)
SELECT 1, 'Visual Customer', 'visual-customer@apsdreamhome.local', '9000000002', @pw,
       'customer', 'active', 'approved', 'Associate', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM users WHERE email = 'visual-customer@apsdreamhome.local');

INSERT INTO users
  (tenant_id, name, email, phone, password, role, status, registration_status, mlm_rank, created_at, updated_at)
SELECT 1, 'Visual Associate', 'visual-associate@apsdreamhome.local', '9000000003', @pw,
       'associate', 'active', 'approved', 'associate', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM users WHERE email = 'visual-associate@apsdreamhome.local');

INSERT INTO users
  (tenant_id, name, email, phone, password, role, status, registration_status, mlm_rank, created_at, updated_at)
SELECT 1, 'Visual Agent', 'visual-agent@apsdreamhome.local', '9000000004', @pw,
       'agent', 'active', 'approved', 'associate', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM users WHERE email = 'visual-agent@apsdreamhome.local');

-- the customer wallet row the customer dashboard reads
INSERT INTO wallet_points
  (tenant_id, user_id, points_balance, total_earned, total_used, status, created_at, updated_at)
SELECT 1, u.id, 0.00, 0.00, 0.00, 'active', NOW(), NOW()
FROM users u
WHERE u.email = 'visual-customer@apsdreamhome.local'
  AND NOT EXISTS (SELECT 1 FROM wallet_points w WHERE w.user_id = u.id);
