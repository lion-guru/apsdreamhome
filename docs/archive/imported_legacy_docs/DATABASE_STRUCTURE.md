# APS Dream Home - Database Structure

## Overview

This document provides a comprehensive overview of the database structure for the APS Dream Home property management system. The database is designed to support property management, lead tracking, visit scheduling, and notification systems.

## Core Tables

### Users and Authentication

#### `users`
```sql
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    role ENUM('admin', 'agent', 'customer') NOT NULL DEFAULT 'customer',
    status ENUM('active', 'inactive', 'pending') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    INDEX idx_email (email),
    INDEX idx_role (role),
    INDEX idx_status (status)
);
```

#### `admin`
```sql
CREATE TABLE admin (
  aid int(10) NOT NULL AUTO_INCREMENT,
  auser varchar(50) NOT NULL,
  aemail varchar(50) NOT NULL,
  apass varchar(255) NOT NULL,
  adob date NOT NULL,
  aphone varchar(15) NOT NULL,
  PRIMARY KEY (aid),
  UNIQUE KEY aemail (aemail),
  KEY idx_email (aemail),
  KEY idx_admin_user (auser)
);
```

### Property Management

#### `properties`
```sql
CREATE TABLE IF NOT EXISTS properties (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    address TEXT NOT NULL,
    price DECIMAL(12,2) NOT NULL,
    bedrooms INT,
    bathrooms INT,
    area DECIMAL(10,2),
    type ENUM('house', 'apartment', 'villa', 'land', 'commercial') NOT NULL,
    status ENUM('available', 'sold', 'rented', 'under_contract', 'off_market') NOT NULL DEFAULT 'available',
    owner_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_status (status),
    INDEX idx_type (type),
    INDEX idx_price (price)
);
```

#### `property_types`
```sql
CREATE TABLE IF NOT EXISTS property_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type VARCHAR(100) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT current_timestamp()
);
```

#### `property_features`
```sql
CREATE TABLE IF NOT EXISTS property_features (
    id INT PRIMARY KEY AUTO_INCREMENT,
    property_id INT NOT NULL,
    feature_name VARCHAR(100) NOT NULL,
    feature_value VARCHAR(255),
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE,
    INDEX idx_property_id (property_id)
);
```

#### `property_images`
```sql
CREATE TABLE IF NOT EXISTS property_images (
    id INT PRIMARY KEY AUTO_INCREMENT,
    property_id INT NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    is_primary BOOLEAN DEFAULT FALSE,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE,
    INDEX idx_property_id (property_id)
);
```

### Customer Management

#### `customers`
```sql
CREATE TABLE IF NOT EXISTS customers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_email (email),
    INDEX idx_email (email),
    INDEX idx_phone (phone)
);
```

### Lead Management

#### `leads`
```sql
CREATE TABLE IF NOT EXISTS leads (
    id INT PRIMARY KEY AUTO_INCREMENT,
    customer_id INT NOT NULL,
    property_id INT NOT NULL,
    source ENUM('website', 'visit_schedule', 'referral', 'direct', 'other') NOT NULL,
    status ENUM('new', 'contacted', 'qualified', 'proposal', 'negotiation', 'closed_won', 'closed_lost') NOT NULL DEFAULT 'new',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE,
    INDEX idx_status (status),
    INDEX idx_source (source)
);
```

#### `lead_status_history`
```sql
CREATE TABLE IF NOT EXISTS lead_status_history (
    id INT PRIMARY KEY AUTO_INCREMENT,
    lead_id INT NOT NULL,
    old_status VARCHAR(50),
    new_status VARCHAR(50) NOT NULL,
    changed_by INT NOT NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (lead_id) REFERENCES leads(id) ON DELETE CASCADE,
    FOREIGN KEY (changed_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_lead_id (lead_id)
);
```

#### `lead_notes`
```sql
CREATE TABLE IF NOT EXISTS lead_notes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    lead_id INT NOT NULL,
    user_id INT NOT NULL,
    note TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (lead_id) REFERENCES leads(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_lead_id (lead_id)
);
```

### Visit Management

