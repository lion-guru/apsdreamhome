-- View for customer summary (using users table for customer details)
CREATE OR REPLACE VIEW `customer_summary` AS
SELECT
    c.id as customer_id,
    u.name as customer_name,
    u.email,
    u.phone as mobile,
    c.customer_type,
    c.kyc_status,
    COUNT(DISTINCT b.id) as total_bookings,
    COALESCE(SUM(b.amount), 0) as total_investment,
    MAX(b.booking_date) as last_booking_date,
    DATEDIFF(CURRENT_DATE, MAX(IFNULL(b.booking_date, CURRENT_DATE))) as days_since_last_booking,
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
    COALESCE(SUM(b.amount), 0) as total_sales_amount,
    (COALESCE(SUM(b.amount), 0) * (a.commission_rate/100)) as estimated_commission
FROM associates a
LEFT JOIN customers c ON a.id = c.referred_by
LEFT JOIN bookings b ON a.id = b.associate_id
GROUP BY a.id, a.name, a.commission_rate, a.status;

-- View for booking summary
CREATE OR REPLACE VIEW `booking_summary` AS
SELECT
    b.id as booking_id,
    b.booking_number,
    b.booking_date,
    c.id as customer_id,
    u.name as customer_name,
    u.phone as customer_phone,
    p.id as property_id,
    p.title as property_title,
    p.location as property_location,
    b.amount as booking_amount,
    b.status as booking_status,
    a.id as associate_id,
    a.name as associate_name,
    b.created_at
FROM bookings b
LEFT JOIN customers c ON b.customer_id = c.id
LEFT JOIN users u ON c.user_id = u.id
LEFT JOIN properties p ON b.property_id = p.id
LEFT JOIN associates a ON b.associate_id = a.id
ORDER BY b.booking_date DESC;

-- View for payment summary
CREATE OR REPLACE VIEW `payment_summary` AS
SELECT
    p.id as payment_id,
    p.booking_id,
    b.booking_number,
    c.id as customer_id,
    u.name as customer_name,
    p.amount as payment_amount,
    p.payment_date,
    p.method as payment_method,
    p.status as payment_status,
    b.amount as booking_amount,
    (SELECT COALESCE(SUM(amount), 0) FROM payments WHERE booking_id = b.id AND status = 'completed') as total_paid_amount,
    (b.amount - (SELECT COALESCE(SUM(amount), 0) FROM payments WHERE booking_id = b.id AND status = 'completed')) as pending_amount
FROM payments p
LEFT JOIN bookings b ON p.booking_id = b.id
LEFT JOIN customers c ON b.customer_id = c.id
LEFT JOIN users u ON c.user_id = u.id
ORDER BY p.payment_date DESC;
