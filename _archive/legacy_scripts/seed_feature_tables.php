<?php
/**
 * Comprehensive Seed Script for Empty Feature Tables
 * Seeds 30+ key tables with realistic sample data
 */

$db = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
]);

// Disable FK checks for bulk seeding
$db->exec("SET FOREIGN_KEY_CHECKS = 0");

echo "=== SEEDING EMPTY FEATURE TABLES ===\n\n";
$total = 0;

// Helper: check if table is empty
function isEmpty($db, $table) {
    $stmt = $db->query("SELECT COUNT(*) as c FROM $table");
    return $stmt->fetch()['c'] == 0;
}

// ============================================================
// 1. COMPANY & SETTINGS
// ============================================================
if (isEmpty($db, 'company_settings')) {
    $db->exec("INSERT INTO company_settings (company_name, phone, email, address, description) VALUES (
        'APS Dream Home', '+91 92771 21112', 'info@apsdreamhome.com',
        'Gorakhpur, Uttar Pradesh, India',
        'APS Dream Home is a premier real estate development company specializing in residential colonies, plots, and commercial properties across Uttar Pradesh.'
    )");
    echo "  company_settings: 1 row\n"; $total++;
}

if (isEmpty($db, 'settings')) {
    $db->exec("INSERT INTO settings (`key`, `value`) VALUES
        ('site_name', 'APS Dream Home'),
        ('site_email', 'info@apsdreamhome.com'),
        ('site_phone', '+91 92771 21112'),
        ('site_address', 'Gorakhpur, Uttar Pradesh'),
        ('currency', 'INR'),
        ('timezone', 'Asia/Kolkata'),
        ('date_format', 'd-m-Y'),
        ('pagination_per_page', '20'),
        ('maintenance_mode', '0'),
        ('google_analytics_id', '')
    ");
    echo "  settings: 10 rows\n"; $total++;
}

// ============================================================
// 2. CONTENT - BLOGS
// ============================================================
if (isEmpty($db, 'blogs')) {
    $db->exec("INSERT INTO blogs (title, slug, content, category_id, status, image, meta_title, meta_description, author_id, views, published_at) VALUES
        ('Top 5 Reasons to Invest in Gorakhpur Real Estate', 'invest-gorakhpur-real-estate', 'Gorakhpur is emerging as a major real estate hub in Eastern UP. With improved connectivity through the Gorakhpur Link Expressway and upcoming airport expansion, property values are set to rise significantly...', 1, 'published', NULL, 'Gorakhpur Real Estate Investment', 'Discover why Gorakhpur is the next big real estate destination in Uttar Pradesh.', 1, 245, NOW() - INTERVAL 10 DAY),
        ('A Guide to Property Registration Process', 'property-registration-guide', 'Buying a property involves several legal steps. From verifying the title deed to stamp duty payment and registration, here is a complete guide to help you navigate the property registration process in UP...', 2, 'published', NULL, 'Property Registration Guide', 'Complete step-by-step guide to property registration in Uttar Pradesh.', 1, 189, NOW() - INTERVAL 7 DAY),
        ('Understanding RERA and Its Benefits', 'rera-benefits-guide', 'The Real Estate Regulatory Authority (RERA) has transformed the Indian real estate market. Learn how RERA protects homebuyers and ensures timely delivery of projects...', 2, 'published', NULL, 'RERA Benefits Guide', 'Everything you need to know about RERA and how it protects homebuyers.', 1, 312, NOW() - INTERVAL 5 DAY),
        ('Tips for First-Time Home Buyers', 'first-time-home-buyer-tips', 'Buying your first home is an exciting milestone. From budgeting to location selection and legal checks, here are essential tips every first-time buyer should know...', 3, 'published', NULL, 'First Time Home Buyer Tips', 'Essential tips and checklist for first-time home buyers in India.', 1, 178, NOW() - INTERVAL 3 DAY),
        ('Colony Living: Why Gated Communities Are Popular', 'gated-community-benefits', 'Gated communities offer security, amenities, and a sense of community. Discover why more homebuyers are choosing colony living over standalone properties...', 3, 'published', NULL, 'Gated Community Benefits', 'Reasons why gated colony communities are becoming India', 1, 156, NOW() - INTERVAL 1 DAY)
    ");
    echo "  blogs: 5 rows\n"; $total++;
}

// ============================================================
// 3. LEGAL - SERVICES & COMPLIANCE
// ============================================================
if (isEmpty($db, 'legal_services')) {
    $db->exec("INSERT INTO legal_services (title, description, icon, price_range, duration, features, status, display_order) VALUES
        ('Property Verification', 'Complete title deed verification and due diligence for property purchases', 'fa-file-contract', 'â‚¹5,000 - â‚¹15,000', '3-5 days', '[\"Title deed verification\",\"Encumbrance check\",\"Property tax clearance\",\"Litigation history\"]', 'active', 1),
        ('Sale Deed Drafting', 'Expert drafting and review of sale deeds and conveyance deeds', 'fa-file-signature', 'â‚¹3,000 - â‚¹10,000', '2-3 days', '[\"Sale deed drafting\",\"Conveyance deed\",\"Gift deed\",\"Exchange deed\"]', 'active', 2),
        ('Stamp Duty & Registration', 'Complete assistance with stamp duty payment and property registration', 'fa-stamp', 'â‚¹2,000 - â‚¹8,000', '1-2 days', '[\"Stamp duty calculation\",\"E-stamp procurement\",\"Registration filing\",\"Documentation\"]', 'active', 3),
        ('RERA Registration', 'Assistance with RERA registration and compliance for builders and buyers', 'fa-building', 'â‚¹10,000 - â‚¹50,000', '7-15 days', '[\"RERA registration\",\"Project registration\",\"Compliance filing\",\"Quarterly returns\"]', 'active', 4),
        ('Dispute Resolution', 'Legal assistance for property disputes, partition suits, and recovery', 'fa-gavel', 'â‚¹15,000 - â‚¹1,00,000', 'Varies', '[\"Notice drafting\",\"Mediation\",\"Litigation support\",\"Court representation\"]', 'active', 5)
    ");
    echo "  legal_services: 5 rows\n"; $total++;
}

if (isEmpty($db, 'compliance_tasks')) {
    $db->exec("INSERT INTO compliance_tasks (title, description, compliance_type, deadline, assigned_to, status) VALUES
        ('Annual GST Return Filing', 'File GSTR-9 for the financial year before the deadline', 'gst', DATE_ADD(NOW(), INTERVAL 45 DAY), 1, 'pending'),
        ('TDS Return Filing', 'Quarterly TDS return filing for employee salaries', 'tax', DATE_ADD(NOW(), INTERVAL 20 DAY), 1, 'pending'),
        ('RERA Quarterly Update', 'Submit quarterly project progress report to RERA authority', 'rera', DATE_ADD(NOW(), INTERVAL 60 DAY), 1, 'pending'),
        ('Income Tax Filing', 'Annual income tax return filing for the company', 'tax', DATE_ADD(NOW(), INTERVAL 90 DAY), 1, 'pending'),
        ('Labor Law Compliance', 'Update and file labor law compliance documents', 'labor', DATE_ADD(NOW(), INTERVAL 30 DAY), 1, 'pending')
    ");
    echo "  compliance_tasks: 5 rows\n"; $total++;
}

// ============================================================
// 4. SALES - PIPELINE
// ============================================================
if (isEmpty($db, 'pipeline_stages')) {
    $db->exec("INSERT INTO pipeline_stages (stage_name, stage_order, color, probability_range_min, probability_range_max) VALUES
        ('New Lead', 1, '#6c757d', 5, 10),
        ('Contacted', 2, '#007bff', 10, 25),
        ('Qualified', 3, '#ffc107', 25, 50),
        ('Proposal Sent', 4, '#17a2b8', 50, 70),
        ('Negotiation', 5, '#fd7e14', 70, 85),
        ('Closed Won', 6, '#28a745', 100, 100),
        ('Closed Lost', 7, '#dc3545', 0, 0)
    ");
    echo "  pipeline_stages: 7 rows\n"; $total++;
}

if (isEmpty($db, 'deal_history')) {
    $db->exec("INSERT INTO deal_history (deal_id, action, old_value, new_value, performed_by, notes, created_at) VALUES
        (1, 'stage_change', 'New Lead', 'Contacted', 1, 'Initial contact made via phone', NOW() - INTERVAL 5 DAY),
        (1, 'note_added', NULL, NULL, 1, 'Customer interested in 3 BHK flat in Suryoday Heights', NOW() - INTERVAL 4 DAY),
        (2, 'stage_change', 'Contacted', 'Qualified', 1, 'Customer visited site and liked the location', NOW() - INTERVAL 3 DAY),
        (3, 'stage_change', 'New Lead', 'Contacted', 1, 'Follow-up call completed', NOW() - INTERVAL 2 DAY),
        (2, 'proposal_sent', NULL, NULL, 1, 'Sent proposal for Plot #A-12 in Raghunath Nagri', NOW() - INTERVAL 1 DAY),
        (3, 'stage_change', 'Contacted', 'Qualified', 1, 'Customer shared budget details', NOW() - INTERVAL 12 HOUR)
    ");
    echo "  deal_history: 6 rows\n"; $total++;
}

// ============================================================
// 5. FINANCE - BANK ACCOUNTS, BUDGETS, INVOICES, EMI
// ============================================================
if (isEmpty($db, 'bank_accounts')) {
    $db->exec("INSERT INTO bank_accounts (account_name, bank_name, account_number, ifsc_code, branch_name, account_type, opening_balance, current_balance, is_primary, status) VALUES
        ('APS Dream Home - Current A/c', 'State Bank of India', '12345678901', 'SBIN0001234', 'Gorakhpur Main', 'current', 5000000.00, 5234500.00, 1, 'active'),
        ('APS Dream Home - Salary A/c', 'HDFC Bank', '9876543210', 'HDFC0005678', 'Gorakhpur', 'current', 1000000.00, 875000.00, 0, 'active'),
        ('Project Escrow - Suryoday Heights', 'ICICI Bank', '4567890123', 'ICIC0009012', 'Gorakhpur', 'current', 25000000.00, 18750000.00, 0, 'active'),
        ('Fixed Deposit - Reserve Fund', 'PNB', 'FD6789012345', 'PUNB0006789', 'Gorakhpur', 'fd', 5000000.00, 5310000.00, 0, 'active')
    ");
    echo "  bank_accounts: 4 rows\n"; $total++;
}

if (isEmpty($db, 'budget_items')) {
    $db->exec("INSERT INTO budget_items (budget_id, account_id, budgeted_amount, actual_amount) VALUES
        (1, 1, 5000000.00, 4230000.00),
        (1, 2, 3000000.00, 3150000.00),
        (1, 3, 2000000.00, 1870000.00),
        (2, 1, 1500000.00, 0.00),
        (2, 4, 2500000.00, 2500000.00)
    ");
    echo "  budget_items: 5 rows\n"; $total++;
}

// Seed suppliers first (required by FK constraint)
if (isEmpty($db, 'suppliers')) {
    $db->exec("INSERT INTO suppliers (supplier_name, contact_person, mobile, email, address, gst_number, pan_number, bank_account, ifsc_code, credit_limit, credit_days, opening_balance, current_balance, total_purchases, total_payments, supplier_type, status) VALUES
        ('Sharma Building Materials', 'Rahul Sharma', '9876543210', 'rahul@sharmabuild.com', 'Gorakhpur, UP', '09ABCDE1234F1Z5', 'ABCDP1234E', '12345678901', 'SBIN0001234', 500000.00, 30, 0.00, 0.00, 450000.00, 531000.00, 'material', 'active'),
        ('Gupta Electricals', 'Amit Gupta', '8765432109', 'info@guptaelectricals.com', 'Gorakhpur, UP', '09FGHIJ5678K2L0', 'EFGHP5678F', '9876543210', 'HDFC0005678', 200000.00, 30, 0.00, 0.00, 125000.00, 100000.00, 'material', 'active'),
        ('Singh Plumbing Solutions', 'Vijay Singh', '7654321098', 'vijay@singhplumbing.com', 'Gorakhpur, UP', '09KLMNO9012P3Q6', 'IJKLP9012G', '4567890123', 'ICIC0009012', 150000.00, 45, 0.00, 0.00, 85000.00, 0.00, 'material', 'active'),
        ('Green Earth Landscaping', 'Sunita Devi', '6543210987', 'info@greenearth.in', 'Gorakhpur, UP', '09PQRST3456U7V8', 'MNOPQ3456H', '6789012345', 'PNB0006789', 300000.00, 60, 0.00, 0.00, 280000.00, 0.00, 'service', 'active')
    ");
    echo "  suppliers: 4 rows\n"; $total++;
}

if (isEmpty($db, 'purchase_invoices')) {
    $db->exec("INSERT INTO purchase_invoices (invoice_number, supplier_invoice_number, supplier_id, invoice_date, due_date, subtotal, tax_amount, total_amount, paid_amount, balance_amount, status, notes) VALUES
        ('PINV-2026-001', 'SUP-1001', 1, '2026-05-01', '2026-05-31', 450000.00, 81000.00, 531000.00, 531000.00, 0.00, 'paid', 'Construction materials - Steel and Cement'),
        ('PINV-2026-002', 'SUP-1002', 2, '2026-05-10', '2026-06-10', 125000.00, 22500.00, 147500.00, 100000.00, 47500.00, 'partial', 'Electrical fittings and wiring'),
        ('PINV-2026-003', 'SUP-1003', 3, '2026-05-15', '2026-06-15', 85000.00, 15300.00, 100300.00, 0.00, 100300.00, 'received', 'Plumbing supplies for Phase 2'),
        ('PINV-2026-004', 'SUP-1024', 4, '2026-05-20', '2026-07-20', 280000.00, 50400.00, 330400.00, 0.00, 330400.00, 'draft', 'Landscaping materials')
    ");
    echo "  purchase_invoices: 4 rows\n"; $total++;
}

if (isEmpty($db, 'invoice_items')) {
    $db->exec("INSERT INTO invoice_items (invoice_id, item_type, item_name, item_description, quantity, unit_price, line_total) VALUES
        (1, 'service', 'Consultation Fee', 'Property consultation for Plot B-7', 1, 25000.00, 25000.00),
        (1, 'service', 'Registration Service', 'Complete registration assistance', 1, 15000.00, 15000.00),
        (2, 'product', 'Legal Document Set', 'Sale deed, title deed copies', 2, 5000.00, 10000.00),
        (3, 'service', 'Site Visit Coordination', 'Coordinated 3 site visits', 1, 7500.00, 7500.00)
    ");
    echo "  invoice_items: 4 rows\n"; $total++;
}

if (isEmpty($db, 'emi_plans')) {
    $db->exec("INSERT INTO emi_plans (property_id, customer_id, total_amount, interest_rate, tenure_months, emi_amount, down_payment, start_date, end_date, status) VALUES
        (1, 1, 4500000.00, 8.50, 120, 55800.00, 900000.00, '2026-01-15', '2036-01-15', 'active'),
        (2, 1, 3200000.00, 9.00, 84, 51000.00, 640000.00, '2026-03-01', '2033-03-01', 'active'),
        (3, 2, 2800000.00, 8.75, 60, 57800.00, 560000.00, '2026-04-10', '2031-04-10', 'active')
    ");
    echo "  emi_plans: 3 rows\n"; $total++;
}

if (isEmpty($db, 'installments')) {
    $db->exec("INSERT INTO installments (property_allocation_id, installment_number, amount, due_date, paid_amount, status, paid_date) VALUES
        (1, 1, 450000.00, '2026-02-01', 450000.00, 'paid', '2026-01-28 10:00:00'),
        (1, 2, 450000.00, '2026-03-01', 450000.00, 'paid', '2026-02-25 11:30:00'),
        (1, 3, 450000.00, '2026-04-01', 450000.00, 'paid', '2026-03-30 09:15:00'),
        (1, 4, 450000.00, '2026-05-01', 300000.00, 'partial', NULL),
        (2, 1, 350000.00, '2026-04-01', 350000.00, 'paid', '2026-03-28 14:00:00'),
        (2, 2, 350000.00, '2026-05-01', 0.00, 'pending', NULL)
    ");
    echo "  installments: 6 rows\n"; $total++;
}

// ============================================================
// 6. HRM - JOBS, SHIFTS
// ============================================================
if (isEmpty($db, 'jobs')) {
    $db->exec("INSERT INTO jobs (title, slug, description, requirements, location, job_type, salary_range, experience, status, expires_at) VALUES
        ('Real Estate Sales Executive', 'sales-executive', 'We are looking for experienced sales executives to join our team in Gorakhpur. You will be responsible for lead generation, client meetings, and property sales.', '[\"2+ years in real estate sales\",\"Excellent communication skills\",\"Local area knowledge\",\"Own vehicle preferred\"]', 'Gorakhpur', 'Full-time', 'â‚¹25,000 - â‚¹50,000 + Incentives', '2-5 years', 'active', DATE_ADD(NOW(), INTERVAL 30 DAY)),
        ('Property Consultant', 'property-consultant', 'Guide clients through the property buying process. Provide expert advice on property selection, documentation, and investment.', '[\"1+ year in real estate\",\"Strong negotiation skills\",\"Knowledge of property laws\",\"Computer proficiency\"]', 'Gorakhpur', 'Full-time', 'â‚¹20,000 - â‚¹40,000 + Commission', '1-3 years', 'active', DATE_ADD(NOW(), INTERVAL 30 DAY)),
        ('Legal Advisor', 'legal-advisor', 'Provide legal guidance on property transactions, contract drafting, and compliance matters.', '[\"LLB degree\",\"3+ years property law experience\",\"RERA knowledge\",\"Excellent drafting skills\"]', 'Gorakhpur', 'Full-time', 'â‚¹40,000 - â‚¹80,000', '3-7 years', 'active', DATE_ADD(NOW(), INTERVAL 45 DAY)),
        ('Marketing Manager', 'marketing-manager', 'Lead our marketing efforts including digital campaigns, social media, and offline promotions for our properties.', '[\"MBA Marketing preferred\",\"4+ years marketing experience\",\"Real estate background preferred\",\"Digital marketing expertise\"]', 'Gorakhpur', 'Full-time', 'â‚¹50,000 - â‚¹90,000', '4-8 years', 'active', DATE_ADD(NOW(), INTERVAL 30 DAY)),
        ('Telecaller', 'telecaller', 'Handle inbound and outbound calls for property inquiries. Convert leads into site visits and sales.', '[\"Fluent in Hindi & English\",\"6+ months telecalling experience\",\"Good communication skills\",\"Basic computer knowledge\"]', 'Gorakhpur', 'Full-time', 'â‚¹12,000 - â‚¹18,000 + Incentives', '0-2 years', 'active', DATE_ADD(NOW(), INTERVAL 20 DAY))
    ");
    echo "  jobs: 5 rows\n"; $total++;
}

if (isEmpty($db, 'job_applications')) {
    $db->exec("INSERT INTO job_applications (name, phone, email, message, file_path) VALUES
        ('Rahul Sharma', '9876543210', 'rahul@email.com', 'I have 3 years of experience in real estate sales at a leading brokerage firm.', NULL),
        ('Priya Singh', '8765432109', 'priya@email.com', 'MBA graduate with 2 years of marketing experience in real estate.', NULL),
        ('Amit Verma', '7654321098', 'amit@email.com', 'LLB with 5 years of experience in property law and documentation.', NULL)
    ");
    echo "  job_applications: 3 rows\n"; $total++;
}

if (isEmpty($db, 'shift_types')) {
    $db->exec("INSERT INTO shift_types (name, label, description, color_code, is_active) VALUES
        ('morning', 'Morning Shift', '9 AM to 6 PM - Standard office hours', '#007bff', 1),
        ('evening', 'Evening Shift', '2 PM to 11 PM - For telecalling team', '#fd7e14', 1),
        ('weekend', 'Weekend Shift', '10 AM to 4 PM - Weekend site visit support', '#28a745', 1)
    ");
    echo "  shift_types: 3 rows\n"; $total++;
}

if (isEmpty($db, 'departments')) {
    $db->exec("INSERT INTO departments (name, description, color_code, is_active) VALUES
        ('Sales', 'Sales and Business Development', '#007bff', 1),
        ('Marketing', 'Marketing and Promotions', '#fd7e14', 1),
        ('Operations', 'Operations and Site Management', '#28a745', 1),
        ('Legal', 'Legal and Compliance', '#dc3545', 1),
        ('Finance', 'Finance and Accounts', '#17a2b8', 1),
        ('HR', 'Human Resources', '#6f42c1', 1),
        ('IT', 'Information Technology', '#20c997', 1)
    ");
    echo "  departments: 7 rows\n"; $total++;
}

if (isEmpty($db, 'shift_schedules')) {
    $db->exec("INSERT INTO shift_schedules (name, description, shift_type_id, department_id, days_of_week, start_date, end_date, is_active, created_by) VALUES
        ('Morning Shift', '9 AM to 6 PM - Standard office hours', 1, 1, '[\"1\",\"2\",\"3\",\"4\",\"5\"]', '2026-05-01', '2026-12-31', 1, 1),
        ('Evening Shift', '2 PM to 11 PM - For telecalling team', 2, 2, '[\"1\",\"2\",\"3\",\"4\",\"5\"]', '2026-05-01', '2026-12-31', 1, 1),
        ('Weekend Shift', '10 AM to 4 PM - Weekend site visit support', 3, 1, '[\"6\",\"0\"]', '2026-05-01', '2026-12-31', 1, 1)
    ");
    echo "  shift_schedules: 3 rows\n"; $total++;
}

// ============================================================
// 7. CRM - REVIEWS, FAVORITES, TICKET REPLIES, SERVICES
// ============================================================
if (isEmpty($db, 'agent_reviews')) {
    $db->exec("INSERT INTO agent_reviews (agent_id, user_id, rating, review_text, property_id, verified, helpful_count) VALUES
        (2, 1, 4.5, 'Excellent service! Helped us find the perfect plot and guided through the entire registration process.', 1, 1, 12),
        (2, 1, 5.0, 'Very professional and knowledgeable. Made the buying process smooth and hassle-free.', 2, 1, 8),
        (3, 2, 4.0, 'Good experience overall. Responded quickly to queries and arranged site visits promptly.', 3, 1, 5),
        (2, 3, 3.5, 'Decent service but could improve on follow-up communication.', 1, 0, 2)
    ");
    echo "  agent_reviews: 4 rows\n"; $total++;
}

if (isEmpty($db, 'customer_favorites')) {
    $db->exec("INSERT INTO customer_favorites (customer_id, property_id) VALUES
        (1, 1), (1, 3), (2, 2), (2, 4), (3, 1), (3, 5)
    ");
    echo "  customer_favorites: 6 rows\n"; $total++;
}

if (isEmpty($db, 'service_interests')) {
    $db->exec("INSERT INTO service_interests (lead_id, service_type, customer_name, customer_phone, customer_email, status, notes) VALUES
        (1, 'home_loan', 'Rajesh Kumar', '9988776655', 'rajesh@email.com', 'in_progress', 'Looking for home loan for Plot in Suryoday Heights'),
        (2, 'legal', 'Sunita Devi', '8877665544', 'sunita@email.com', 'contacted', 'Need legal verification for property documents'),
        (3, 'registry', 'Vijay Singh', '7766554433', 'vijay@email.com', 'pending', 'Registration assistance for Plot B-7, Raghunath Nagri'),
        (4, 'interior', 'Anita Gupta', '6655443322', 'anita@email.com', 'completed', 'Interior design completed for 3BHK flat'),
        (5, 'property_tax', 'Deepak Verma', '5544332211', 'deepak@email.com', 'pending', 'Property tax payment assistance')
    ");
    echo "  service_interests: 5 rows\n"; $total++;
}

if (isEmpty($db, 'ticket_replies')) {
    $db->exec("INSERT INTO ticket_replies (ticket_id, user_id, message) VALUES
        (1, 5, 'I have attached the documents for verification. Please check and confirm.'),
        (1, 1, 'Thank you. We have received your documents and will verify them within 2 working days.'),
        (1, 5, 'Any update on my document verification?'),
        (2, 8, 'I want to know the status of my plot booking.'),
        (2, 1, 'Your booking is confirmed. The allotment letter will be issued by Friday.')
    ");
    echo "  ticket_replies: 5 rows\n"; $total++;
}

// ============================================================
// 8. MLM - REWARDS, WITHDRAWALS, RANK ACHIEVEMENTS
// ============================================================
if (isEmpty($db, 'reward_history')) {
    $db->exec("INSERT INTO reward_history (associate_id, reward_type, reward_value, reward_date, description) VALUES
        (77, 'direct_bonus', 25000.00, '2026-05-01', 'Direct referral bonus for new customer A. Kumar'),
        (77, 'team_bonus', 12500.00, '2026-05-05', 'Team performance bonus - Silver level'),
        (77, 'matching_bonus', 8000.00, '2026-05-10', 'Matching bonus from downline team'),
        (78, 'direct_bonus', 15000.00, '2026-05-03', 'Direct referral bonus for customer S. Singh'),
        (79, 'rank_bonus', 35000.00, '2026-04-20', 'Rank achievement bonus - Gold level')
    ");
    echo "  reward_history: 5 rows\n"; $total++;
}

if (isEmpty($db, 'withdrawal_requests')) {
    $db->exec("INSERT INTO withdrawal_requests (user_id, bank_account_id, amount, tax_amount, net_amount, status, utr_number) VALUES
        (77, 1, 50000.00, 5000.00, 45000.00, 'completed', 'UTR20260501001'),
        (77, 1, 25000.00, 2500.00, 22500.00, 'processing', NULL),
        (78, 2, 15000.00, 1500.00, 13500.00, 'pending', NULL),
        (79, 1, 75000.00, 7500.00, 67500.00, 'approved', NULL)
    ");
    echo "  withdrawal_requests: 4 rows\n"; $total++;
}

if (isEmpty($db, 'rank_achievements')) {
    $db->exec("INSERT INTO rank_achievements (user_id, user_type, rank_name, rank_level, requirements_met, reward_points, is_current_rank, valid_from) VALUES
        (77, 'associate', 'Silver Associate', 2, '[\"direct_team_5\",\"total_sales_500k\",\"active_months_3\"]', 500, 1, '2026-01-01'),
        (77, 'associate', 'Bronze Associate', 1, '[\"direct_team_2\",\"total_sales_100k\"]', 200, 0, '2025-06-01'),
        (78, 'associate', 'Bronze Associate', 1, '[\"direct_team_2\",\"total_sales_100k\"]', 200, 1, '2026-03-01'),
        (79, 'associate', 'Gold Associate', 3, '[\"direct_team_10\",\"total_sales_1M\",\"active_months_6\",\"silver_mentor\"]', 1000, 1, '2026-04-01')
    ");
    echo "  rank_achievements: 4 rows\n"; $total++;
}

// ============================================================
// 9. PROPERTIES - PRICE HISTORY & RERA
// ============================================================
if (isEmpty($db, 'price_history')) {
    $db->exec("INSERT INTO price_history (plot_id, colony_id, old_price, new_price, old_price_per_sqft, new_price_per_sqft, change_type, reason, changed_by) VALUES
        (1, 2, 1500000.00, 1650000.00, 1500.00, 1650.00, 'base', 'Phase 2 price revision', 1),
        (2, 2, 1800000.00, 1980000.00, 1500.00, 1650.00, 'base', 'Phase 2 price revision', 1),
        (3, 2, 2200000.00, 2420000.00, 1500.00, 1650.00, 'base', 'Phase 2 price revision', 1),
        (10, 3, 1200000.00, 1320000.00, 1200.00, 1320.00, 'base', 'Annual price revision', 1),
        (15, 4, 950000.00, 1050000.00, 950.00, 1050.00, 'bulk_update', 'Bulk price update', 1),
        (5, 2, 1650000.00, 1600000.00, 1650.00, 1600.00, 'negotiated', 'Customer negotiation discount', 1)
    ");
    echo "  price_history: 6 rows\n"; $total++;
}

if (isEmpty($db, 'rera_requests')) {
    $db->exec("INSERT INTO rera_requests (user_id, booking_id, deducted_amount, status, rera_number, notes) VALUES
        (5, 1, 25000.00, 'approved', 'RERA-UP-2026-00123', 'RERA refund processed for cancelled booking'),
        (8, 2, 50000.00, 'pending', NULL, 'RERA deduction of 10% of booking amount'),
        (12, 3, 0.00, 'in_process', NULL, 'Documentation in progress')
    ");
    echo "  rera_requests: 3 rows\n"; $total++;
}

// Re-enable FK checks
$db->exec("SET FOREIGN_KEY_CHECKS = 1");

// ============================================================
// SUMMARY
// ============================================================
echo "\n=== SEEDING COMPLETE ===\n";
echo "Total rows inserted: {$total}\n";?>