# Manual Command Execution Guide - APS Dream Home

This guide provides instructions on how to manually execute various system commands and scripts.

## 1. Where to run commands?
Always open your terminal (PowerShell or Command Prompt) in the project root directory:
`C:\xampp\htdocs\apsdreamhome`

## 2. Dependency Management (Composer)
If you add new packages or need to update existing ones:
- **Update all packages**: `php composer.phar update`
- **Install packages from lock file**: `php composer.phar install`
- **Dump Autoload**: `php composer.phar dump-autoload`

## 3. Cron Jobs (Manual Trigger)
Use these to manually run scheduled tasks for testing or immediate updates:
- **Admin Reports**: `php admin/auto_admin_report_cron.php`
- **Database Backup**: `php admin/auto_backup_cron.php`
- **Lead Reminders**: `php admin/auto_lead_reminder_cron.php`
- **Revenue/Commission Calculation**: `php admin/auto_revenue_commission_cron.php`

## 4. Database Operations
- **Table Validation**: `php check_payout_tables.php`
- **Manual Migration**: `php includes/db_migrate.php` (if available)

## 5. Security and Utilities
- **Check PHP Syntax Errors**: `php -l path/to/file.php`
- **Clear Cache/Logs**: `rm admin/logs/*.log` (Requires Safe Rm Alias if enabled)

## 6. Safe Rm Alias (PowerShell)
To prevent accidental file deletion, ensure you have set up the alias in your `$PROFILE`:
```powershell
# Add this to your PowerShell $PROFILE
function Remove-ItemSafe {
    param([Parameter(ValueFromPipeline=$true, Position=0)][string[]]$Path)
    Remove-Item -Path $Path -Confirm
}
Set-Alias rm Remove-ItemSafe -Option ReadOnly -Force
```
After adding this, `rm` will always ask for confirmation.
