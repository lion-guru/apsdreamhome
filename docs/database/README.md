# Database Documentation

Project databases power everything from customer onboarding to AI analytics. Use this guide to locate canonical schema assets, understand major table groups, and keep environments aligned.

## Canonical Assets

| Asset | Location | Purpose |
| ----- | -------- | ------- |
| **Baseline schema** | `database/00_schema/apsdreamhome_schema.sql` | Snapshot of the normalized production schema. Apply first when standing up a new environment. |
| **Baseline seed data** | `database/00_schema/apsdreamhome_data.sql` | Core lookup/seeding set (roles, property types, demo users). Optional for production. |
| **Versioned migrations** | `database/01_migrations/` | Incremental changes after the baseline snapshot. Run chronologically (filename timestamps). |
| **Historical archives** | `database/archive/` | Legacy exports and superseded migrations kept for reference only. Do not apply directly without review. |

> **Source of truth:** keep migrations in Git. If the database diverges from the latest schema file, generate a fresh schema dump and update `00_schema/` plus add forward-only migrations.

## Core Table Groups

| Domain | Representative tables | Notes |
| ------ | --------------------- | ----- |
| **Identity & security** | `users`, `roles`, `user_roles`, `user_meta`, `password_resets`, `activity_logs` | Handles authentication, RBAC, and audit trail. |
| **Inventory & content** | `properties`, `property_types`, `property_features`, `property_images`, `projects`, `project_media` | Surfaces listings, multimedia, and marketing metadata. |
| **Engagement pipeline** | `leads`, `lead_sources`, `contact_requests`, `appointments`, `property_visits`, `notifications` | Tracks prospect interactions from inquiry to viewing. |
| **Customers & finance** | `customers`, `kyc_documents`, `customer_ledger`, `transactions`, `invoices`, `payments`, `commissions` | Governs customer lifecycle, receivables, and agent payouts. |
| **Operations** | `tasks`, `support_tickets`, `workflows`, `system_settings`, `cron_jobs` | Internal tooling, configuration, and automation references. |
| **Human resources** | `employees`, `attendance`, `leave_requests`, `payroll_runs`, `salary_slabs` | Employee management and payroll processing. |
| **Analytics & AI** | `analytics_reports`, `ai_recommendations`, `chatbot_sessions`, `lead_scores` | Optional modules; apply seeds only when enabling analytics features. |

Refer to the [ER diagram](er-diagram.md) for relationship-level detail.

## Environment Setup Workflow

1. **Create database**: provision a UTF-8 database matching the environment name (e.g., `apsdreamhome_dev`).
2. **Apply baseline schema**:

   ```bash
   mysql -u <user> -p <db_name> < database/00_schema/apsdreamhome_schema.sql
   ```

3. **Apply migrations** (if any beyond the baseline):

   ```bash
   for file in database/01_migrations/*.sql; do
       mysql -u <user> -p <db_name> < "$file"
   done
   ```

4. **Seed data** (optional):

   ```bash
   mysql -u <user> -p <db_name> < database/00_schema/apsdreamhome_data.sql
   ```

5. **Environment secrets**: update `.env`/`config.php` values for database credentials, caching, and third-party integrations.

## Verification Checklist

- Run `database/tools/db_health_report.php` to confirm connectivity and critical counts.
- Log into the admin panel and ensure dashboards load without SQL errors.
- Execute smoke queries (e.g., property search, lead creation) and confirm inserts succeed.
- Check for pending migrations by comparing schema version tables (if implemented) or reviewing migration logs.

## Maintenance & Backups

- **Backups**: schedule daily `mysqldump` exports; replicate to secure offsite storage (S3/Drive).
- **Index health**: use `ANALYZE TABLE` monthly on high-traffic tables (`properties`, `leads`, `transactions`).
- **Archival strategy**: move historical rows (e.g., inactive leads > 18 months) into archive tables or cold storage to keep OLTP performance high.
- **Monitoring**: integrate slow query log review into monthly ops cadence; cross-reference with application profiling.

## Common Queries & Utilities

```sql
-- Active property listings joined with type metadata
SELECT p.*, pt.name AS property_type
FROM properties AS p
JOIN property_types AS pt ON p.type_id = pt.id
WHERE p.status = 'active';

-- Customer ledger snapshot (descending chronologically)
SELECT c.name,
       cl.transaction_type,
       cl.amount,
       cl.balance_after,
       cl.transaction_date
FROM customer_ledger AS cl
JOIN customers AS c ON cl.customer_id = c.id
WHERE c.id = :customer_id
ORDER BY cl.transaction_date DESC;
```

Store additional reusable queries or stored procedure references under `docs/database/snippets/` if specialised workflows emerge.

## Related Documentation

- [ER Diagram](er-diagram.md) – tabular representation of primary entities and relationships.
- [Database Migration Guide](migration-guide.md) – step-by-step instructions for cloning or upgrading environments.
- [Deployment & Operations Handbook](../deployment/README.md) – environment-level runbooks including backup and monitoring practices.
