# APS Dream Home - Database Setup Documentation

## 🗄️ Database Configuration Status

**Database Name**: `apsdreamhome`  
**Database Type**: MariaDB 10.4.32  
**Setup Date**: September 24, 2025  
**Status**: ✅ Successfully Configured  

---

## 📊 Database Overview

### Database Statistics
- **Total Tables**: 120 tables
- **Admin Users**: 20 users
- **Regular Users**: 72 users  
- **Sample Properties**: 1 property
- **Architecture**: Fully normalized with foreign key relationships

### Core Table Categories

#### 1. User Management (12 tables)
- `admin` - Admin user accounts and permissions
- `users` - Regular user accounts
- `customers` - Customer data and profiles
- `associates` - MLM associates/agents
- `employees` - Company employees
- `user_roles` - User role assignments
- `user_preferences` - User-specific settings
- `user_sessions` - Active user sessions
- `user_social_accounts` - Social login integrations
- `password_resets` - Password reset tokens
- `role_permissions` - Permission mappings
- `roles` - System roles definitions

#### 2. Property Management (15 tables)
- `properties` - Main property listings
- `property_types` - Property type classifications
- `property_features` - Available property features
- `property_feature_mappings` - Property-feature relationships
- `property_images` - Property photo galleries
- `projects` - Development projects
- `project_categories` - Project classifications
- `project_category_relations` - Project-category mappings
- `project_amenities` - Project amenity listings
- `project_gallery` - Project image galleries
- `plots` - Land plot information
- `rental_properties` - Rental-specific data
- `resale_properties` - Resale property listings
- `ar_vr_tours` - Virtual tour configurations
- `saved_searches` - User saved property searches

#### 3. CRM & Lead Management (8 tables)
- `leads` - Lead capture and tracking
- `opportunities` - Sales opportunities
- `communications` - Communication logs
- `customer_journeys` - Customer interaction tracking
- `feedback` - Customer feedback
- `feedback_tickets` - Support ticket system
- `support_tickets` - Technical support requests
- `marketing_campaigns` - Marketing campaign data

#### 4. MLM & Commission System (8 tables)
- `mlm_commissions` - Commission calculations
- `mlm_commission_ledger` - Commission transaction logs
- `mlm_tree` - MLM hierarchy structure
- `commission_transactions` - Commission payments
- `commission_payouts` - Payout tracking
- `resale_commissions` - Resale commission structure
- `associate_levels` - MLM level definitions
- `reward_history` - Reward distribution logs

#### 5. Financial Management (12 tables)
- `payments` - Payment transactions
- `payment_logs` - Payment processing logs
- `payment_gateway_config` - Gateway configurations
- `global_payments` - International payments
- `transactions` - Financial transactions
- `emi` - EMI/loan information
- `emi_plans` - EMI plan structures
- `emi_installments` - Installment tracking
- `rent_payments` - Rental payment tracking
- `expenses` - Company expense tracking
- `salaries` - Employee salary data
- `salary_plan` - Salary structure definitions

#### 6. Communication Systems (8 tables)
- `whatsapp_automation_config` - WhatsApp API settings
- `chat_messages` - Internal chat system
- `notifications` - System notifications
- `notification_settings` - User notification preferences
- `notification_templates` - Message templates
- `notification_logs` - Notification delivery logs
- `voice_assistant_config` - Voice AI configurations
- `ai_chatbot_interactions` - Chatbot conversation logs

#### 7. AI & Advanced Features (10 tables)
- `ai_config` - AI system configuration
- `ai_logs` - AI operation logs
- `ai_lead_scores` - AI-generated lead scores
- `ai_chatbot_config` - Chatbot configurations
- `iot_devices` - IoT device management
- `iot_device_events` - IoT event logging
- `smart_contracts` - Blockchain contracts
- `data_stream_events` - Real-time data events
- `workflow_automations` - Automated workflows
- `workflows` - Business process workflows

#### 8. Security & Audit (12 tables)
- `audit_log` - System audit trail
- `audit_access_log` - Access audit logging
- `admin_activity_log` - Admin action logging
- `activity_logs` - General activity tracking
- `upload_audit_log` - File upload auditing
- `api_request_logs` - API access logs
- `jwt_blacklist` - JWT token blacklist
- `api_keys` - API key management
- `api_rate_limits` - Rate limiting configuration
- `mobile_devices` - Mobile device registration
- `permissions` - System permissions
- `inventory_log` - Inventory change tracking

#### 9. Content Management (8 tables)
- `news` - News and announcements
- `testimonials` - Customer testimonials
- `gallery` - Media gallery
- `about` - About page content
- `documents` - Document management
- `legal_documents` - Legal document storage
- `customer_documents` - Customer-specific documents
- `site_settings` - Website configuration

#### 10. Business Intelligence (10 tables)
- `reports` - Business reports
- `marketing_strategies` - Marketing analytics
- `foreclosure_logs` - Foreclosure tracking
- `tasks` - Task management
- `team` - Team structure
- `team_hierarchy` - Organizational hierarchy
- `attendance` - Employee attendance
- `leaves` - Leave management
- `bookings` - Appointment bookings
- `migrations` - Database migration history

