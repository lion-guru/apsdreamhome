-- View for land management summary (updated to match actual schema)
CREATE OR REPLACE VIEW `land_management_summary` AS
SELECT
    lp.id as land_purchase_id,
    f.id as farmer_id,
    f.name as farmer_name,
    lp.purchase_date,
    lp.amount as purchase_amount,
    lp.registry_no,
    p.id as property_id,
    p.title as property_title,
    p.location as property_location,
    p.total_area,
    p.measurement_unit,
    p.status as property_status,
    COUNT(DISTINCT pd.id) as total_plots,
    COUNT(DISTINCT CASE WHEN pd.status = 'sold' THEN pd.id END) as plots_sold,
    COUNT(DISTINCT CASE WHEN pd.status = 'available' THEN pd.id END) as plots_available
FROM land_purchases lp
LEFT JOIN farmers f ON lp.farmer_id = f.id
LEFT JOIN properties p ON lp.property_id = p.id
LEFT JOIN plot_development pd ON p.id = pd.property_id
GROUP BY lp.id, f.id, f.name, lp.purchase_date, lp.amount, lp.registry_no, p.id, p.title, p.location, p.total_area, p.measurement_unit, p.status;

-- View for builder performance (simplified to match existing schema)
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
    SUM(cp.budget) as total_budget_managed
FROM builders b
LEFT JOIN construction_projects cp ON b.id = cp.builder_id
GROUP BY b.id, b.name, b.contact_person, b.phone, b.email, b.rating;

-- View for customer summary (updated to match actual schema)
CREATE OR REPLACE VIEW `customer_summary` AS
SELECT
    c.id as customer_id,
    u.name as customer_name,
    u.email,
    u.phone as mobile,
    c.customer_type,
    c.kyc_status,
    COUNT(DISTINCT b.id) as total_bookings,
    SUM(b.amount) as total_investment,
    MAX(b.booking_date) as last_booking_date,
    DATEDIFF(CURRENT_DATE, MAX(b.booking_date)) as days_since_last_booking,
    c.created_at as customer_since
FROM customers c
LEFT JOIN users u ON c.user_id = u.id
LEFT JOIN bookings b ON c.id = b.customer_id
GROUP BY c.id, u.name, u.email, u.phone, c.customer_type, c.kyc_status, c.created_at;

-- View for MLM performance (simplified to match existing schema)
CREATE OR REPLACE VIEW `mlm_performance` AS
SELECT
    a.id as associate_id,
    a.name as associate_name,
    a.commission_rate,
    a.status,
    COUNT(DISTINCT c.id) as total_referrals,
    COUNT(DISTINCT b.id) as total_sales,
    SUM(b.amount) as total_sales_amount,
    (SUM(b.amount) * (a.commission_rate/100)) as estimated_commission
FROM associates a
LEFT JOIN customers c ON a.id = c.referred_by
LEFT JOIN bookings b ON a.id = b.associate_id
GROUP BY a.id, a.name, a.commission_rate, a.status;
