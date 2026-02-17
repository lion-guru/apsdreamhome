# Database Schema Version Control System

## Overview
A comprehensive PHP-based tool for managing database schema migrations, tracking changes, and maintaining version history.

## Features
- Automated migration management
- SQL and PHP migration support
- Detailed migration history tracking
- Backup before migration
- Schema difference reporting
- Configurable migration rules

## Migration Types
1. **SQL Migrations**
   - Direct SQL script execution
   - Up and down migration support
   - Transactional changes

2. **PHP Migrations**
   - Object-oriented migration classes
   - Programmatic schema modifications
   - Advanced transformation logic

## Configuration
Customize via `config/db_schema_version_config.json`:
- Migration naming patterns
- Backup settings
- Migration history size
- Allowed migration types

## Usage Modes

### Command Line
```bash
php db_schema_version_control.php
```

### Web Interface
Navigate to `db_schema_version_control.php`

## Migration Workflow
1. Create migration script
2. Review migration
3. Apply migrations
4. Track migration history
5. Generate reports

## Configuration Options
- Migration file naming
- Backup before migration
- Maximum migration history
- Supported migration types

## Reporting Capabilities
- Migration history tracking
- Schema difference reports
- Execution time logging
- Error tracking

## Security Considerations
- Prepared statement usage
- Transaction-based migrations
- Detailed error logging
- Configurable backup

## Dependencies
- PHP 7.4+
- PDO Extension
- MySQL/MariaDB
- Optional: mysqldump for backups

## Best Practices
- Version control migrations
- Test migrations in staging
- Review migration scripts
- Monitor logs
- Backup before major changes

## Troubleshooting
- Check `logs/schema_version_control_*.log`
- Verify database connection
- Ensure proper permissions
- Review migration scripts

## Limitations
- Database-specific migrations
- Performance overhead
- Manual intervention for complex changes

## Recommended Workflow
1. Design schema changes
2. Create migration script
3. Test in development
4. Apply to staging
5. Deploy to production
6. Monitor and log