#### 11. Integration & APIs (15 tables)
- `api_integrations` - Third-party integrations
- `api_developers` - API developer accounts
- `api_usage` - API usage statistics
- `api_sandbox` - API testing environment
- `third_party_integrations` - External service integrations
- `marketplace_apps` - App marketplace
- `app_store` - Application store
- `saas_instances` - SaaS deployment instances
- `partner_certification` - Partner certifications
- `partner_rewards` - Partner reward system
- `settings` - System-wide settings
- `companies` - Company/organization data
- `company_employees` - Company-employee relationships
- `farmers` - Agricultural stakeholder data
- `land_purchases` - Land acquisition tracking

---

## 🔧 Database Setup Process

### 1. Database Creation
```sql
CREATE DATABASE IF NOT EXISTS apsdreamhome;
USE apsdreamhome;
```

### 2. Schema Import
- **Source File**: `DATABASE FILE/apsdreamhome.sql`
- **Import Method**: MySQL CLI import
- **Status**: ✅ Successfully imported with minor foreign key constraint warning

### 3. Data Verification
```sql
-- Admin users: 20 records
SELECT COUNT(*) FROM admin;

-- Regular users: 72 records  
SELECT COUNT(*) FROM users;

-- Properties: 1 sample record
SELECT COUNT(*) FROM properties;

-- Total tables: 120 tables
SHOW TABLES;
```

---

## ⚙️ Configuration Requirements

### Database Connection Settings
Update the following files with your database credentials:

#### 1. Main Configuration (`includes/config.php`)
```php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'apsdreamhome');
```

#### 2. Alternative Configuration Files
- `includes/database.php` - Secondary DB connection
- `api/config/database.php` - API database configuration
- `admin/includes/config.php` - Admin panel configuration

### Required PHP Extensions
Ensure the following PHP extensions are enabled:
- `mysqli` - MySQL database connectivity
- `pdo_mysql` - PDO MySQL driver
- `json` - JSON processing
- `curl` - HTTP requests for API integrations
- `gd` - Image processing
- `openssl` - Encryption and security

---

## 🚨 Known Issues & Solutions

### 1. Foreign Key Constraint Warning
**Issue**: Minor foreign key constraint error during import on `resale_commissions` table  
**Impact**: Non-critical - table structure is intact  
**Solution**: 
```sql
-- Disable foreign key checks temporarily
SET FOREIGN_KEY_CHECKS = 0;
-- Re-import specific table if needed
SET FOREIGN_KEY_CHECKS = 1;
```

### 2. Large Dataset Performance
**Consideration**: 120 tables with potential for large datasets  
**Recommendations**:
- Configure MySQL query cache
- Implement proper indexing strategy
- Consider read replicas for high-traffic scenarios

---

## 🔒 Security Configuration

### Database Security Checklist
- [ ] Change default MySQL root password
- [ ] Create dedicated database user with limited privileges
- [ ] Enable MySQL audit logging
- [ ] Configure SSL/TLS for database connections
- [ ] Implement regular backup procedures
- [ ] Set up database firewall rules

### Recommended Database User Setup
```sql
-- Create dedicated application user
CREATE USER 'apsdreamhome'@'localhost' IDENTIFIED BY 'secure_password_here';

-- Grant necessary privileges
GRANT SELECT, INSERT, UPDATE, DELETE ON apsdreamhome.* TO 'apsdreamhome'@'localhost';

-- Revoke unnecessary privileges
REVOKE ALL PRIVILEGES ON *.* FROM 'apsdreamhome'@'localhost';

FLUSH PRIVILEGES;
```

---

## 📈 Performance Optimization

### Indexing Strategy
The database includes optimized indexes for:
- Primary keys on all tables
- Foreign key relationships
- Search-heavy columns (property location, price, etc.)
- User authentication fields

### Query Optimization
- Use prepared statements for all user input
- Implement query result caching
- Optimize JOIN operations for complex queries
- Regular ANALYZE TABLE operations

---

## 🔄 Backup & Maintenance

### Automated Backup Script
```bash
#!/bin/bash
# Daily backup script
mysqldump -u root -p apsdreamhome > backup_$(date +%Y%m%d).sql
```

### Maintenance Tasks
- **Daily**: Automated backups
- **Weekly**: Index optimization and table analysis
- **Monthly**: Database integrity checks
- **Quarterly**: Performance review and optimization

---

## 🌐 Integration Points

### API Database Access
The database supports API access through:
- RESTful API endpoints (`api/` directory)
- JWT-based authentication
- Rate limiting and usage tracking
- Comprehensive audit logging

### External Service Integration
Database tables configured for:
- WhatsApp Business API
- Payment gateways (Razorpay, PayPal)
- AI services (OpenAI, Gemini)
- Email service providers
- SMS gateway services

---

## 📋 Next Steps

### Immediate Tasks
1. ✅ Database schema imported successfully
2. ✅ Sample data verified
3. 🔄 Configure application database connections
4. 🔄 Set up database user with proper privileges
5. 🔄 Implement backup procedures

### Post-Setup Configuration
1. Update all configuration files with database credentials
2. Test application connectivity
3. Configure production security settings
4. Set up monitoring and alerting
5. Implement performance optimization

---

## 📞 Support Information

### Database Issues
- Check MySQL/MariaDB service status
- Verify user privileges and access rights
- Review error logs in `/xampp/mysql/data/`
- Ensure proper PHP extension configuration

### Performance Issues
- Monitor query execution times
- Check available database connections
- Review slow query log
- Consider implementing caching strategies

---

**Database Setup Completed Successfully! ✅**

**Timestamp**: September 24, 2025  
**Version**: APS Dream Home v1.0  
**Setup By**: Automated Database Configuration Script