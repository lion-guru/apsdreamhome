# APS Dream Home Project

## Project Overview

APS Dream Home is an advanced real estate management platform that combines cutting-edge security, performance optimization, and user-centric design to revolutionize property management and customer engagement.

## Key Features

### 🔒 Advanced Security
- Multi-layered security architecture
- Input sanitization and XSS prevention
- CSRF protection
- Two-factor authentication
- Comprehensive security logging
- IP reputation tracking
- Rate limiting

### 📧 Communication Management
- Advanced email and SMS notification systems
- Dynamic email and SMS template management
- Multi-channel communication
- Configurable user notification preferences
- Email and SMS queueing with retry mechanisms

### 🚀 Performance Optimization
- Dependency injection framework
- Caching mechanisms (memory, file-based)
- Performance profiling
- OPcache configuration
- Efficient database query management

### 🔍 Key Modules
- Property Management
- Lead Tracking
- Visit Scheduling
- User Authentication
- Notification System
- Analytics & Reporting

## Project Structure

The project is organized in a modular way to ensure easy maintenance and scalability:

### Main Directories

- **admin/** - Admin panel interface
  - Dashboard, property management, user management, analytics

- **agent/** - Agent interface
  - Dashboard, lead management, visit scheduling, property management

- **api/** - API endpoints for mobile and third-party integrations

- **assets/** - Static assets
  - **css/** - CSS files and frameworks
  - **js/** - JavaScript files
  - **images/** - Image files
  - **fonts/** - Font files

- **database/** - Database migration and seed files
  - SQL scripts for table creation and sample data

- **includes/** - Common PHP components
  - **templates/** - Common templates like headers and footers
  - **config/** - Configuration files
  - **functions/** - Helper functions and utilities
  - **classes/** - Class definitions for core functionality

- **logs/** - System logs for debugging and auditing

### Important Files

- **config.php** - Main configuration file
- **.env** - Environment variables (do not commit)
- **.env.example** - Template for environment variables
- **.gitignore** - Git ignore rules
- **PROJECT_STATUS.md** - Current project status and roadmap

## Database Setup

### 🗄️ Migration Order
Run database migration scripts in the following order:
1. `database/migrations/20250514_001_auth_tables.sql`
2. `database/migrations/20250514_002_security_tables.sql`
3. `database/migrations/20250514_003_email_queue.sql`
4. `database/migrations/20250514_004_sms_queue.sql`

### 🔐 Initial Configuration
- Create an admin user
- Configure email and SMS providers
- Set up initial notification templates

## Background Tasks

### Recommended Cron Jobs
```bash
# Process email queue every 5 minutes
*/5 * * * * php /path/to/project/scripts/process_email_queue.php

# Process SMS queue every 5 minutes
*/5 * * * * php /path/to/project/scripts/process_sms_queue.php

# Clean up security logs and tokens
0 1 * * * php /path/to/project/scripts/security_cleanup.php
```

## Installation Requirements

### 🖥️ System Requirements
- PHP 8.1+ (Recommended)
- MySQL 8.0+ 
- Apache 2.4+ / Nginx 1.18+
- Composer
- Twilio Account (for SMS)

### 🔧 Dependencies
- PHPMailer
- Twilio PHP SDK
- PHP-JWT
- OPcache
- PDO/MySQLi

### 🛠️ Environment Setup
1. Clone the repository
2. Run `composer install`
3. Configure `.env` file with database and service credentials
4. Run database migrations
5. Set up cron jobs for background tasks

## Security Configuration

### Environment Variables
- `APP_SECRET_KEY`: Application encryption key
- `DB_HOST`, `DB_USER`, `DB_PASS`: Database credentials
- `EMAIL_USERNAME`, `EMAIL_PASSWORD`: SMTP credentials
- `SMS_TWILIO_SID`, `SMS_TWILIO_TOKEN`: Twilio SMS credentials

### Recommended Security Practices
- Use strong, unique passwords
- Enable two-factor authentication
- Regularly update dependencies
- Monitor security logs
- Implement IP whitelisting
- Use HTTPS

## Database Connection

The system uses a secure and optimized database connection management system:

### Database Configuration

- Default database credentials are stored in `includes/config/db_config.php`:
  ```php
  define('DB_HOST', 'localhost');
  define('DB_USER', 'root');
  define('DB_PASS', '');
  define('DB_NAME', 'apsdreamhomefinal');
  ```

### Connection Management

- The system uses a centralized connection management approach in `includes/db_connection.php`
- Features include:
  - Environment variable loading
  - Parameter validation
  - Comprehensive error logging
  - Connection pooling
  - SSL support for secure connections
  - UTF-8 character set enforcement
  - SQL injection prevention

### Connection Usage

To use the database connection in any file:

```php
// Include the database connection
require_once __DIR__ . '/includes/db_connection.php';

// Get connection
$conn = getDbConnection();

// Use prepared statements for all queries
$stmt = $conn->prepare("SELECT * FROM properties WHERE id = ?");
$stmt->bind_param("i", $property_id);
$stmt->execute();
$result = $stmt->get_result();
```

4. Import the database schema:
   ```bash
   mysql -u your_username -p apsdreamhomefinal < database/database_structure.sql
   ```

5. Import sample data (optional):
   ```bash
   mysql -u your_username -p apsdreamhomefinal < database/insert_sample_data.sql
   ```

6. Set up your web server (Apache/Nginx) to point to the project directory

7. Access the application:
   - Frontend: http://localhost/apsdreamhomefinal
   - Admin Panel: http://localhost/apsdreamhomefinal/admin
   - Agent Dashboard: http://localhost/apsdreamhomefinal/agent

## Demo Data & Test Logins

The platform comes pre-seeded with demo data for all user types and employees for easy testing and stakeholder review.

**Demo Login Credentials:**

- Multiple user types: admin, associate, agent, builder, tenant, employee, superadmin, investor, customer, user
- Password for all demo employees: `Aps@128128`
- See `database/create_employees_table.sql` for demo employee details

## Admin Module Roles & Login

- **Allowed Roles:**
  - admin, super_admin, finance, it_head, hr, marketing, operations, legal, sales, support, manager, director, ceo, cto, cfo, coo, cm, office_admin, official_employee
- **Admin Table:**
  - All official admin users must have their `role` column set to one of the above roles (case-insensitive, recommended lowercase with underscores).
  - The `status` column must be `active` for login to succeed.
  - Password for all official admin users: `Aps@128128`

## New Automated Systems

### 1. Property Recommendations
- AI-Powered Suggestions for similar properties
- Market Analysis with price comparisons
- Personalized recommendations for logged-in users

### 2. Automated Visit Scheduling
- Smart Scheduling System with pre-defined time slots
- Real-time availability checking
- Automated notifications and visit reminders

### 3. Follow-up System
- Automated Communications for visit reminders and follow-ups
- AI Integration to track customer engagement
- Email templates for different communication types

### 4. Property Comparison
- Detailed Analysis with side-by-side comparison
- Scoring System for price, location, and amenities
- Market trends and property ratings

### 5. Cron Jobs
- Automated Tasks for follow-ups, AI score updates, and reports
- Monitoring with success/error logging
- System health checks

## Modernization & Backups

- All dashboards have been modernized with Bootstrap 5, FontAwesome, and card-based UI
- Legacy files are backed up with `.bak` extensions before modernization
- Redirects are in place from legacy files to modern equivalents
- All duplicate/legacy dashboard files have been removed for maintainability

## Project Status

For a detailed breakdown of completed features, pending tasks, and future enhancements, please refer to the `PROJECT_STATUS.md` file.

*Last Updated: May 13, 2025*
