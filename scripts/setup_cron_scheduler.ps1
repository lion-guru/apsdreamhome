<#
.SYNOPSIS
    Setup Windows Task Scheduler for APS Dream Home cron jobs
    Run as Administrator to install scheduled tasks

.DESCRIPTION
    Creates scheduled tasks for all critical cron jobs:
    - cron_lead_scoring.php (every 15 min)
    - cron_queue_worker.php (every 2 min)
    - cron_backup_verify.php (daily 3 AM)
    - cron_ai_health.php (every 15 min)
    - cron_ai_autodialer.php (every 2 min, 9AM-8PM)
    - cron_agentic_ai.php (every 5 min)
    - cron_salary_grants.php (monthly 1st at 2 AM)

.NOTES
    Must run as Administrator
    Requires PHP to be in system PATH
#>

param(
    [string]$PhpPath = "C:\xampp\php\php.exe",
    [string]$ProjectPath = "C:\xampp\htdocs\apsdreamhome",
    [switch]$Force = $false
)

# Check if running as Administrator
$isAdmin = ([Security.Principal.WindowsPrincipal][Security.Principal.WindowsIdentity]::GetCurrent()).IsInRole([Security.Principal.WindowsBuiltInRole]::Administrator)
if (-not $isAdmin) {
    Write-Error "This script must be run as Administrator!"
    exit 1
}

# Verify PHP exists
if (-not (Test-Path $PhpPath)) {
    Write-Error "PHP not found at $PhpPath. Please install PHP or update the path."
    exit 1
}

# Verify project path
if (-not (Test-Path $ProjectPath)) {
    Write-Error "Project not found at $ProjectPath"
    exit 1
}

$tasks = @(
    @{
        Name = "APS Lead Scoring"
        Path = "\APS Dream Home\Lead Scoring"
        Script = "scripts\cron_lead_scoring.php"
        Schedule = "Every 15 minutes"
        Trigger = @{
            Repetition = @{ Interval = "PT15M"; Duration = "P1D" }
        }
    },
    @{
        Name = "APS Queue Worker"
        Path = "\APS Dream Home\Queue Worker"
        Script = "scripts\cron_queue_worker.php"
        Schedule = "Every 2 minutes"
        Trigger = @{
            Repetition = @{ Interval = "PT2M"; Duration = "P1D" }
        }
    },
    @{
        Name = "APS Backup Verify"
        Path = "\APS Dream Home\Backup Verify"
        Script = "scripts\cron_backup_verify.php"
        Schedule = "Daily at 3:00 AM"
        Trigger = @{
            Daily = @{ DaysInterval = 1; At = "03:00" }
        }
    },
    @{
        Name = "APS AI Health Check"
        Path = "\APS Dream Home\AI Health"
        Script = "scripts\cron_ai_health.php"
        Schedule = "Every 15 minutes"
        Trigger = @{
            Repetition = @{ Interval = "PT15M"; Duration = "P1D" }
        }
    },
    @{
        Name = "APS AI Auto-Dialer"
        Path = "\APS Dream Home\AI Auto-Dialer"
        Script = "scripts\cron_ai_autodialer.php"
        Schedule = "Every 2 minutes (9AM-8PM)"
        Trigger = @{
            Repetition = @{ Interval = "PT2M"; Duration = "P1D" }
        }
        # Note: The script itself checks working hours (9AM-8PM)
    },
    @{
        Name = "APS Agentic AI"
        Path = "\APS Dream Home\Agentic AI"
        Script = "scripts\cron_agentic_ai.php"
        Schedule = "Every 5 minutes"
        Trigger = @{
            Repetition = @{ Interval = "PT5M"; Duration = "P1D" }
        }
    },
    @{
        Name = "APS Salary Grants"
        Path = "\APS Dream Home\Salary Grants"
        Script = "scripts\cron_salary_grants.php"
        Schedule = "Monthly on 1st at 2:00 AM"
        Trigger = @{
            Monthly = @{ DaysOfMonth = "1"; Months = "1,2,3,4,5,6,7,8,9,10,11,12"; At = "02:00" }
        }
    }
)

