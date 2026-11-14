# APS Dream Home - AI Auto-Dialer Task Setup
# Run as Administrator: powershell -ExecutionPolicy Bypass -File scripts\setup_ai_autodialer_task.ps1
# Schedules the AI outbound-call dialer every 5 minutes, 09:00-20:00 (Mon-Sun).
# DRY-RUN ONLY by default: add -Execute to pass the --execute flag (real dialing).

param([switch]$Execute)

$php = 'C:\xampp\php\php.exe'
$script = 'C:\xampp\htdocs\apsdreamhome\scripts\cron_ai_autodialer.php'
$taskName = $Execute ? 'APS_AIAutoDialer_Execute' : 'APS_AIAutoDialer_DryRun'
if (-not (Test-Path $php)) { $php = 'php.exe' }
if (-not (Test-Path $script)) { Write-Error "Not found: $script"; exit 1 }

$taskService = New-Object -ComObject "Schedule.Service"
$taskService.Connect()
$taskFolder = $taskService.GetFolder("\")

$taskDefinition = $taskService.NewTask(0)
$action = $taskDefinition.Actions.Create(0) # TASK_ACTION_EXEC = 0
$action.Path = $php
$action.Arguments = $Execute ? '"cron_ai_autodialer.php" --execute' : '"cron_ai_autodialer.php"'
$action.WorkingDirectory = 'C:\xampp\htdocs\apsdreamhome\scripts'

$trigger = $taskDefinition.Triggers.Create(2) # TASK_TRIGGER_DAILY = 2
$trigger.StartBoundary = (Get-Date).ToString("yyyy-MM-dd") + "T09:00:00"
$trigger.DaysInterval = 1
$trigger.Enabled = $true

# Repeat every 5 minutes for the whole day; business-hours enforced inside the script (9-20) unless --force
$settings = $taskDefinition.Settings
$settings.AllowStartIfOnBatteries = $true
$settings.DontStopIfGoingOnBatteries = $true
$settings.StartWhenAvailable = $true
$settings.ExecutionTimeLimit = "PT10M"
$settings.MultipleInstances = 2 # TASK_INSTANCES_IGNORE_NEW
# Trigger repetition: every 5 minutes for 24h (script self-gates to 09:00-20:00)
$trigger.Repetition.Interval = "PT5M"
$trigger.Repetition.Duration = "P1D"

$taskDefinition.Principal.UserId = "SYSTEM"
$taskDefinition.Principal.LogonType = 5  # TASK_LOGON_SERVICE_ACCOUNT
$taskDefinition.Principal.RunLevel = 1   # TASK_RUNLEVEL_HIGHEST

$taskFolder.RegisterTaskDefinition($taskName, $taskDefinition, 6, "SYSTEM", $null, 5, $null)
if ($Execute) {
    Write-Host "Task '$taskName' registered (LIVE DIALING, every 5 minutes)." -ForegroundColor Red
    Write-Host "Revert with: schtasks /Delete /TN $taskName /F" -ForegroundColor Yellow
} else {
    Write-Host "Task '$taskName' registered (DRY-RUN every 5 minutes)." -ForegroundColor Green
    Write-Host "When you are ready for live dialing: setup_ai_autodialer_task.ps1 -Execute" -ForegroundColor Cyan
}
Write-Host "Verify with: schtasks /Query /TN $taskName /V /FO LIST"
