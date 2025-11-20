-- View for land management summary
CREATE OR REPLACE VIEW `land_management_summary` AS
SELECT
    s.id as site_id,
    s.site_name,
    s.location,
    s.total_area,
    s.developed_area,
    s.status as site_status,
    COUNT(DISTINCT lp.id) as total_land_parcels,
    SUM(lp.area) as total_land_area,
    SUM(lp.purchase_price) as total_investment,
    COUNT(DISTINCT pd.id) as total_plots,
    COUNT(DISTINCT CASE WHEN pd.status = 'sold' THEN pd.id END) as plots_sold,
    COUNT(DISTINCT CASE WHEN pd.status = 'available' THEN pd.id END) as plots_available,
    COUNT(DISTINCT cp.id) as construction_projects,
    SUM(CASE WHEN cp.status = 'completed' THEN 1 ELSE 0 END) as completed_projects
FROM sites s
LEFT JOIN land_purchases lp ON s.id = lp.site_id
LEFT JOIN plot_development pd ON s.id = pd.site_id
LEFT JOIN construction_projects cp ON s.id = cp.site_id
GROUP BY s.id, s.site_name, s.location, s.total_area, s.developed_area, s.status;

-- View for builder performance
CREATE OR REPLACE VIEW `builder_performance` AS
SELECT
    b.id as builder_id,
    b.name as builder_name,
    b.contact_person,
    b.phone,
    b.email,
    b.rating,
    COUNT(DISTINCT cp.id) as total_projects,
    SUM(CASE WHEN cp.status = 'completed' THEN 1 ELSE 0 END) as completed_projects,
    SUM(CASE WHEN cp.status = 'in_progress' THEN 1 ELSE 0 END) as in_progress_projects,
    SUM(cp.budget) as total_budget_managed,
    AVG(cp.quality_rating) as avg_quality_rating,
    DATEDIFF(MAX(cp.completion_date), MIN(cp.start_date)) / COUNT(DISTINCT cp.id) as avg_days_per_project
FROM builders b
LEFT JOIN construction_projects cp ON b.id = cp.builder_id
GROUP BY b.id, b.name, b.contact_person, b.phone, b.email, b.rating;

-- View for customer summary
CREATE OR REPLACE VIEW `customer_summary` AS
SELECT
    c.id as customer_id,
    c.name as customer_name,
    c.email,
    c.mobile,
    c.city,
    c.state,
    c.kyc_status,
    COUNT(DISTINCT b.id) as total_bookings,
    SUM(b.amount) as total_investment,
    COUNT(DISTINCT p.id) as total_properties_owned,
    MAX(b.booking_date) as last_booking_date,
    DATEDIFF(CURRENT_DATE, MAX(b.booking_date)) as days_since_last_booking,
    c.created_at as customer_since
FROM customers c
LEFT JOIN bookings b ON c.id = b.customer_id
LEFT JOIN properties p ON c.id = p.owner_id
GROUP BY c.id, c.name, c.email, c.mobile, c.city, c.state, c.kyc_status, c.created_at;

-- View for MLM performance
CREATE OR REPLACE VIEW `mlm_performance` AS
SELECT
    a.id as associate_id,
    a.name as associate_name,
    al.level_name,
    a.total_business,
    a.total_earnings,
    COUNT(DISTINCT c.id) as total_referrals,
    COUNT(DISTINCT b.id) as total_sales,
    SUM(b.amount) as total_sales_amount,
    COUNT(DISTINCT m.id) as team_members,
    SUM(m.total_business) as team_business,
    SUM(mc.commission_amount) as total_commissions,
    SUM(CASE WHEN mc.status = 'paid' THEN mc.commission_amount ELSE 0 END) as paid_commissions,
    SUM(CASE WHEN mc.status = 'pending' THEN mc.commission_amount ELSE 0 END) as pending_commissions
FROM associates a
LEFT JOIN associate_levels al ON a.level_id = al.id
LEFT JOIN customers c ON a.id = c.referred_by
LEFT JOIN bookings b ON a.id = b.associate_id
LEFT JOIN associates m ON m.sponsor_id = a.id
LEFT JOIN mlm_commissions mc ON a.id = mc.associate_id
GROUP BY a.id, a.name, al.level_name, a.total_business, a.total_earnings;
