# APS Dream Homes - Property Management System

## Overview

APS Dream Homes is a comprehensive property management system designed for real estate agencies. The system handles property listings, lead management, visit scheduling, and notifications to streamline the property sales and rental process.

## Project Structure

The project is organized in a modular way to ensure easy maintenance and scalability:

### Main Directories

- **admin/** - Admin panel interface
  - Dashboard, property management, user management, analytics
  - EMI management system
  - Payment tracking and processing

- **agent/** - Agent interface
  - Dashboard, lead management, visit scheduling, property management
  - EMI plan monitoring

- **api/** - API endpoints for mobile and third-party integrations
  - EMI calculation endpoints
  - Payment processing endpoints

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

## Key Features

### Property Management
- Property listing and details
- Property status tracking (available, sold, under contract)
- Property type categorization
- Property image management
- Property search and filtering

### Lead Management
- Lead capture from multiple sources
- Lead assignment to agents
- Lead status tracking
- Lead follow-up reminders
- Lead conversion analytics

### EMI Management System

### Overview
The EMI (Equated Monthly Installment) management system is designed to handle property financing through installment plans. It provides comprehensive tools for managing EMI plans, tracking payments, and handling late fees.

### Key Features

1. **EMI Plan Management**
   - Create and manage EMI plans
   - Automatic EMI calculation based on principal, interest rate, and tenure
   - Down payment handling
   - Plan status tracking (active/completed/defaulted/cancelled)

2. **Installment Tracking**
   - Monthly installment scheduling
   - Principal and interest component breakdown
   - Due date management
   - Payment status monitoring

3. **Payment Processing**
   - Multiple payment methods (Cash, Bank Transfer, UPI, Cheque)
   - Late fee calculation and management
   - Payment receipt generation
   - Payment history tracking

4. **Notifications & Reminders**
   - Due date reminders
   - Payment confirmation notifications
   - Late payment alerts
   - Plan completion notifications

5. **Reports & Analytics**
   - EMI collection statistics
   - Payment status reports
   - Default rate analysis
   - Revenue forecasting

### Usage

1. **Creating EMI Plans**
   ```
   Navigate to Admin > Accounting > EMI Plans > Add New Plan
   Fill in the required details:
   - Select Property and Customer
   - Enter Total Amount and Down Payment
   - Set Interest Rate and Tenure
   - Choose Start Date
   ```

2. **Recording Payments**
   ```
   Navigate to Admin > Accounting > EMI Plans
   Click on View for the specific plan
   Click Record Payment on the pending installment
   Enter payment details and submit
   ```

3. **Monitoring EMI Status**
   ```
   Navigate to Admin > Accounting > Dashboard
   View EMI statistics and charts
   Check overdue payments and upcoming dues
   ```

### Visit Scheduling
- Visit request handling
- Availability management
- Visit confirmation and reminders
- Visit feedback collection
- Visit status tracking

### Notification System
- Real-time in-app notifications
- Email notifications
- Customizable notification templates
- Notification preferences

### User Management
- Role-based access control
- User authentication and authorization
- User profile management
- Agent performance tracking

### Security Features
- Input validation and sanitization
- CSRF protection
- Password hashing
- Session management
- XSS prevention
- SQL injection prevention

## Database Schema

### Core Tables
- `users` - User management (admin, agents, customers)
- `properties` - Property listings with details
- `customers` - Customer information
- `leads` - Lead management system
- `property_visits` - Visit scheduling system
- `notifications` - Notification system

### Supporting Tables
- `visit_reminders` - Automated visit reminders
- `visit_availability` - Property viewing time slots
- `notification_settings` - User notification preferences
- `notification_templates` - Templates for different notification types
- `notification_logs` - Debugging and analytics for notifications

## Environment Setup

1. Clone the repository:
   ```bash
   git clone https://github.com/your-username/apsdreamhomefinal.git
   ```

2. Copy `.env.example` to `.env`:
   ```bash
   cp .env.example .env
   ```

3. Update the `.env` file with your configuration:
   ```env
   # Database Configuration
   DB_HOST=localhost
   DB_USER=your_username
   DB_PASS=your_password
   DB_NAME=apsdreamhomefinal

   # Google OAuth Configuration
   GOOGLE_CLIENT_ID="your-google-client-id"
   GOOGLE_CLIENT_SECRET="your-google-client-secret"

   # Gemini AI Configuration
   GEMINI_API_KEY="your-gemini-api-key"
   ```

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
