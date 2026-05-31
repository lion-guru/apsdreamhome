<?php
/**
 * Create ERP Feature Database Tables
 * Creates tables for Invoice, Property Allocation, and Deal Pipeline features
 */

$host = '127.0.0.1';
$port = 3307;
$dbname = 'apsdreamhome';
$username = 'root';
$password = '';

try {
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
    $conn = new PDO($dsn, $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "🔍 Creating ERP Feature Tables...\n\n";
    
    // 1. Invoices table
    $conn->query("CREATE TABLE IF NOT EXISTS invoices (
        id INT AUTO_INCREMENT PRIMARY KEY,
        invoice_number VARCHAR(50) UNIQUE NOT NULL,
        customer_id INT,
        property_id INT,
        amount DECIMAL(12, 2) NOT NULL DEFAULT 0,
        due_date DATE,
        description TEXT,
        installment_number INT DEFAULT 1,
        total_installments INT DEFAULT 1,
        status ENUM('draft', 'sent', 'partial', 'paid', 'overdue', 'cancelled') DEFAULT 'draft',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_customer (customer_id),
        INDEX idx_property (property_id),
        INDEX idx_status (status),
        INDEX idx_due_date (due_date),
        INDEX idx_invoice_number (invoice_number)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "✅ invoices table created\n";
    
    // 2. Payments table
    $conn->query("CREATE TABLE IF NOT EXISTS payments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        invoice_id INT,
        property_allocation_id INT,
        customer_id INT,
        amount DECIMAL(12, 2) NOT NULL DEFAULT 0,
        payment_date DATE,
        payment_method ENUM('cash', 'bank_transfer', 'upi', 'card', 'cheque') DEFAULT 'cash',
        transaction_id VARCHAR(100),
        status ENUM('pending', 'completed', 'failed', 'refunded') DEFAULT 'pending',
        notes TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_invoice (invoice_id),
        INDEX idx_allocation (property_allocation_id),
        INDEX idx_customer (customer_id),
        INDEX idx_status (status),
        INDEX idx_payment_date (payment_date)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "✅ payments table created\n";
    
    // 3. Property Allocations table
    $conn->query("CREATE TABLE IF NOT EXISTS property_allocations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        allocation_number VARCHAR(50) UNIQUE NOT NULL,
        customer_id INT NOT NULL,
        property_id INT NOT NULL,
        booking_amount DECIMAL(12, 2) DEFAULT 0,
        total_price DECIMAL(12, 2) NOT NULL DEFAULT 0,
        installment_plan ENUM('full_payment', 'emi', 'custom') DEFAULT 'full_payment',
        installment_months INT DEFAULT 0,
        per_installment_amount DECIMAL(12, 2) DEFAULT 0,
        first_installment_date DATE,
        notes TEXT,
        status ENUM('pending', 'confirmed', 'cancelled', 'transferred') DEFAULT 'pending',
        confirmed_at TIMESTAMP NULL,
        cancelled_at TIMESTAMP NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_customer (customer_id),
        INDEX idx_property (property_id),
        INDEX idx_status (status),
        INDEX idx_allocation_number (allocation_number)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "✅ property_allocations table created\n";
    
    // 4. Deals table (for Deal Pipeline)
    $conn->query("CREATE TABLE IF NOT EXISTS deals (
        id INT AUTO_INCREMENT PRIMARY KEY,
        deal_number VARCHAR(50) UNIQUE NOT NULL,
        customer_id INT NOT NULL,
        property_id INT,
        assigned_to INT,
        deal_value DECIMAL(12, 2) DEFAULT 0,
        expected_close_date DATE,
        probability INT DEFAULT 50,
        priority ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium',
        stage ENUM('lead', 'qualified', 'site_visit', 'negotiation', 'booking', 'agreement', 'closed_won', 'closed_lost') DEFAULT 'lead',
        source ENUM('manual', 'website', 'referral', 'advertising', 'social_media', 'email', 'phone') DEFAULT 'manual',
        notes TEXT,
        status ENUM('active', 'completed', 'paused', 'archived') DEFAULT 'active',
        closed_at TIMESTAMP NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_customer (customer_id),
        INDEX idx_property (property_id),
        INDEX idx_assigned_to (assigned_to),
        INDEX idx_stage (stage),
        INDEX idx_status (status),
        INDEX idx_priority (priority),
        INDEX idx_expected_close (expected_close_date),
        INDEX idx_deal_number (deal_number)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "✅ deals table created\n";
    
    // 5. Deal History table (for timeline tracking)
    $conn->query("CREATE TABLE IF NOT EXISTS deal_history (
        id INT AUTO_INCREMENT PRIMARY KEY,
        deal_id INT NOT NULL,
        action VARCHAR(50),
        old_value TEXT,
        new_value TEXT,
        performed_by INT,
        notes TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_deal (deal_id),
        INDEX idx_action (action),
        INDEX idx_created_at (created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "✅ deal_history table created\n";
    
    // 6. Installments table (for tracking installment payments)
    $conn->query("CREATE TABLE IF NOT EXISTS installments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        property_allocation_id INT NOT NULL,
        installment_number INT NOT NULL,
        amount DECIMAL(12, 2) NOT NULL DEFAULT 0,
        due_date DATE NOT NULL,
        paid_amount DECIMAL(12, 2) DEFAULT 0,
        status ENUM('pending', 'partial', 'paid', 'overdue', 'waived') DEFAULT 'pending',
        paid_date TIMESTAMP NULL,
        payment_id INT,
        notes TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_allocation (property_allocation_id),
        INDEX idx_installment_number (installment_number),
        INDEX idx_due_date (due_date),
        INDEX idx_status (status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "✅ installments table created\n";
    
    echo "\n🎉 All ERP Feature Tables Created Successfully!\n";
    echo "\n📋 Tables Created:\n";
    echo "1. invoices - Invoice management\n";
    echo "2. payments - Payment tracking\n";
    echo "3. property_allocations - Plot allocation system\n";
    echo "4. deals - Deal pipeline management\n";
    echo "5. deal_history - Deal timeline tracking\n";
    echo "6. installments - Installment payment tracking\n";
    
} catch (PDOException $e) {
    echo "❌ Database Error: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
