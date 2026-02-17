# APS Dream Home – Database Kit

This folder is the single source of truth for every database artifact.

| Folder    | Purpose                                                                |
| --------- | ---------------------------------------------------------------------- |
| `sql`     | Consolidated SQL files, schema definitions, migrations, and seeds.     |
| `scripts` | Database maintenance, utility scripts, and PHP-based migrations/seeds. |
| `docs`    | Database documentation, guides, and reference material.                |
| `archive` | Obsolete or reference material; not executed in normal workflows.      |

## Directory Structure

### `sql/`

-   `schema/`: Canonical dumps and structure files (e.g., `enhanced_database_structure.sql`).
-   `migrations/`: Replay-able SQL deltas.
-   `seeds/`: SQL data seeding files.
-   `modules/`: Feature-specific SQL modules.
-   `patches/`: Hotfixes and patches.

### `scripts/`

-   `migrations/`: PHP-based migration runners and scripts.
-   `seeds/`: PHP seeders (e.g., `complete_database_seed.php`).
-   `setup/`: Installation and setup scripts.
-   `tools/`: CLI helpers (backup, export, health check).
-   `updates/`: Database update scripts for new features.

## Quick start

1. **Fresh install**: Import `sql/schema/enhanced_database_structure.sql` into your database.
2. **Apply updates**: Run scripts in `scripts/updates/` as needed.
3. **Seed data**: Run `php scripts/seeds/complete_database_seed.php`.
4. **Backup**: Use `php scripts/tools/backup_db.php` or the web interface.

## Management Panel

Access `database/index.php` in your browser (e.g., `http://localhost/apsdreamhome/database/`) for a graphical interface to manage backups, updates, and view documentation.

**Note:** Never commit production credentials or real customer data.
