<#Requires -Version 5.1
<#
.SYNOPSIS
    Registers a Windows Scheduled Task that runs the APS Dream Home daily backup
    at 02:00 (every day).

.DESCRIPTION
    Creates / replaces a task named APS_DailyBackup that executes:
        C:\xampp\php\php.exe C:\xampp\htdocs\apsdreamhome\scripts\backup_cron.php
    The task runs as the current user, with highest privileges, and does NOT
    require an interactive logon. Output is written to storage/logs/backup_cron.log

.PARAMETER PhpPath
    Path to php.exe. Defaults to C:\xampp\php\php.exe

.PARAMETER ScriptPath
    Path to the backup cron script. Defaults to
    C:\xampp\htdocs\apsdreamhome\scripts\backup_cron.php

.PARAMETER Time
    Time of day to run, 24h HH:mm. Defaults to 02:00

.PARAMETER TaskName
    Name of the Windows task. Defaults to APS_DailyBackup

.EXAMPLE
    powershell -ExecutionPolicy Bypass -File scripts\setup_backup_cron.ps1
    powershell -ExecutionPolicy Bypass -File scripts\setup_backup_cron.ps1 -Time "03:30"
#>

[CmdletBinding()]
param(
    [string]$PhpPath    = 'C:\xampp\php\php.exe',
    [string]$ScriptPath = 'C:\xampp\htdocs\apsdreamhome\scripts\backup_cron.php',
    [string]$Time       = '02:00',
    [string]$TaskName   = 'APS_DailyBackup'
)

$ErrorActionPreference = 'Stop'

Write-Host ''
Write-Host '=== APS Dream Home — Backup Cron Setup ===' -ForegroundColor Cyan
Write-Host ("PhpPath    : {0}" -f $PhpPath)
Write-Host ("ScriptPath : {0}" -f $ScriptPath)
Write-Host ("Time       : {0}" -f $Time)
Write-Host ("TaskName   : {0}" -f $TaskName)
Write-Host ''

# Sanity checks
if (-not (Test-Path -LiteralPath $PhpPath)) {
    Write-Host "FATAL: php.exe not found at $PhpPath" -ForegroundColor Red
    exit 1
}
if (-not (Test-Path -LiteralPath $ScriptPath)) {
    Write-Host "FATAL: cron script not found at $ScriptPath" -ForegroundColor Red
    exit 1
}

# Validate time format
try {
    $dt = [datetime]::ParseExact($Time, 'HH:mm', [System.Globalization.CultureInfo]::InvariantCulture)
} catch {
    Write-Host "FATAL: invalid time '$Time' (expected HH:mm, e.g. 02:00)" -ForegroundColor Red
    exit 1
}

# Remove existing task with the same name (idempotent re-install)
$existing = Get-ScheduledTask -TaskName $TaskName -ErrorAction SilentlyContinue
if ($existing) {
    Write-Host "Removing existing task '$TaskName'..." -ForegroundColor Yellow
    Unregister-ScheduledTask -TaskName $TaskName -Confirm:$false
}

# Create the action: php.exe <script>
$action = New-ScheduledTaskAction `
    -Execute $PhpPath `
    -Argument "`"$ScriptPath`"" `
    -WorkingDirectory (Split-Path -Parent $ScriptPath)

# Daily trigger at HH:mm
$trigger = New-ScheduledTaskTrigger -Daily -At $Time

# Settings: allow on battery, run as soon as possible if missed
$settings = New-ScheduledTaskSettingsSet `
    -AllowStartIfOnBatteries `
    -DontStopIfGoingOnBatteries `
    -StartWhenAvailable `
    -ExecutionTimeLimit (New-TimeSpan -Hours 2) `
    -MultipleInstances IgnoreNew

# Principal: run as current user with highest privileges
$principal = New-ScheduledTaskPrincipal `
    -UserId ([System.Security.Principal.WindowsIdentity]::GetCurrent().Name) `
    -LogonType S4U `
    -RunLevel Highest

try {
    Register-ScheduledTask `
        -TaskName $TaskName `
        -Action $action `
        -Trigger $trigger `
        -Settings $settings `
        -Principal $principal `
        -Description "Automated DB backup for APS Dream Home. Runs daily at $Time. See storage\logs\backup_cron.log" `
        -Force | Out-Null
}
catch {
    Write-Host "FATAL: Register-ScheduledTask failed: $_" -ForegroundColor Red
    exit 1
}

Write-Host ""
Write-Host "Scheduled task '$TaskName' installed successfully." -ForegroundColor Green
Write-Host ""
Write-Host "Verify with:    Get-ScheduledTask -TaskName $TaskName" -ForegroundColor Gray
Write-Host "Test run with:  Start-ScheduledTask -TaskName $TaskName" -ForegroundColor Gray
Write-Host "Remove with:    Unregister-ScheduledTask -TaskName $TaskName -Confirm:`$false" -ForegroundColor Gray
Write-Host ""

# Print next run time
$task = Get-ScheduledTask -TaskName $TaskName
$info = $task | Get-ScheduledTaskInfo
if ($info) {
    Write-Host ("Next run: {0}" -f $info.NextRunTime) -ForegroundColor Cyan
}
exit 0
