# Database Migration Guide

## Overview
This guide provides instructions for migrating the APS Dream Home database between different environments.

## Prerequisites
- MySQL/MariaDB server
- Database backup files
- Sufficient permissions to create/modify databases

## Migration Steps

### 1. Backup Existing Database
```bash
mysqldump -u [username] -p [database_name] > backup_$(date +%Y%m%d).sql
```

### 2. Create New Database (if needed)
```sql
CREATE DATABASE apsdreamhome CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 3. Import Database Schema
```bash
mysql -u [username] -p [database_name] < schema.sql
```

### 4. Import Data
```bash
mysql -u [username] -p [database_name] < data_dump.sql
```

### 5. Update Configuration
Update your `config.php` with the new database credentials:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
define('DB_NAME', 'apsdreamhome');
```

## Common Issues

### Character Set Issues
If you encounter character encoding issues, ensure your database and tables use UTF-8:

```sql
ALTER DATABASE apsdreamhome CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### Permission Issues
Make sure the database user has sufficient privileges:

```sql
GRANT ALL PRIVILEGES ON apsdreamhome.* TO 'username'@'localhost';
FLUSH PRIVILEGES;
```

## Rollback Procedure

1. Restore from backup:
   ```bash
   mysql -u [username] -p [database_name] < backup_file.sql
   ```

2. Revert any configuration changes

## Best Practices
- Always backup before migration
- Test migrations in a staging environment first
- Document any schema changes
- Keep migration scripts in version control
- Monitor performance after migration
