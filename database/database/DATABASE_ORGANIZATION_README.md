# APS Dream Homes - Database Organization

## Directory Structure

```
database/
├── current/           # Main database files (apsdreamhomes.sql, etc.)
├── backups/           # Database backup files
├── migrations/        # Database migration and update scripts
├── seeders/           # Sample data and seed files
└── archive/           # Archived/old database files
```

## Main Database Files

- `current/aps_dream_homes_current.sql` - Main database schema and data
- `current/aps_dream_homes_main_config.sql` - Header/footer settings and configuration
- `backups/` - All backup files organized by date
- `migrations/` - Database patches and updates

## Setup Instructions

1. Import the main database: `current/aps_dream_homes_current.sql`
2. Run configuration: `current/aps_dream_homes_main_config.sql`
3. Check migrations folder for any additional updates needed

## Last Organized

2025-09-30 17:23:26