function New-Task {
    param(
        [string]$TaskName,
        [string]$TaskPath,
        [string]$ScriptPath,
        [hashtable]$Trigger,
        [string]$PhpPath,
        [string]$ProjectPath,
        [switch]$Force
    )

    $fullScriptPath = Join-Path $ProjectPath $ScriptPath
    
    if (-not (Test-Path $fullScriptPath)) {
        Write-Warning "Script not found: $fullScriptPath"
        return
    }

    # Check if task exists
    $existingTask = Get-ScheduledTask -TaskName $TaskName -TaskPath $TaskPath -ErrorAction SilentlyContinue
    if ($existingTask -and -not $Force) {
        Write-Host "Task '$TaskName' already exists. Use -Force to overwrite." -ForegroundColor Yellow
        return
    }

    # Create trigger
    $trigger = $null
    if ($Trigger.Repetition) {
        $trigger = New-ScheduledTaskTrigger -Once -At (Get-Date).ToString("HH:mm") -RepetitionInterval $Trigger.Repetition.Interval -RepetitionDuration $Trigger.Repetition.Duration
    } elseif ($Trigger.Daily) {
        $trigger = New-ScheduledTaskTrigger -Daily -At $Trigger.Daily.At -DaysInterval $Trigger.Daily.DaysInterval
    } elseif ($Trigger.Monthly) {
        $trigger = New-ScheduledTaskTrigger -Monthly -DaysOfMonth $Trigger.Monthly.DaysOfMonth -Months $Trigger.Monthly.Months -At $Trigger.Monthly.At
    }

    if (-not $trigger) {
        Write-Error "Failed to create trigger for $TaskName"
        return
    }

    # Create action
    $action = New-ScheduledTaskAction -Execute $PhpPath -Argument "`"$fullScriptPath`"" -WorkingDirectory $ProjectPath

    # Create settings
    $settings = New-ScheduledTaskSettingsSet `
        -AllowStartIfOnBatteries `
        -DontStopIfGoingOnBatteries `
        -StartWhenAvailable `
        -RunOnlyIfNetworkAvailable `
        -Hidden `
        -Priority BelowNormal

    # Register task
    try {
        Register-ScheduledTask `
            -TaskName $TaskName `
            -TaskPath $TaskPath `
            -Action $action `
            -Trigger $trigger `
            -Settings $settings `
            -User "SYSTEM" `
            -RunLevel Highest `
            -Force:$Force
        
        Write-Host "✅ Created task: $TaskName" -ForegroundColor Green
    } catch {
        Write-Error "Failed to create task '$TaskName': $_"
    }
}

Write-Host "Setting up APS Dream Home cron jobs..." -ForegroundColor Cyan
Write-Host "PHP: $PhpPath" -ForegroundColor Gray
Write-Host "Project: $ProjectPath" -ForegroundColor Gray
Write-Host ""

foreach ($task in $tasks) {
    Write-Host "Creating: $($task.Name) [$($task.Schedule)]" -ForegroundColor Cyan
    New-Task `
        -TaskName $task.Name `
        -TaskPath $task.Path `
        -ScriptPath $task.Script `
        -Trigger $task.Trigger `
        -PhpPath $PhpPath `
        -ProjectPath $ProjectPath `
        -Force:$Force
}

Write-Host ""
Write-Host "✅ All tasks created successfully!" -ForegroundColor Green
Write-Host ""
Write-Host "To verify tasks:" -ForegroundColor Cyan
Write-Host "  Get-ScheduledTask -TaskPath '\APS Dream Home\'" -ForegroundColor Gray
Write-Host ""
Write-Host "To run a task manually:" -ForegroundColor Cyan
Write-Host "  Start-ScheduledTask -TaskName 'APS Lead Scoring' -TaskPath '\APS Dream Home\'" -ForegroundColor Gray