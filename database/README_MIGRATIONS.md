# APS Dream Home - Database Migration Manager

## Overview

The Database Migration Manager is a powerful tool for managing database schema changes and versioning in your APS Dream Home system. It allows you to:

1. **Track Database Schema Changes**: Keep a history of all changes made to your database structure
2. **Version Control**: Maintain different versions of your database schema
3. **Safe Updates**: Apply changes in a controlled manner with transaction support
4. **Rollback Capability**: Revert changes if needed
5. **Documentation**: Automatically document database changes

## Key Features

- **Version Tracking**: Automatically tracks the current database version
- **Migration Scripts**: Create and manage SQL migration scripts
- **Rollback Support**: Create and execute rollback scripts to undo migrations
- **Transaction Safety**: All migrations run in transactions for data integrity
- **User-Friendly Interface**: Easy-to-use web interface for managing migrations

## How to Use

### Accessing the Migration Manager

1. Navigate to the Database Management Hub: `http://localhost/apsdreamhomefinal/database/`
2. Click on "Manage Migrations" in the Quick Actions section

### Creating a New Migration

1. In the Migration Manager, scroll to the "Create New Migration" section
2. Enter a version number (e.g., 1.0.1) following semantic versioning
3. Enter a descriptive name for your migration (e.g., Add_User_Preferences_Table)
4. Click "Create Migration"
5. Edit the generated SQL file in the migrations directory to add your SQL statements

### Running Migrations

1. In the Migration Manager, view the list of available migrations
2. Select the target version you want to migrate to
3. Click "Run Migrations" to apply all pending migrations up to the selected version

### Creating Rollback Scripts

1. For any migration, click the "Create Rollback" button
2. Edit the generated rollback SQL file to add statements that will undo your migration

### Rolling Back Migrations

1. For any executed migration, click the "Rollback" button
2. Confirm the rollback action
3. The system will execute the rollback script and revert to the previous version

## Migration File Format

### Migration Files

Migration files follow this naming convention:
```
V{version}__{name}.sql
```

Example: `V1.0.1__Add_User_Preferences_Table.sql`

### Rollback Files

Rollback files follow this naming convention:
```
R{version}__{name}.sql
```

Example: `R1.0.1__Add_User_Preferences_Table.sql`

## Best Practices

1. **Small, Focused Migrations**: Create small, focused migrations that do one thing well
2. **Always Create Rollbacks**: Always create rollback scripts for your migrations
3. **Test Before Production**: Test migrations in a development environment before applying to production
4. **Document Changes**: Include comments in your migration files explaining the purpose of each change
5. **Use Transactions**: The system uses transactions automatically, but be aware of statements that might cause implicit commits
6. **Verify Migrations**: Include verification queries at the end of your migration scripts

## Example Migration

```sql
-- Migration: Add User Preferences Table
-- Version: 1.0.1
-- Created: 2025-05-18

-- Create user preferences table
CREATE TABLE user_preferences (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    preference_key VARCHAR(100) NOT NULL,
    preference_value TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_user_preference (user_id, preference_key),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Add index for faster lookups
CREATE INDEX idx_user_preferences_key ON user_preferences(preference_key);

-- Migration verification
SELECT COUNT(*) FROM user_preferences;
```

## Example Rollback

```sql
-- Rollback for Migration: Add User Preferences Table
-- Version: 1.0.1
-- Created: 2025-05-18

-- Drop the user_preferences table
DROP TABLE IF EXISTS user_preferences;

-- Rollback verification
SHOW TABLES LIKE 'user_preferences';
```

## Troubleshooting

- **Migration Failed**: If a migration fails, the entire transaction is rolled back, and the database remains unchanged
- **Rollback Failed**: If a rollback fails, check the error message and fix the rollback script
- **Version Conflict**: If you try to create a migration with an existing version, you'll receive an error message

## Support

For any issues or questions about the Database Migration Manager, please contact your system administrator or the APS Dream Home support team.
