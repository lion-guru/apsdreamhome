# APS Dream Home – Database Kit

This folder is the single source of truth for every database artefact.

| Folder         | Purpose                                                                 |
|----------------|-------------------------------------------------------------------------|
| `00_schema`    | Canonical dumps – `apsdreamhome_schema.sql` (structure) + `apsdreamhome_data.sql` (rows). Always replay these first. |
| `01_migrations`| Replay-able, timestamped SQL deltas. Run in alphabetical order via `migrate.php` or your own runner. |
| `02_seeds`     | Idempotent PHP seeders. Safe to run many times: `php 02_seeds/SeedLeads.php` |
| `03_backups`   | Daily / weekly full snapshots. Name pattern: `YYYY-MM-DD_backup[_full│_schema].sql` |
| `04_tools`     | CLI helpers that ship with the project (db_health, optimize, etc.). |
| `05_archive`   | Obsolete or reference material; not executed in normal workflows. |

## Quick start
1. Fresh install:  `mysql -u root -p apsdreamhome < 00_schema/apsdreamhome_schema.sql`  then  `< apsdreamhome_data.sql`  
2. Apply new deltas: `php 04_tools/migrate.php`  (runs everything in `01_migrations` that hasn’t been recorded in `migrations` table).  
3. Seed sample data: `php 02_seeds/SeedLeads.php && php 02_seeds/SeedTransactions.php`  
4. Nightly backup:  `mysqldump --routines --triggers apsdreamhome > 03_backups/$(date +%F)_backup_full.sql`

Never commit production credentials or real customer data.