# Database Migration Naming Convention

## Current State
Only 3 numbered migrations exist:
- `020_...` 
- `021_...`
- `025_...`

Remaining migrations use ad-hoc names like:
- `create_associates_table.php`
- `drop_associates_table.php`
- `drop_associates_table_with_constraints.php`
- `drop_conflicting_associate_tables.php`
- `consolidate_database_tables.php`
- `analyze_table_conflicts.php`

## Recommended Convention (effective immediately)

**Format**: `YYYYMMDDHHMMSS_description.php`

**Examples**:
- `20260802143000_create_users_table.php`
- `20260802143500_add_tenant_id_to_plots.php`
- `20260802150000_create_commission_ledger_table.php`

**Rules**:
1. Always use 14-digit timestamp prefix (year, month, day, hour, minute, second)
2. Use snake_case for description
3. Be descriptive but concise
4. One migration = one logical change
5. Never rename existing migrations (breaks history)

## associates Table Churn Investigation

The `associates` table has been created/dropped/consolidated 4+ times:
1. `create_associates_table.php`
2. `drop_associates_table.php`
3. `drop_associates_table_with_constraints.php`
4. `drop_conflicting_associate_tables.php`
5. `consolidate_database_tables.php`
6. `analyze_table_conflicts.php`

This indicates recurring schema conflicts. Before any future changes to this table:
- Run `analyze_table_conflicts.php` against current DB
- Confirm current schema matches models/services
- Take fresh backup before any DROP

## Going Forward
- All NEW migrations must follow the timestamp convention
- Document this in project README
- Code review should enforce this standard