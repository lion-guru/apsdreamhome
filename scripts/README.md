# Scripts Folder

**24 essential scripts** for database management, seeding, and maintenance.
One-off cleanup scripts are archived in `_archive/` (historical reference only).

## Migrations & Tracking
- `create_migrations_table.php` — Initialize `_migrations` table
- `track_migration.php <name> <category> [desc] [rows]` — Mark a script as applied
- `view_migrations.php [limit]` — View recent migration history

## Schema Fixes
- `fix_pincodes_table.php` — Add missing columns to `pincodes`
- `fix_user_properties_schema.php` — Add location columns to `user_properties`
- `fix_testimonials_table.php` — Fix testimonials schema
- `fix_schema.php` — General schema repair
- `fix_mlm_extensions.php` — Backfill MLM extension records for missing associates/agents

## Schema Additions
- `add_property_image_column.php` — Add image column to properties
- `add_ticket_booking_column.php` — Add booking columns to tickets
- `add_colony_content_columns.php` — Add content columns to colonies
- `add_user_tracking_columns.php` — Add tracking columns to users
- `add_admin_menu_items.php` — Add menu items to admin sidebar
- `add_voice_ai_indexes.php` — Add performance indexes for voice AI
- `apply_performance_indexes.php` — Apply general performance indexes

## Data Seeding
- `seed_api_keys.php` — Seed API keys
- `seed_bank_data.php` — Seed bank master + branches
- `seed_pincodes.php` — Seed postal codes
- `seed_complete_location_data.php` — Seed countries/states/districts/cities
- `seed_voice_agents.php` — Seed voice AI agents + scripts
- `seed_feature_tables.php` — Seed 22 sample feature records
- `seed_feature_tables_2.php` — Seed 55+ more feature records

## Maintenance
- `drop_broken_views.php` — Drop views referencing non-existent tables
- `cron_daily_compliance.php` — Daily compliance automation (cron)

## Archive
`_archive/` contains 125+ one-off cleanup/analysis scripts from the 2026-06-02/03 cleanup sprint.
**Do not run** — historical reference only. Most have been replaced by cleaner approaches.
