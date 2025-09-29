# Database Migrations for Tests

This directory contains database migrations for the test environment. Migrations help manage database schema changes and test data in a consistent way across different environments.

## Directory Structure

```
tests/
  Database/
    Migrations/           # Migration files
    Migration.php         # Base migration class
    MigrationManager.php  # Handles running migrations
```

## Creating Migrations

1. Create a new migration file in the `Migrations` directory with the following naming convention:
   `YYYY_MM_DD_HHMMSS_descriptive_name.php`

2. Extend the base `Migration` class and implement the `up()` and `down()` methods:

```php
<?php

namespace Tests\Database\Migrations;

use Tests\Database\Migration;

class YYYY_MM_DD_HHMMSS_descriptive_name extends Migration
{
    public function up(): void
    {
        // Create tables, modify schema, or insert test data
        $this->execute("CREATE TABLE IF NOT EXISTS `table_name` (...)");
    }

    public function down(): void
    {
        // Reverse the changes made in up()
        $this->execute("DROP TABLE IF EXISTS `table_name`");
    }
}
```

## Running Migrations

### Command Line

Run migrations:
```bash
php tests/migrate.php migrate
```

Rollback the last batch of migrations:
```bash
php tests/migrate.php rollback
```

Refresh all migrations:
```bash
php tests/migrate.php refresh
```

Reset the database (drop all tables and reset migrations):
```bash
php tests/migrate.php reset
```

### In Tests

Migrations are automatically run before tests via the `bootstrap.php` file. The test database connection is configured using environment variables:

```
DB_HOST=localhost
DB_NAME=apsdreamhome_test
DB_USER=testuser
DB_PASS=testpass
```

## Best Practices

1. Each migration should be atomic and independent
2. Always implement both `up()` and `down()` methods
3. Use the `execute()` method to run SQL queries
4. Keep migrations idempotent (can be run multiple times without errors)
5. Include comments explaining the purpose of the migration
6. Use transactions where appropriate to ensure data consistency

## Example Migration

See `2025_09_28_000001_create_properties_table.php` for a complete example.
