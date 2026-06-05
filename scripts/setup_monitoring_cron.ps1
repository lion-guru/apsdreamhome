# Setup Windows Task Scheduler entry for monitoring_cron.php
# Runs every 5 minutes, logs output to storage/logs/monitoring_cron.log
#
# Usage (one-time, as administrator):
#   powershell -ExecutionPolicy Bypass -File .\scripts\setup_monitoring_cron.ps1
#
# To remove:
#   Unregister-ScheduledTask -TaskName "APS_MonitoringCron" -Confirm:$false

[CmdletBinding()]
param (
    [string]$TaskName    = "APS_MonitoringCron",
    [string]$PhpPath     = "C:\xampp\php\php.exe",
    [string]$ScriptPath  = "C:\xampp\htdocs\apsdreamhome\scripts\monitoring_cron.php",
    [int]   $IntervalMin = 5
)

Write-Host "Setting up APS monitoring cron task..." -ForegroundColor Cyan

if (-not (Test-Path -LiteralPath $PhpPath)) {
    Write-Host "ERROR: PHP not found at $PhpPath" -ForegroundColor Red
    exit 1
}
if (-not (Test-Path -LiteralPath $ScriptPath)) {
    Write-Host "ERROR: Cron script not found at $ScriptPath" -ForegroundColor Red
    exit 1
}

try {
    $existing = Get-ScheduledTask -TaskName $TaskName -ErrorAction SilentlyContinue
    if ($existing) {
        Write-Host "Removing existing task '$TaskName'..." -ForegroundColor Yellow
        Unregister-ScheduledTask -TaskName $TaskName -Confirm:$false
    }

    $action = New-ScheduledTaskAction `
        -Execute $PhpPath `
        -Argument "`"$ScriptPath`""

    $trigger = New-ScheduledTaskTrigger `
        -Once -At (Get-Date) `
        -RepetitionInterval (New-TimeSpan -Minutes $IntervalMin)

    $settings = New-ScheduledTaskSettingsSet `
        -AllowStartIfOnBatteries `
        -DontStopIfGoingOnBatteries `
        -StartWhenAvailable `
        -ExecutionTimeLimit (New-TimeSpan -Minutes 4)

    $principal = New-ScheduledTaskPrincipal `
        -UserId "SYSTEM" `
        -LogonType ServiceAccount `
        -RunLevel Highest

    Register-ScheduledTask `
        -TaskName $TaskName `
        -Action $action `
        -Trigger $trigger `
        -Settings $settings `
        -Principal $principal `
        -Description "APS Dream Home monitoring health check (every $IntervalMin min)" `
        | Out-Null

    Write-Host "[OK] Task '$TaskName' registered. Runs every $IntervalMin minutes." -ForegroundColor Green
    Write-Host "     Logs: C:\xampp\htdocs\apsdreamhome\storage\logs\monitoring_cron.log" -ForegroundColor Gray
    Write-Host ""
    Write-Host "To verify: Get-ScheduledTask -TaskName '$TaskName'" -ForegroundColor Gray
    Write-Host "To remove: Unregister-ScheduledTask -TaskName '$TaskName' -Confirm:`$false" -ForegroundColor Gray
}
catch {
    Write-Host "ERROR: $($_.Exception.Message)" -ForegroundColor Red
    Write-Host "Run this script as Administrator." -ForegroundColor Yellow
    exit 1
}
