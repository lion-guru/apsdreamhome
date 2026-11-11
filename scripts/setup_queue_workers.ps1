<#>
.SYNOPSIS
    Sets up Windows Task Scheduler tasks for queue workers and cron jobs.

.DESCRIPTION
    Creates scheduled tasks for:
    - Queue workers (notifications, emails, sms, pdf, reports)
    - Cron jobs (salary grants, maintenance, backups, AI health)

.NOTES
    Run as Administrator.
#>

param(
    [switch]$Force
)

$ErrorActionPreference = 'Stop'

Write-Host "=== APS Dream Home - Task Scheduler Setup ===" -ForegroundColor Cyan

# Check if running as Admin
$isAdmin = ([Security.Principal.WindowsPrincipal][Security.Principal.WindowsIdentity]::GetCurrent()).IsInRole([Security.Principal.WindowsBuiltInRole]::Administrator)
if (-not $isAdmin) {
    Write-Error "This script must be run as Administrator!"
    exit 1
}

$phpPath = "C:\xampp\php\php.exe"
$projectRoot = "C:\xampp\htdocs\apsdreamhome"
$scriptsDir = "$projectRoot\scripts"
$phpIni = "C:\xampp\php\php.ini"

if (-not (Test-Path $phpPath)) {
    Write-Error "PHP not found at $phpPath"
    exit 1
}

if (-not (Test-Path $projectRoot)) {
    Write-Error "Project not found at $projectRoot"
    exit 1
}

Write-Host "PHP: $phpPath" -ForegroundColor Green
Write-Host "Project: $projectRoot" -ForegroundColor Green

# Function to create a scheduled task
function Create-Task {
    param(
        [string]$Name,
        [string]$Description,
        [string]$Script,
        [string]$Schedule,
        [string]$Arguments = "",
        [string]$User = "SYSTEM",
        [int]$Priority = 0
    )
    
    $taskName = "APS_$Name"
    
    # Check if task exists
    $existing = schtasks /query /tn $taskName 2>$null
    if ($LASTEXITCODE -eq 0 -and -not $Force) {
        Write-Host "Task '$taskName' already exists. Use -Force to overwrite." -ForegroundColor Yellow
        return
    }
    
    if ($existing -and $Force) {
        schtasks /delete /tn $taskName /f 2>$null
    }
    
    $action = New-ScheduledTaskAction -Execute $phpPath -Argument "-c $script $Arguments"
    $trigger = New-ScheduledTaskTrigger -$Schedule
    $settings = New-ScheduledTaskSettingsSet -AllowStartIfOnBatteries -DontStopIfGoingOnBatteries -StartWhenAvailable -RunOnlyIfNetworkAvailable
    $principal = New-ScheduledTaskPrincipal -UserId $User -LogonType ServiceAccount -RunLevel Highest
    
    try {
        Register-ScheduledTask -TaskName $taskName -Action $action -Trigger $trigger -Settings $settings -Principal $principal -Description $Description -Force
        Write-Host "✅ Created task: $taskName" -ForegroundColor Green
    } catch {
        Write-Error "Failed to create task $taskName: $_"
    }
}

# Queue Workers (run every 5 minutes)
Write-Host "`n=== Creating Queue Worker Tasks ===" -ForegroundColor Cyan

$queueTransports = @('notifications', 'emails', 'sms', 'pdf', 'reports')
foreach ($transport in $queueTransports) {
    Create-Task -Name "Queue_$transport" `
        -Description "Queue worker for $transport transport" `
        -Script "$scriptsDir\queue_worker.php" `
        -Schedule "Once -At (Get-Date).AddMinutes(1) -RepetitionInterval (New-TimeSpan -Minutes 5) -RepetitionDuration ([TimeSpan]::MaxValue)" `
        -Arguments $transport
}

# Cron Jobs
Write-Host "`n=== Creating Cron Job Tasks ===" -ForegroundColor Cyan

# Salary Grants - 1st of month at 02:00
Create-Task -Name "Cron_SalaryGrants" `
    -Description "Monthly salary grants processing" `
    -Script "$scriptsDir\cron_salary_grants.php" `
    -Schedule "Monthly -DaysOfMonth 1 -At 02:00"

# Maintenance Dues - Daily at 03:00
Create-Task -Name "Cron_MaintenanceDues" `
    -Description "Generate monthly maintenance dues" `
    -Script "$scriptsDir\cron_maintenance_dues.php" `
    -Schedule "Daily -At 03:00"

# AI Health Check - Every 15 minutes
Create-Task -Name "Cron_AIHealth" `
    -Description "AI health check and monitoring" `
    -Script "$scriptsDir\cron_ai_health.php" `
    -Schedule "Once -At (Get-Date).AddMinutes(1) -RepetitionInterval (New-TimeSpan -Minutes 15) -RepetitionDuration ([TimeSpan]::MaxValue)"

# AI Auto-dialer - Every 2 minutes (9AM-8PM)
Create-Task -Name "Cron_AIAutoDialer" `
    -Description "AI auto-dialer for outbound calls" `
    -Script "$scriptsDir\cron_ai_autodialer.php" `
    -Schedule "Once -At 09:00 -RepetitionInterval (New-TimeSpan -Minutes 2) -RepetitionDuration (New-TimeSpan -Hours 11)"

# Backup - Daily at 00:30
Create-Task -Name "Cron_Backup" `
    -Description "Nightly database backup" `
    -Script "$scriptsDir\backup_db.ps1" `
    -Schedule "Daily -At 00:30"

# Notification Archiving - Daily at 01:00
Create-Task -Name "Cron_NotificationArchive" `
    -Description "Archive read notifications older than 90 days" `
    -Script "$scriptsDir\cron_archive_notifications.php" `
    -Schedule "Daily -At 01:00"

# Low Stock Alerts - Daily at 06:00
Create-Task -Name "Cron_LowStock" `
    -Description "Check for low stock alerts" `
    -Script "$scriptsDir\cron_low_stock_alert.php" `
    -Schedule "Daily -At 06:00"

# Overdue Demand Reminders - Daily at 07:00
Create-Task -Name "Cron_OverdueDemands" `
    -Description "Send overdue demand reminders" `
    -Script "$scriptsDir\cron_overdue_demand_reminders.php" `
    -Schedule "Daily -At 07:00"

Write-Host "`n=== Task Scheduler Setup Complete ===" -ForegroundColor Cyan
Write-Host "All tasks created. View with: schtasks /query /fo TABLE" -ForegroundColor Green
Write-Host "Test a task with: schtasks /run /tn APS_Queue_notifications" -ForegroundColor Green