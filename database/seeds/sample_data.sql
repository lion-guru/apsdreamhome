-- Disable foreign key checks temporarily
SET FOREIGN_KEY_CHECKS = 0;

-- Clear existing data (be careful with this in production!)
TRUNCATE TABLE associate_levels;
TRUNCATE TABLE associates;
TRUNCATE TABLE customers;
TRUNCATE TABLE properties;
TRUNCATE TABLE bookings;
TRUNCATE TABLE payments;
TRUNCATE TABLE emi_schedule;

-- Enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1;

-- Insert sample associate levels
INSERT INTO associate_levels (name, commission_percent, min_business, max_business, direct_referral_bonus, level_bonus, reward_description, min_team_size, status)
VALUES
('Starter', 5.00, 0, 500000, 1.00, 0.00, 'Basic level for new associates', 0, 'active'),
('Bronze', 7.00, 500001, 2000000, 1.50, 0.50, 'Bronze level with increased commission', 3, 'active'),
('Silver', 10.00, 2000001, 5000000, 2.00, 1.00, 'Silver level with higher rewards', 10, 'active'),
('Gold', 12.50, 5000001, 10000000, 2.50, 1.50, 'Gold level with premium benefits', 25, 'active'),
('Platinum', 15.00, 10000001, 999999999, 3.00, 2.00, 'Top level with maximum benefits', 50, 'active');

-- Insert sample associates (MLM team)
-- Level 1 (Top level)
INSERT INTO associates (name, email, phone, commission_rate, address, city, state, pincode, aadhar_number, pan_number, bank_account, ifsc_code, level_id, status, created_at)
VALUES
('Rahul Sharma', 'rahul.sharma@example.com', '9876543210', 15.00, '123 MG Road', 'Mumbai', 'Maharashtra', '400001', '123412341234', 'ABCDE1234F', '12345678901234', 'HDFC0001234', 5, 'active', '2024-01-15 10:00:00');

-- Get the ID of the first associate
SET @rahul_id = LAST_INSERT_ID();

