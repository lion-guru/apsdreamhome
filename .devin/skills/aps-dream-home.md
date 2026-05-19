# APS Dream Home Development Skill

Specialized skill for APS Dream Home real estate management system development and maintenance.

## Description

This skill provides context-aware assistance for the APS Dream Home project, including database operations, testing, admin panel management, and common development tasks.

## When to Use

Use this skill when working on:
- Database queries and migrations
- Admin panel features and RBAC
- User authentication and permissions
- Property management system
- Testing and validation
- Project-specific debugging
- Configuration and environment setup

## Commands Reference

### Database Operations
- **Query database**: Direct MySQL queries on port 3307
- **Check tables**: List and describe database tables
- **Run migrations**: Execute database migration scripts
- **Seed data**: Populate test data for development

### Admin Panel
- **Test admin login**: Bypass authentication for testing
- **Check RBAC**: Verify role-based permissions
- **Menu management**: Admin sidebar menu operations
- **User management**: Create and manage admin users

### Testing
- **Run visual tests**: Execute Playwright visual tests
- **User login tests**: Test authentication for all user roles
- **Sidebar consistency**: Verify menu rendering across pages
- **Full test suite**: Run comprehensive A-to-Z tests

### Development Tools
- **Fix permissions**: Configure file permissions
- **Clear cache**: Clear application cache
- **Restart services**: Restart XAMPP services
- **Check logs**: View error logs

## Database Configuration

- **Host**: 127.0.0.1
- **Port**: 3307
- **Database**: apsdreamhome
- **User**: root
- **Password**: (empty)

## Common Issues and Solutions

### Admin Login Issues
- Use test-login bypass: `/admin/login?test_login=1`
- Check admin_users table for credentials
- Verify session configuration

### RBAC Menu Issues
- Check admin_menu_items table has 150 active items
- Verify admin_role_menu_permissions table is populated
- Run `tools/setup_rbac_permissions.php`

### Database Connection Issues
- Verify XAMPP MySQL is running on port 3307
- Check database credentials in config files
- Test connection via `tools/check_rbac_menu_system.php`

### Layout Issues
- Ensure unified layout files exist in proper directories
- Check that controllers pass required variables to views
- Verify CSS and JS assets are loading correctly

## Project Structure Key Points

- **Controllers**: `app/Http/Controllers/`
- **Models**: `app/Models/`
- **Views**: `app/Views/`
- **Routes**: `routes/web.php`, `routes/api.php`
- **Database Tools**: `tools/`
- **Tests**: `testing/visual_tests/`

## User Roles and Permissions

- **Super Admin**: Full access to all features
- **Admin**: Full administrative access
- **Manager**: Access to CRM, Properties, Users
- **Associate**: MLM network, commissions, referrals
- **Agent**: Leads, properties, clients
- **Employee**: Tasks, attendance, HR features
- **Customer**: Properties, inquiries, bookings

## Quick Commands

```bash
# Start XAMPP services
# Start Apache and MySQL from XAMPP Control Panel

# Run comprehensive test
node testing/visual_tests/MASTER_TEST_RUNNER.js

# Fix RBAC permissions
php tools/setup_rbac_permissions.php

# Check database health
php tools/comprehensive_project_analysis.php

# Test admin login
# Visit: http://localhost/apsdreamhome/admin/login?test_login=1

# Clear all caches
# Delete contents of cache directories if any
```

## Environment Variables

Set these for development:
- `APP_ENV`: development
- `DB_HOST`: 127.0.0.1
- `DB_PORT`: 3307
- `DB_NAME`: apsdreamhome
- `DB_USER`: root
- `BASE_URL`: http://localhost/apsdreamhome

## Contact and Support

For project-specific issues, check:
- `AGENTS.md` - Project status and rules
- `PROJECT_MAP.md` - Architecture documentation
- `PROJECT_IMPROVEMENTS_REPORT.md` - Recent improvements