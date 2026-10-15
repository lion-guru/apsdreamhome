<#>
.SYNOPSIS
    Sets up Windows Task Scheduler for AI Cron Jobs
.DESCRIPTION
    Creates scheduled tasks for:
    - Agentic AI Orchestrator (every 5 minutes)
    - AI Auto-Dialer (every 2 minutes during 9AM-8PM)
    - AI Health Check (every 15 minutes)
.NOTES
    Run as Administrator
#>

param(
    [string]$PhpPath = "C:\xampp\php\php.exe",
    [string]$ProjectPath = "C:\xampp\htdocs\apsdreamhome"
)

Write-Host "=== APS Dream Home AI Cron Scheduler Setup ===" -ForegroundColor Cyan
Write-Host "PHP Path: $PhpPath" -ForegroundColor Gray
Write-Host "Project Path: $ProjectPath" -ForegroundColor Gray
Write-Host ""

# Verify PHP exists
if (-not (Test-Path $PhpPath)) {
    Write-Error "PHP not found at $PhpPath. Please update the path."
    exit 1
}

# Verify scripts exist
$scripts = @(
    "cron_agentic_ai.php",
    "cron_ai_autodialer.php", 
    "cron_ai_health.php"
)

foreach ($script in $scripts) {
    $fullPath = Join-Path $ProjectPath "scripts\$script"
    if (-not (Test-Path $fullPath)) {
        Write-Error "Script not found: $fullPath"
        exit 1
    }
    Write-Host "Found: $script" -ForegroundColor Green
}

Write-Host ""

# Function to create/update a scheduled task
function Create-Task {
    param(
        [string]$TaskName,
        [string]$ScriptName,
        [int]$IntervalMinutes,
        [string]$StartTime = "00:00",
        [string]$EndTime = "23:59",
        [bool]$WorkingHoursOnly = $false
    )
    
    $scriptPath = Join-Path $ProjectPath "scripts\$ScriptName"
    $action = New-ScheduledTaskAction -Execute $PhpPath -Argument "`"$scriptPath`"" -WorkingDirectory $ProjectPath
    
    # Create a daily trigger that repeats every N minutes
    $trigger = New-ScheduledTaskTrigger -Daily -At $StartTime
    $trigger.Repetition.Interval = (New-TimeSpan -Minutes $IntervalMinutes)
    $trigger.Repetition.Duration = [TimeSpan]::MaxValue
    
    $settings = New-ScheduledTaskSettingsSet -AllowStartIfOnBatteries -DontStopIfGoingOnBatteries -StartWhenAvailable -RunOnlyIfNetworkAvailable -ExecutionTimeLimit (New-TimeSpan -Hours 1)
    
    $principal = New-ScheduledTaskPrincipal -UserId "SYSTEM" -LogonType ServiceAccount -RunLevel Highest
    
    try {
        # Unregister existing task if exists
        if (Get-ScheduledTask -TaskName $TaskName -ErrorAction SilentlyContinue) {
            Unregister-ScheduledTask -TaskName $TaskName -Confirm:$false
            Write-Host "  Removed existing task: $TaskName" -ForegroundColor Yellow
        }
        
        Register-ScheduledTask -TaskName $TaskName -Action $action -Trigger $trigger -Settings $settings -Principal $principal -Force
        Write-Host "  Created task: $TaskName (every $IntervalMinutes min)" -ForegroundColor Green
    } catch {
        $errorRecord = $_
        $err = $errorRecord.Exception.Message
        $err = $err -replace ':', '_'
        $msg = '  Failed to create task ' + $TaskName + ': ' + $err
        Write-Error $msg
    }
}

Write-Host "Creating scheduled tasks..." -ForegroundColor Cyan

# 1. Agentic AI Orchestrator - Every 5 minutes, 24/7
Create-Task -TaskName "APS_AgenticAI_Orchestrator" -ScriptName "cron_agentic_ai.php" -IntervalMinutes 5

# 2. AI Auto-Dialer - Every 2 minutes (we'll handle working hours in the script)
Create-Task -TaskName "APS_AI_AutoDialer" -ScriptName "cron_ai_autodialer.php" -IntervalMinutes 2

# 3. AI Health Check - Every 15 minutes, 24/7
Create-Task -TaskName "APS_AI_HealthCheck" -ScriptName "cron_ai_health.php" -IntervalMinutes 15

Write-Host ""
Write-Host "=== Verifying Tasks ===" -ForegroundColor Cyan
Get-ScheduledTask -TaskName "APS_*" | Format-Table TaskName, State, @{Name="NextRun"; Expression={(Get-ScheduledTaskInfo -TaskName $_.TaskName).NextRunTime}} -AutoSize

Write-Host ""
Write-Host "=== Manual Test Commands ===" -ForegroundColor Cyan
Write-Host "Test Agentic AI:     & `$PhpPath `"$ProjectPath\scripts\cron_agentic_ai.php`"" -ForegroundColor Gray
Write-Host "Test Auto-Dialer:    & `$PhpPath `"$ProjectPath\scripts\cron_ai_autodialer.php`"" -ForegroundColor Gray
Write-Host "Test Health Check:   & `$PhpPath `"$ProjectPath\scripts\cron_ai_health.php`"" -ForegroundColor Gray

Write-Host ""
Write-Host "=== View Task Logs ===" -ForegroundColor Cyan
Write-Host "Event Viewer > Applications and Services Logs > Microsoft > Windows > TaskScheduler > Operational" -ForegroundColor Gray
Write-Host ""
Write-Host "Done! All AI cron jobs are scheduled." -ForegroundColor Green