-- Active: 1758300303350@@127.0.0.1@3306@apsdreamhomefinal
-- Final verification script to check all tables and their data counts
-- This helps ensure all dashboard widgets have data to display

-- Core tables verification
SELECT 
    'Core Tables' AS category,
    (SELECT COUNT(*) FROM users) AS users_count,
    (SELECT COUNT(*) FROM properties) AS properties_count,
    (SELECT COUNT(*) FROM customers) AS customers_count,
    (SELECT COUNT(*) FROM leads) AS leads_count,
    (SELECT COUNT(*) FROM bookings) AS bookings_count;

-- Supporting tables verification
SELECT 
    'Supporting Tables' AS category,
    (SELECT COUNT(*) FROM property_visits) AS property_visits_count,
    (SELECT COUNT(*) FROM notifications) AS notifications_count,
    (SELECT COUNT(*) FROM feedback) AS feedback_count,
    (SELECT COUNT(*) FROM gallery) AS gallery_count,
    (SELECT COUNT(*) FROM testimonials) AS testimonials_count;

-- Advanced tables verification (these may or may not exist)
SELECT 
    'Advanced Tables' AS category,
    (SELECT COUNT(*) FROM property_features) AS property_features_count,
    (SELECT COUNT(*) FROM property_transactions) AS property_transactions_count,
    (SELECT COUNT(*) FROM communication_logs) AS communication_logs_count,
    (SELECT COUNT(*) FROM agent_commissions) AS agent_commissions_count;

-- Analytics tables verification (these may or may not exist)
SELECT 
    'Analytics Tables' AS category,
    (SELECT COUNT(*) FROM lead_analytics) AS lead_analytics_count,
    (SELECT COUNT(*) FROM property_analytics) AS property_analytics_count,
    (SELECT COUNT(*) FROM agent_analytics) AS agent_analytics_count,
    (SELECT COUNT(*) FROM revenue_analytics) AS revenue_analytics_count;

-- Dashboard-specific tables verification
SELECT 
    'Dashboard Tables' AS category,
    (SELECT COUNT(*) FROM visit_availability) AS visit_availability_count,
    (SELECT COUNT(*) FROM visit_reminders) AS visit_reminders_count,
    (SELECT COUNT(*) FROM customer_engagement) AS customer_engagement_count,
    (SELECT COUNT(*) FROM marketing_campaigns) AS marketing_campaigns_count;
