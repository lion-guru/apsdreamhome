# Windows Task Scheduler — Monthly Salary Grants Payout

## Purpose
Auto-run `cron_salary_grants.php` on the 1st of every month to process salary grant payouts for all active grants.

## Script
```
C:\xampp\php\php.exe C:\xampp\htdocs\apsdreamhome\scripts\cron_salary_grants.php
```

## Windows Task Scheduler Setup (PowerShell)

```powershell
# Run as Administrator
$action = New-ScheduledTaskAction -Execute "C:\xampp\php\php.exe" -Argument "C:\xampp\htdocs\apsdreamhome\scripts\cron_salary_grants.php"
$trigger = New-ScheduledTaskTrigger -Monthly -DaysOfMonth 1 -At 02:00
$settings = New-ScheduledTaskSettingsSet -AllowStartIfOnBatteries -DontStopIfGoingOnBatteries -StartWhenAvailable -RunOnlyIfNetworkAvailable
$principal = New-ScheduledTaskPrincipal -UserId "SYSTEM" -LogonType ServiceAccount -RunLevel Highest
Register-ScheduledTask -TaskName "APS_SalaryGrants_Monthly" -Action $action -Trigger $trigger -Settings $settings -Principal $principal -Description "Monthly salary grant payout processing for APS Dream Home"
```

## Manual Test
```cmd
C:\xampp\php\php.exe C:\xampp\htdocs\apsdreamhome\scripts\cron_salary_grants.php
```
Expected output: `month=YYYY-MM processed=N amount=X`

## Monitoring
- Logs: Windows Event Viewer → Task Scheduler Library → APS_SalaryGrants_Monthly → History
- DB check: `SELECT * FROM mlm_salary_grant_logs WHERE action='paid' AND payout_month=DATE_FORMAT(NOW(),'%Y-%m')`

## Failure Handling
- Task runs as SYSTEM (no user login required)
- Script is idempotent: re-runs same month never double-pay (payout_month guard)
- On failure: check Event Viewer, run manually to see PHP errors