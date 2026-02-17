-- APS Dream Homes Data Migration Script
-- This script migrates core business and CRM/marketing tables from your old database structure to the new normalized schema.
-- Run this after importing the new schema and restoring your old data to a temp database (e.g., apsdreamhome_old).

-- 1. ADMIN TABLE MIGRATION
INSERT INTO admin (aid, auser, apass, role, status, email, phone, created_at)
SELECT aid, auser, apass, role, status, aemail, aphone, NOW() FROM apsdreamhome_old.admin;

-- 2. ASSOCIATES TABLE MIGRATION
INSERT INTO associates (id, name, email, phone, level, created_at)
SELECT associate_id, NULL, NULL, NULL, level, created_at FROM apsdreamhome_old.associates;

-- 3. ASSOCIATE LEVELS MIGRATION
INSERT INTO associate_levels (id, level, commission_percent, description)
SELECT level_id, level_name, commission_percentage, reward_description FROM apsdreamhome_old.associate_levels;

-- 4. USERS TABLE MIGRATION (CUSTOMERS/INVESTORS)
-- You may need to adjust this if you have a users or user_backup table
-- Example:
-- INSERT INTO users (id, name, email, phone, type, status, address, created_at)
-- SELECT uid, uname, uemail, uphone, 'customer', 'active', NULL, NOW() FROM apsdreamhome_old.users;

-- 5. BOOKINGS TABLE MIGRATION
INSERT INTO bookings (id, plot_id, customer_id, booking_date, status, amount, created_at)
SELECT booking_id, property_id, NULL, booking_date, status, amount, NOW() FROM apsdreamhome_old.bookings;

-- 6. LEADS TABLE MIGRATION
INSERT INTO leads (id, name, email, phone, source, status, notes, created_at)
SELECT lead_id, name, email, phone, NULL, status, notes, created_at FROM apsdreamhome_old.leads;

-- 7. ACTIVITIES (CRM) TABLE MIGRATION
INSERT INTO activities (activity_id, lead_id, opportunity_id, type, subject, description, due_date, completed, completed_date, created_by, assigned_to, created_at, updated_at)
SELECT activity_id, lead_id, opportunity_id, type, subject, description, due_date, completed, completed_date, created_by, assigned_to, created_at, updated_at FROM apsdreamhome_old.activities;

-- 8. CAMPAIGNS TABLE MIGRATION
INSERT INTO campaigns (campaign_id, name, description, type, status, start_date, end_date, budget, expected_revenue, actual_cost, actual_revenue, created_at, updated_at)
SELECT campaign_id, name, description, type, status, start_date, end_date, budget, expected_revenue, actual_cost, actual_revenue, created_at, updated_at FROM apsdreamhome_old.campaigns;

-- 9. CAMPAIGN MEMBERS TABLE MIGRATION
INSERT INTO campaign_members (member_id, campaign_id, lead_id, status, created_at, updated_at)
SELECT member_id, campaign_id, lead_id, status, created_at, updated_at FROM apsdreamhome_old.campaign_members;

-- 10. CONTACT BACKUP TABLE MIGRATION
INSERT INTO contact_backup (cid, name, email, phone, subject, message, status, created_at)
SELECT cid, name, email, phone, subject, message, status, created_at FROM apsdreamhome_old.contact_backup;

-- 11. AUDIT LOG MIGRATION
INSERT INTO audit_log (id, user_id, action, entity_type, entity_id, changes, ip_address, created_at)
SELECT id, user_id, action, entity_type, entity_id, changes, ip_address, created_at FROM apsdreamhome_old.audit_log;

-- 12. CAREER APPLICATIONS MIGRATION
INSERT INTO career_applications (id, name, phone, email, file_name, file_type, file_size, comments, created_at, file_data)
SELECT id, name, phone, email, file_name, file_type, file_size, comments, created_at, file_data FROM apsdreamhome_old.career_applications;

-- 13. CITY TABLE MIGRATION
INSERT INTO city (cid, cname, sid)
SELECT cid, cname, sid FROM apsdreamhome_old.city;

-- 14. COMMISSION TRANSACTIONS MIGRATION
INSERT INTO commission_transactions (transaction_id, associate_id, booking_id, business_amount, commission_amount, commission_percentage, level_difference_amount, upline_id, transaction_date, status)
SELECT transaction_id, associate_id, booking_id, business_amount, commission_amount, commission_percentage, level_difference_amount, upline_id, transaction_date, status FROM apsdreamhome_old.commission_transactions;

-- 15. COMMUNICATIONS MIGRATION
INSERT INTO communications (communication_id, lead_id, opportunity_id, type, direction, subject, content, communication_date, user_id, created_at)
SELECT communication_id, lead_id, opportunity_id, type, direction, subject, content, communication_date, user_id, created_at FROM apsdreamhome_old.communications;

-- 16. COMPONENTS MIGRATION
INSERT INTO components (id, name, type, content, is_active, created_at, updated_at, created_by)
SELECT id, name, type, content, is_active, created_at, updated_at, created_by FROM apsdreamhome_old.components;

-- 17. BOOKING PAYMENTS MIGRATION
INSERT INTO booking_payments (payment_id, booking_id, payment_amount, payment_date, payment_method, transaction_id, payment_notes)
SELECT payment_id, booking_id, payment_amount, payment_date, payment_method, transaction_id, payment_notes FROM apsdreamhome_old.booking_payments;

-- Add more migrations as needed for other CRM/marketing tables.

-- NOTE: You may need to adjust field mappings if column names/types are different or if you want to migrate only specific data.
-- Always test this script on a backup before running on production.
