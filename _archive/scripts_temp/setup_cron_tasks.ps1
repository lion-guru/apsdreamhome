#!/usr/bin/env pwsh
# Registers 3 daily Windows scheduled tasks for APS Dream Home automation.
# Re-running this script is safe: existing tasks with the same name are replaced
# (Register-ScheduledTask -Force).
#
# Schedule:
#   - APS_DailyBackup       02:00  Full MySQL backup
#   - APS_DailySearchAlerts 09:00  Saved-search email alerts
#   - APS_DailyCompliance   10:00  Booking token compliance check
#
# All three run as the current user with highest privileges; outputs to
# <project_root>\logs\<task>.log (each script owns its own log file).
#
# Usage (PowerShell, from project root):
#   powershell -ExecutionPolicy Bypass -File scripts\setup_cron_tasks.ps1
# Uninstall:
#   powershell -ExecutionPolicy Bypass -File scripts\setup_cron_tasks.ps1 -Uninstall

[CmdletBinding()]
param(
    [switch]$Uninstall
)

$ErrorActionPreference = 'Stop'

$projectRoot = (Resolve-Path "$PSScriptRoot\..").Path
$phpExe      = 'C:\xampp\php\php.exe'

if (-not (Test-Path -LiteralPath $phpExe)) {
    Write-Host "ERROR: PHP executable not found at $phpExe" -ForegroundColor Red
    exit 1
}

$tasks = @(
    @{ Name = 'APS_DailyBackup';        Description = 'Daily full MySQL backup';              Time = '02:00'; Script = Join-Path $projectRoot 'scripts\backup_cron.php' }
    @{ Name = 'APS_DailySearchAlerts';  Description = 'Send saved-search email alerts';       Time = '09:00'; Script = Join-Path $projectRoot 'scripts\daily_alerts_cron.php' }
    @{ Name = 'APS_DailyCompliance';    Description = 'Run booking token compliance checks';  Time = '10:00'; Script = Join-Path $projectRoot 'scripts\cron_daily_compliance.php' }
)

function Remove-ExistingTask([string]$Name) {
    if (Get-ScheduledTask -TaskName $Name -ErrorAction SilentlyContinue) {
        Unregister-ScheduledTask -TaskName $Name -Confirm:$false
        Write-Host "  Removed existing task: $Name"
    }
}

if ($Uninstall) {
    Write-Host "Uninstalling APS Dream Home scheduled tasks..." -ForegroundColor Yellow
    foreach ($task in $tasks) {
        Remove-ExistingTask -Name $task.Name
    }
    Write-Host "Done." -ForegroundColor Green
    exit 0
}

Write-Host "Registering APS Dream Home scheduled tasks..." -ForegroundColor Cyan
foreach ($task in $tasks) {
    if (-not (Test-Path -LiteralPath $task.Script)) {
        Write-Host "  SKIP: $($task.Name) - script not found: $($task.Script)" -ForegroundColor Yellow
        continue
    }

    Remove-ExistingTask -Name $task.Name

    $action  = New-ScheduledTaskAction -Execute $phpExe -Argument "`"$($task.Script)`""
    $trigger = New-ScheduledTaskTrigger -Daily -At $task.Time
    $settings = New-ScheduledTaskSettingsSet -StartWhenAvailable -DontStopIfGoingOnBatteries -ExecutionTimeLimit (New-TimeSpan -Minutes 30)

    Register-ScheduledTask `
        -TaskName $task.Name `
        -Action $action `
        -Trigger $trigger `
        -Settings $settings `
        -Description $task.Description `
        -Force | Out-Null

    Write-Host "  Registered: $($task.Name)  (daily at $($task.Time))" -ForegroundColor Green
}

Write-Host ""
Write-Host "All tasks registered. Verify with:" -ForegroundColor Cyan
Write-Host "  Get-ScheduledTask | Where-Object { `$_.TaskName -like 'APS_*' } | Format-Table TaskName,State"
Write-Host ""
Write-Host "Run a task on-demand with:" -ForegroundColor Cyan
Write-Host "  Start-ScheduledTask -TaskName APS_DailySearchAlerts"
