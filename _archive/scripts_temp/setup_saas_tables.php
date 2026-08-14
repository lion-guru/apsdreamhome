<?php
/**
 * SaaS Multi-Tenancy Foundation â€” Database Setup
 * Creates: tenants, subscription_plans, tenant_subscriptions tables
 * Seeds: 4 subscription plans (Free/Pro/Enterprise/Custom)
 */

require_once __DIR__ . '/../vendor/autoload.php';

$db = \App\Core\Database\Database::getInstance()->getConnection();

echo "=== SaaS Multi-Tenancy Setup ===\n\n";

// â”€â”€ 1. tenants table â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
$db->exec("CREATE TABLE IF NOT EXISTS tenants (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    domain VARCHAR(255) DEFAULT NULL,
    logo_url VARCHAR(500) DEFAULT NULL,
    primary_color VARCHAR(7) DEFAULT '#667eea',
    secondary_color VARCHAR(7) DEFAULT '#764ba2',
    contact_name VARCHAR(200) DEFAULT NULL,
    contact_email VARCHAR(255) DEFAULT NULL,
    contact_phone VARCHAR(20) DEFAULT NULL,
    address TEXT DEFAULT NULL,
    city VARCHAR(100) DEFAULT NULL,
    state VARCHAR(100) DEFAULT NULL,
    plan_id INT UNSIGNED DEFAULT 1,
    status ENUM('active','suspended','cancelled','trial') DEFAULT 'trial',
    trial_ends_at DATETIME DEFAULT NULL,
    max_users INT DEFAULT 1,
    max_leads INT DEFAULT 50,
    max_properties INT DEFAULT 10,
    storage_limit_mb INT DEFAULT 100,
    features_enabled JSON DEFAULT NULL,
    config JSON DEFAULT NULL,
    settings JSON DEFAULT NULL,
    onboarded_at DATETIME DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at DATETIME DEFAULT NULL,
    UNIQUE KEY idx_slug (slug),
    KEY idx_status (status),
    KEY idx_plan (plan_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
echo "âœ… tenants table created\n";

// â”€â”€ 2. subscription_plans table â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
$db->exec("CREATE TABLE IF NOT EXISTS subscription_plans (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(50) NOT NULL UNIQUE,
    description TEXT DEFAULT NULL,
    price_monthly DECIMAL(10,2) DEFAULT 0.00,
    price_yearly DECIMAL(10,2) DEFAULT 0.00,
    currency VARCHAR(3) DEFAULT 'INR',
    max_users INT DEFAULT 1,
    max_leads INT DEFAULT 50,
    max_properties INT DEFAULT 10,
    max_associates INT DEFAULT 0,
    storage_limit_mb INT DEFAULT 100,
    api_access TINYINT(1) DEFAULT 0,
    white_label TINYINT(1) DEFAULT 0,
    custom_domain TINYINT(1) DEFAULT 0,
    priority_support TINYINT(1) DEFAULT 0,
    advanced_analytics TINYINT(1) DEFAULT 0,
    mlm_engine TINYINT(1) DEFAULT 0,
    accounting_module TINYINT(1) DEFAULT 0,
    ai_features TINYINT(1) DEFAULT 0,
    mobile_app TINYINT(1) DEFAULT 0,
    features_enabled JSON DEFAULT NULL,
    is_active TINYINT(1) DEFAULT 1,
    sort_order INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY idx_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
echo "âœ… subscription_plans table created\n";

// â”€â”€ 3. tenant_subscriptions table â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
$db->exec("CREATE TABLE IF NOT EXISTS tenant_subscriptions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT UNSIGNED NOT NULL,
    plan_id INT UNSIGNED NOT NULL,
    status ENUM('active','cancelled','past_due','trialing') DEFAULT 'active',
    billing_cycle ENUM('monthly','yearly') DEFAULT 'monthly',
    amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    razorpay_subscription_id VARCHAR(100) DEFAULT NULL,
    razorpay_customer_id VARCHAR(100) DEFAULT NULL,
    current_period_start DATETIME DEFAULT NULL,
    current_period_end DATETIME DEFAULT NULL,
    cancelled_at DATETIME DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_tenant (tenant_id),
    KEY idx_plan (plan_id),
    KEY idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
echo "âœ… tenant_subscriptions table created\n";

// â”€â”€ 4. tenant_usage table (tracks usage per billing cycle) â”€
$db->exec("CREATE TABLE IF NOT EXISTS tenant_usage (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT UNSIGNED NOT NULL,
    period_start DATE NOT NULL,
    period_end DATE NOT NULL,
    users_count INT DEFAULT 0,
    leads_created INT DEFAULT 0,
    properties_count INT DEFAULT 0,
    api_calls INT DEFAULT 0,
    storage_used_mb INT DEFAULT 0,
    emails_sent INT DEFAULT 0,
    sms_sent INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY idx_tenant_period (tenant_id, period_start),
    KEY idx_tenant (tenant_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
echo "âœ… tenant_usage table created\n";

// â”€â”€ Seed Subscription Plans â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
$plans = [
    ['Free', 'free', 'Perfect for trying out the platform', 0, 0, 1, 50, 10, 0, 100, 0,0,0,0,0,0,0,0,0, 1],
    ['Starter', 'starter', 'For small real estate teams', 999, 9999, 5, 500, 50, 10, 500, 0,0,0,0,1,0,0,0,0, 2],
    ['Professional', 'professional', 'For growing real estate businesses', 2999, 29999, 25, 5000, 500, 100, 2048, 1,1,0,1,1,1,1,1,1, 3],
    ['Enterprise', 'enterprise', 'Unlimited access with white-label', 9999, 99999, 999, 99999, 9999, 9999, 10240, 1,1,1,1,1,1,1,1,1, 4],
];

$stmt = $db->prepare("INSERT IGNORE INTO subscription_plans 
    (name, slug, description, price_monthly, price_yearly, max_users, max_leads, max_properties, max_associates, storage_limit_mb,
     api_access, white_label, custom_domain, priority_support, advanced_analytics, mlm_engine, accounting_module, ai_features, mobile_app, sort_order)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

foreach ($plans as $p) {
    $stmt->execute($p);
    echo "  â†’ Plan: {$p[0]} (â‚¹{$p[3]}/mo)\n";
}
echo "âœ… 4 subscription plans seeded\n";

// â”€â”€ Seed APS Dream Home as first tenant â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
$check = $db->query("SELECT COUNT(*) FROM tenants WHERE slug = 'apsdreamhome'")->fetchColumn();
if ($check == 0) {
    $db->exec("INSERT INTO tenants (name, slug, domain, contact_name, contact_email, contact_phone, plan_id, status, max_users, max_leads, max_properties, storage_limit_mb, onboarded_at)
        VALUES ('APS Dream Home', 'apsdreamhome', 'localhost', 'Abhaay Singh', 'admin@apsdreamhome.com', '+91-9918061919', 4, 'active', 999, 99999, 9999, 10240, NOW())");
    echo "âœ… APS Dream Home seeded as first tenant (Enterprise plan)\n";
} else {
    echo "â†’ APS Dream Home tenant already exists\n";
}

echo "\n=== SaaS Setup Complete ===\n";
echo "Tables: tenants, subscription_plans, tenant_subscriptions, tenant_usage\n";
echo "Plans: Free (â‚¹0), Starter (â‚¹999), Professional (â‚¹2,999), Enterprise (â‚¹9,999)\n";
echo "Tenant: APS Dream Home (Enterprise, active)\n";?>