#### `property_visits`
```sql
CREATE TABLE IF NOT EXISTS property_visits (
    id INT PRIMARY KEY AUTO_INCREMENT,
    customer_id INT NOT NULL,
    property_id INT NOT NULL,
    lead_id INT,
    visit_date DATE NOT NULL,
    visit_time TIME NOT NULL,
    notes TEXT,
    status ENUM('scheduled', 'completed', 'cancelled', 'rescheduled', 'no_show') DEFAULT 'scheduled',
    feedback TEXT,
    rating INT CHECK (rating >= 1 AND rating <= 5),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE,
    FOREIGN KEY (lead_id) REFERENCES leads(id) ON DELETE SET NULL,
    INDEX idx_visit_datetime (visit_date, visit_time),
    INDEX idx_status (status)
);
```

#### `visit_reminders`
```sql
CREATE TABLE IF NOT EXISTS visit_reminders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    visit_id INT NOT NULL,
    reminder_type ENUM('24h_before', '1h_before', 'feedback_request') NOT NULL,
    status ENUM('pending', 'sent', 'failed') DEFAULT 'pending',
    scheduled_at TIMESTAMP NOT NULL,
    sent_at TIMESTAMP NULL,
    error_message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (visit_id) REFERENCES property_visits(id) ON DELETE CASCADE,
    INDEX idx_reminder_status (status, scheduled_at)
);
```

#### `visit_availability`
```sql
CREATE TABLE IF NOT EXISTS visit_availability (
    id INT PRIMARY KEY AUTO_INCREMENT,
    property_id INT NOT NULL,
    day_of_week TINYINT NOT NULL CHECK (day_of_week BETWEEN 0 AND 6),
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    max_visits_per_slot INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE,
    UNIQUE KEY unique_availability (property_id, day_of_week, start_time)
);
```

### Notification System