-- Level 2 (Rahul's direct referrals)
INSERT INTO associates (name, email, phone, commission_rate, address, city, state, pincode, aadhar_number, pan_number, bank_account, ifsc_code, sponsor_id, level_id, status, created_at)
VALUES
('Priya Patel', 'priya.patel@example.com', '9876543211', 10.00, '456 Linking Road', 'Mumbai', 'Maharashtra', '400052', '234523452345', 'BCDEF2345G', '23456789012345', 'ICIC0001234', @rahul_id, 3, 'active', '2024-02-01 11:30:00'),
('Amit Singh', 'amit.singh@example.com', '9876543212', 12.50, '789 Andheri East', 'Mumbai', 'Maharashtra', '400069', '345634563456', 'CDEFG3456H', '34567890123456', 'SBIN0001234', @rahul_id, 4, 'active', '2024-02-05 14:15:00');

-- Get the IDs of level 2 associates
SET @priya_id = @rahul_id + 1;
SET @amit_id = @rahul_id + 2;

-- Level 3 (Referrals of Priya and Amit)
INSERT INTO associates (name, email, phone, commission_rate, address, city, state, pincode, aadhar_number, pan_number, bank_account, ifsc_code, sponsor_id, level_id, status, created_at)
VALUES
('Neha Gupta', 'neha.gupta@example.com', '9876543213', 7.00, '321 Bandra West', 'Mumbai', 'Maharashtra', '400050', '456745674567', 'DEFGH4567I', '45678901234567', 'HDFC0002345', @priya_id, 2, 'active', '2024-02-15 09:45:00'),
('Vikram Joshi', 'vikram.joshi@example.com', '9876543214', 5.00, '654 Juhu', 'Mumbai', 'Maharashtra', '400049', '567856785678', 'EFGHI5678J', '56789012345678', 'ICIC0002345', @amit_id, 1, 'active', '2024-02-20 16:30:00'),
('Ananya Reddy', 'ananya.reddy@example.com', '9876543215', 5.00, '987 Powai', 'Mumbai', 'Maharashtra', '400076', '678967896789', 'FGHIJ6789K', '67890123456789', 'SBIN0002345', @priya_id, 1, 'inactive', '2024-02-25 13:20:00');

-- Insert sample customers
INSERT INTO users (name, email, phone, password, type, status, created_at)
VALUES
('Customer One', 'customer1@example.com', '9876532101', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', 'active', '2024-01-10 10:00:00'),
('Customer Two', 'customer2@example.com', '9876532102', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', 'active', '2024-01-15 11:30:00'),
('Customer Three', 'customer3@example.com', '9876532103', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', 'active', '2024-02-05 14:15:00'),
('Customer Four', 'customer4@example.com', '9876532104', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', 'active', '2024-02-20 16:30:00'),
('Customer Five', 'customer5@example.com', '9876532105', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', 'active', '2024-03-01 09:45:00');

-- Link customers to users and associates
INSERT INTO customers (user_id, name, email, mobile, customer_type, kyc_status, referred_by, created_at)
SELECT 
    id, 
    name, 
    email, 
    phone, 
    'individual', 
    CASE WHEN id % 3 = 0 THEN 'pending' WHEN id % 3 = 1 THEN 'verified' ELSE 'rejected' END,
    CASE 
        WHEN id % 4 = 0 THEN @rahul_id
        WHEN id % 4 = 1 THEN @priya_id
        WHEN id % 4 = 2 THEN @amit_id
        ELSE NULL
    END,
    created_at
FROM users 
WHERE type = 'customer';

-- Get property type IDs
SET @apartment_type_id = (SELECT id FROM property_types WHERE name = 'Apartment' LIMIT 1);
SET @villa_type_id = (SELECT id FROM property_types WHERE name = 'Villa' LIMIT 1);
SET @studio_type_id = (SELECT id FROM property_types WHERE name = 'Studio' LIMIT 1);
SET @penthouse_type_id = (SELECT id FROM property_types WHERE name = 'Penthouse' LIMIT 1);

-- Insert sample properties
INSERT INTO properties (title, slug, description, property_type_id, price, area_sqft, bedrooms, bathrooms, address, city, state, country, postal_code, status, featured, created_at)
VALUES
('Luxury Apartment in Bandra', 'luxury-apartment-bandra', 'Beautiful 3BHK apartment with sea view', @apartment_type_id, 25000000, 1800, 3, 3, '12 Hill Road', 'Mumbai', 'Maharashtra', 'India', '400050', 'available', 1, '2024-01-05 10:00:00'),
('Modern Villa in Powai', 'modern-villa-powai', 'Spacious 4BHK villa with modern amenities', @villa_type_id, 45000000, 3500, 4, 4, '34 Hiranandani Gardens', 'Mumbai', 'Maharashtra', 'India', '400076', 'available', 1, '2024-01-10 11:30:00'),
('Cozy Studio in Andheri', 'cozy-studio-andheri', 'Compact studio apartment in prime location', @studio_type_id, 8500000, 500, 1, 1, '56 SV Road', 'Mumbai', 'Maharashtra', 'India', '400058', 'available', 0, '2024-01-15 14:15:00'),
('Luxury Penthouse in Worli', 'luxury-penthouse-worli', 'Exclusive penthouse with panoramic city views', @penthouse_type_id, 75000000, 4500, 5, 5, '78 Dr. Annie Besant Road', 'Mumbai', 'Maharashtra', 'India', '400018', 'available', 1, '2024-01-20 16:30:00'),
('Resale Flat in Juhu', 'resale-flat-juhu', 'Well-maintained 2BHK resale flat', @apartment_type_id, 18000000, 1100, 2, 2, '90 Juhu Tara Road', 'Mumbai', 'Maharashtra', 'India', '400049', 'sold', 0, '2024-01-25 09:45:00');

-- Insert sample bookings
INSERT INTO bookings (customer_id, property_id, associate_id, booking_number, booking_date, amount, total_amount, payment_plan, status, source, created_at)
SELECT 
    c.id,
    p.id,
    CASE 
        WHEN c.id % 3 = 0 THEN @rahul_id
        WHEN c.id % 3 = 1 THEN @priya_id
        ELSE @amit_id
    END,
    CONCAT('BK', LPAD(c.id, 5, '0')),
    DATE_ADD('2024-01-01', INTERVAL c.id * 7 DAY),
    p.price * 0.1, -- 10% booking amount
    p.price,
    CASE 
        WHEN c.id % 3 = 0 THEN 'emi'
        WHEN c.id % 3 = 1 THEN 'installment'
        ELSE 'full_payment'
    END,
    CASE 
        WHEN p.status = 'sold' THEN 'completed'
        WHEN c.id % 4 = 0 THEN 'cancelled'
        ELSE 'confirmed'
    END,
    CASE 
        WHEN c.id % 3 = 0 THEN 'associate'
        WHEN c.id % 3 = 1 THEN 'online'
        ELSE 'direct'
    END,
    NOW()
FROM customers c
CROSS JOIN properties p
WHERE (c.id + p.id) % 3 = 0
LIMIT 10;

-- Insert sample payments
INSERT INTO payments (booking_id, amount, payment_date, method, status, created_at)
SELECT 
    b.id,
    CASE 
        WHEN b.payment_plan = 'full_payment' THEN b.amount
        ELSE b.amount * 0.5 -- 50% for EMI/installment first payment
    END,
    DATE_ADD(b.booking_date, INTERVAL 1 DAY),
    CASE 
        WHEN b.id % 4 = 0 THEN 'credit_card'
        WHEN b.id % 4 = 1 THEN 'debit_card'
        WHEN b.id % 4 = 2 THEN 'net_banking'
        ELSE 'upi'
    END,
    'completed',
    NOW()
FROM bookings b
WHERE b.status != 'cancelled';

-- Insert EMI schedules for EMI payment plans
INSERT INTO emi_schedule (customer_id, booking_id, emi_number, amount, due_date, status, created_at)
SELECT 
    b.customer_id,
    b.id,
    n.n as emi_number,
    (b.amount * 0.9) / 12 as emi_amount, -- Remaining 90% over 12 months
    DATE_ADD(b.booking_date, INTERVAL n.n MONTH) as due_date,
    'pending',
    NOW()
FROM bookings b
CROSS JOIN (SELECT 1 as n UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 
            UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9 UNION SELECT 10
            UNION SELECT 11 UNION SELECT 12) n
WHERE b.payment_plan = 'emi'
AND b.status != 'cancelled'
ORDER BY b.id, n.n;

-- Update associate business and earnings based on bookings
UPDATE associates a
JOIN (
    SELECT 
        associate_id, 
        COUNT(*) as total_bookings, 
        SUM(amount) as total_business,
        SUM(amount * (SELECT commission_rate/100 FROM associates WHERE id = b.associate_id)) as total_earnings
    FROM bookings b
    WHERE b.status != 'cancelled'
    GROUP BY associate_id
) b ON a.id = b.associate_id
SET 
    a.total_business = b.total_business,
    a.total_earnings = b.total_earnings;

-- Update associate levels based on business volume
UPDATE associates a
JOIN associate_levels al ON a.total_business BETWEEN al.min_business AND al.max_business
SET a.level_id = al.id
WHERE a.status = 'active';