#### `notifications`
```sql
CREATE TABLE IF NOT EXISTS notifications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    type VARCHAR(50) NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    link VARCHAR(255),
    status ENUM('unread', 'read', 'archived') DEFAULT 'unread',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    read_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

#### `notification_settings`
```sql
CREATE TABLE IF NOT EXISTS notification_settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    type VARCHAR(50) NOT NULL,
    email_enabled BOOLEAN DEFAULT TRUE,
    push_enabled BOOLEAN DEFAULT TRUE,
    sms_enabled BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_type (user_id, type)
);
```

#### `notification_templates`
```sql
CREATE TABLE IF NOT EXISTS notification_templates (
    id INT PRIMARY KEY AUTO_INCREMENT,
    type VARCHAR(50) NOT NULL,
    title_template TEXT NOT NULL,
    message_template TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_type (type)
);
```

#### `notification_logs`
```sql
CREATE TABLE IF NOT EXISTS notification_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    status VARCHAR(50) NOT NULL,
    error_message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES notifications(id) ON DELETE CASCADE
);
```

## Accounting System

### `payments`
```sql
CREATE TABLE IF NOT EXISTS payments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    transaction_id VARCHAR(50) UNIQUE,
    booking_id INT,
    customer_id INT NOT NULL,
    property_id INT,
    amount DECIMAL(12,2) NOT NULL,
    payment_type ENUM('booking', 'installment', 'full_payment', 'commission', 'other') NOT NULL,
    payment_method ENUM('cash', 'check', 'bank_transfer', 'credit_card', 'upi', 'other') NOT NULL,
    payment_status ENUM('pending', 'completed', 'failed', 'refunded') NOT NULL DEFAULT 'pending',
    reference_number VARCHAR(100),
    payment_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    notes TEXT,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE SET NULL,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_transaction_id (transaction_id),
    INDEX idx_payment_date (payment_date),
    INDEX idx_payment_status (payment_status)
);
```

### `invoices`
```sql
CREATE TABLE IF NOT EXISTS invoices (
    id INT PRIMARY KEY AUTO_INCREMENT,
    invoice_number VARCHAR(20) UNIQUE NOT NULL,
    customer_id INT NOT NULL,
    property_id INT,
    booking_id INT,
    total_amount DECIMAL(12,2) NOT NULL,
    tax_amount DECIMAL(12,2) DEFAULT 0.00,
    discount_amount DECIMAL(12,2) DEFAULT 0.00,
    final_amount DECIMAL(12,2) NOT NULL,
    paid_amount DECIMAL(12,2) DEFAULT 0.00,
    due_amount DECIMAL(12,2) NOT NULL,
    invoice_date DATE NOT NULL,
    due_date DATE NOT NULL,
    status ENUM('draft', 'sent', 'paid', 'partially_paid', 'overdue', 'cancelled') NOT NULL DEFAULT 'draft',
    notes TEXT,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE SET NULL,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_invoice_number (invoice_number),
    INDEX idx_invoice_date (invoice_date),
    INDEX idx_due_date (due_date),
    INDEX idx_status (status)
);
```

### `invoice_items`
```sql
CREATE TABLE IF NOT EXISTS invoice_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    invoice_id INT NOT NULL,
    description VARCHAR(255) NOT NULL,
    quantity DECIMAL(10,2) NOT NULL DEFAULT 1.00,
    unit_price DECIMAL(12,2) NOT NULL,
    tax_percentage DECIMAL(5,2) DEFAULT 0.00,
    discount_percentage DECIMAL(5,2) DEFAULT 0.00,
    total_amount DECIMAL(12,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE,
    INDEX idx_invoice_id (invoice_id)
);
```

### `expenses`
```sql
CREATE TABLE IF NOT EXISTS expenses (
    id INT PRIMARY KEY AUTO_INCREMENT,
    expense_category_id INT NOT NULL,
    property_id INT,
    amount DECIMAL(12,2) NOT NULL,
    expense_date DATE NOT NULL,
    description TEXT NOT NULL,
    payment_method ENUM('cash', 'check', 'bank_transfer', 'credit_card', 'upi', 'other') NOT NULL,
    reference_number VARCHAR(100),
    receipt_image VARCHAR(255),
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (expense_category_id) REFERENCES expense_categories(id) ON DELETE RESTRICT,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_expense_date (expense_date),
    INDEX idx_expense_category (expense_category_id)
);
```

### `expense_categories`
```sql
CREATE TABLE IF NOT EXISTS expense_categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_name (name)
);
```

### `financial_reports`
```sql
CREATE TABLE IF NOT EXISTS financial_reports (
    id INT PRIMARY KEY AUTO_INCREMENT,
    report_type ENUM('income', 'expense', 'profit_loss', 'tax', 'commission', 'custom') NOT NULL,
    title VARCHAR(255) NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    total_income DECIMAL(15,2) DEFAULT 0.00,
    total_expense DECIMAL(15,2) DEFAULT 0.00,
    net_profit DECIMAL(15,2) DEFAULT 0.00,
    report_data LONGTEXT,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_report_type (report_type),
    INDEX idx_date_range (start_date, end_date)
);
```

## Land and Kissan (Farmer) Management

### `site_master`
```sql
CREATE TABLE IF NOT EXISTS site_master (
    site_id INT PRIMARY KEY AUTO_INCREMENT,
    site_name VARCHAR(200) NOT NULL,
    site_location VARCHAR(255) NOT NULL,
    total_area DECIMAL(12,2) NOT NULL,
    available_area DECIMAL(12,2) NOT NULL,
    site_status ENUM('active', 'inactive', 'completed', 'planning') NOT NULL DEFAULT 'active',
    acquisition_date DATE,
    development_start_date DATE,
    expected_completion_date DATE,
    actual_completion_date DATE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_site_name (site_name),
    INDEX idx_site_status (site_status)
);
```

### `gata_master`
```sql
CREATE TABLE IF NOT EXISTS gata_master (
    gata_id INT PRIMARY KEY AUTO_INCREMENT,
    site_id INT NOT NULL,
    gata_no VARCHAR(50) NOT NULL,
    khasra_no VARCHAR(50),
    area DECIMAL(12,2) NOT NULL,
    available_area DECIMAL(12,2) NOT NULL,
    land_type ENUM('agricultural', 'residential', 'commercial', 'industrial', 'mixed') NOT NULL,
    land_status ENUM('available', 'reserved', 'sold', 'under_development') NOT NULL DEFAULT 'available',
    purchase_rate DECIMAL(12,2),
    current_rate DECIMAL(12,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (site_id) REFERENCES site_master(site_id) ON DELETE CASCADE,
    INDEX idx_gata_no (gata_no),
    INDEX idx_land_status (land_status),
    UNIQUE KEY unique_site_gata (site_id, gata_no)
);
```

### `kissan_master`
```sql
CREATE TABLE IF NOT EXISTS kissan_master (
    kissan_id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(200) NOT NULL,
    father_name VARCHAR(200),
    adhaar_number VARCHAR(12),
    pan_number VARCHAR(10),
    mobile_number VARCHAR(15) NOT NULL,
    alternate_mobile VARCHAR(15),
    address TEXT NOT NULL,
    village VARCHAR(100),
    tehsil VARCHAR(100),
    district VARCHAR(100),
    state VARCHAR(100),
    pincode VARCHAR(10),
    bank_name VARCHAR(100),
    account_number VARCHAR(50),
    ifsc_code VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_kissan_name (name),
    INDEX idx_adhaar (adhaar_number),
    INDEX idx_mobile (mobile_number)
);
```

### `kissan_land_mapping`
```sql
CREATE TABLE IF NOT EXISTS kissan_land_mapping (
    id INT PRIMARY KEY AUTO_INCREMENT,
    kissan_id INT NOT NULL,
    gata_id INT NOT NULL,
    ownership_percentage DECIMAL(5,2) NOT NULL DEFAULT 100.00,
    ownership_area DECIMAL(12,2) NOT NULL,
    purchase_date DATE,
    purchase_rate DECIMAL(12,2),
    purchase_amount DECIMAL(15,2),
    registry_number VARCHAR(100),
    registry_date DATE,
    agreement_status ENUM('pending', 'completed', 'cancelled') NOT NULL DEFAULT 'pending',
    payment_status ENUM('pending', 'partial', 'completed') NOT NULL DEFAULT 'pending',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (kissan_id) REFERENCES kissan_master(kissan_id) ON DELETE CASCADE,
    FOREIGN KEY (gata_id) REFERENCES gata_master(gata_id) ON DELETE CASCADE,
    INDEX idx_purchase_date (purchase_date),
    INDEX idx_agreement_status (agreement_status),
    INDEX idx_payment_status (payment_status),
    UNIQUE KEY unique_kissan_gata (kissan_id, gata_id)
);
```

### `kissan_payments`
```sql
CREATE TABLE IF NOT EXISTS kissan_payments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    mapping_id INT NOT NULL,
    kissan_id INT NOT NULL,
    amount DECIMAL(12,2) NOT NULL,
    payment_date DATE NOT NULL,
    payment_mode ENUM('cash', 'check', 'bank_transfer', 'rtgs', 'neft', 'upi') NOT NULL,
    transaction_id VARCHAR(100),
    bank_name VARCHAR(100),
    check_number VARCHAR(50),
    check_date DATE,
    status ENUM('pending', 'completed', 'bounced', 'cancelled') NOT NULL DEFAULT 'completed',
    remarks TEXT,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (mapping_id) REFERENCES kissan_land_mapping(id) ON DELETE CASCADE,
    FOREIGN KEY (kissan_id) REFERENCES kissan_master(kissan_id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_payment_date (payment_date),
    INDEX idx_status (status)
);
```

### `land_development`
```sql
CREATE TABLE IF NOT EXISTS land_development (
    id INT PRIMARY KEY AUTO_INCREMENT,
    site_id INT NOT NULL,
    development_type ENUM('boundary_wall', 'road', 'electricity', 'water', 'landscaping', 'other') NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE,
    status ENUM('planned', 'in_progress', 'completed', 'on_hold') NOT NULL DEFAULT 'planned',
    estimated_cost DECIMAL(15,2) NOT NULL,
    actual_cost DECIMAL(15,2),
    contractor_name VARCHAR(200),
    contractor_contact VARCHAR(15),
    description TEXT,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (site_id) REFERENCES site_master(site_id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_development_type (development_type),
    INDEX idx_status (status),
    INDEX idx_date_range (start_date, end_date)
);
```

## Database Relationships

### One-to-Many Relationships

1. **User to Properties**: One user (agent) can manage multiple properties
2. **Customer to Leads**: One customer can have multiple leads
3. **Property to Leads**: One property can have multiple leads
4. **Customer to Visits**: One customer can schedule multiple visits
5. **Property to Visits**: One property can have multiple visits
6. **Lead to Visits**: One lead can result in multiple visits
7. **Visit to Reminders**: One visit can have multiple reminders
8. **User to Notifications**: One user can have multiple notifications
9. **Customer to Invoices**: One customer can have multiple invoices
10. **Customer to Payments**: One customer can have multiple payments
11. **Site to Gata**: One site can have multiple land parcels (gata)
12. **Kissan to Land**: One farmer can own multiple land parcels

### Many-to-One Relationships

1. **Properties to User**: Many properties can be managed by one user (agent)
2. **Leads to Customer**: Many leads are associated with one customer
3. **Leads to Property**: Many leads are for one property
4. **Visits to Customer**: Many visits are scheduled by one customer
5. **Visits to Property**: Many visits are for one property
6. **Reminders to Visit**: Many reminders are for one visit
7. **Notifications to User**: Many notifications are for one user

## Database Diagram

```
+-------------+       +---------------+       +-------------+
|    users    |<------| properties    |------>|   visits    |
+-------------+       +---------------+       +-------------+
      ^                     ^                      ^
      |                     |                      |
      v                     v                      v
+-------------+       +---------------+       +-------------+
|notifications|       |    leads      |------>|  reminders  |
+-------------+       +---------------+       +-------------+
                             ^
                             |
                             v
                      +---------------+
                      |   customers   |
                      +---------------+
```

## Indexes and Performance Optimization

The database schema includes strategic indexes to optimize query performance:

1. Primary keys on all tables for fast lookups
2. Foreign key indexes for efficient joins
3. Indexes on frequently queried columns:
   - Email addresses
   - Status fields
   - Date fields for time-based queries
4. Composite indexes for multi-column conditions

## Security Considerations

1. Password hashing for user authentication
2. Prepared statements for all database queries to prevent SQL injection
3. Input validation and sanitization before database operations
4. Restricted database user permissions based on roles
5. Regular database backups and transaction logging

## Maintenance Procedures

1. Regular index optimization
2. Database statistics updates
3. Query performance monitoring
4. Archiving of old data
### EMI Management System

#### `emi_plans`
```sql
CREATE TABLE IF NOT EXISTS emi_plans (
    id INT PRIMARY KEY AUTO_INCREMENT,
    property_id INT NOT NULL,
    customer_id INT NOT NULL,
    total_amount DECIMAL(12,2) NOT NULL,
    interest_rate DECIMAL(5,2) NOT NULL,
    tenure_months INT NOT NULL,
    emi_amount DECIMAL(12,2) NOT NULL,
    down_payment DECIMAL(12,2) NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    status ENUM('active', 'completed', 'defaulted', 'cancelled') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    created_by INT NOT NULL,
    FOREIGN KEY (property_id) REFERENCES properties(id),
    FOREIGN KEY (customer_id) REFERENCES customers(id),
    FOREIGN KEY (created_by) REFERENCES users(id),
    INDEX idx_customer (customer_id),
    INDEX idx_property (property_id),
    INDEX idx_status (status)
);
```

#### `emi_installments`
```sql
CREATE TABLE IF NOT EXISTS emi_installments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    emi_plan_id INT NOT NULL,
    installment_number INT NOT NULL,
    due_date DATE NOT NULL,
    amount DECIMAL(12,2) NOT NULL,
    principal_component DECIMAL(12,2) NOT NULL,
    interest_component DECIMAL(12,2) NOT NULL,
    payment_status ENUM('pending', 'paid', 'overdue', 'defaulted') NOT NULL DEFAULT 'pending',
    payment_date DATE NULL,
    payment_id INT NULL,
    late_fee DECIMAL(10,2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (emi_plan_id) REFERENCES emi_plans(id),
    FOREIGN KEY (payment_id) REFERENCES payments(id),
    INDEX idx_emi_plan (emi_plan_id),
    INDEX idx_due_date (due_date),
    INDEX idx_status (payment_status)
);
```

#### `emi_late_fee_config`
```sql
CREATE TABLE IF NOT EXISTS emi_late_fee_config (
    id INT PRIMARY KEY AUTO_INCREMENT,
    days_after_due INT NOT NULL,
    fee_type ENUM('fixed', 'percentage') NOT NULL,
    fee_amount DECIMAL(10,2) NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP
);
```

## Database Maintenance

1. Regular backups (daily incremental, weekly full)
2. Index optimization for high-traffic tables
3. Query performance monitoring
4. Data archival strategy
5. Database backup and recovery testing
