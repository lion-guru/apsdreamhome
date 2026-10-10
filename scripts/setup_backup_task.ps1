# APS Dream Home - Nightly Database Backup Task Setup
# Run as Administrator: powershell -ExecutionPolicy Bypass -File scripts\setup_backup_task.ps1
# Schedules scripts\backup_db.bat every night at 00:00 (timestamped dump into backups\db)

$taskService = New-Object -ComObject "Schedule.Service"
$taskService.Connect()
$taskFolder = $taskService.GetFolder("\")
$taskDefinition = $taskService.NewTask(0)

$action = $taskDefinition.Actions.Create(0)  # TASK_ACTION_EXEC = 0
$action.Path = "C:\xampp\htdocs\apsdreamhome\scripts\backup_db.bat"
$action.WorkingDirectory = "C:\xampp\htdocs\apsdreamhome\scripts"

$trigger = $taskDefinition.Triggers.Create(2)  # TASK_TRIGGER_DAILY = 2
$trigger.StartBoundary = (Get-Date).ToString("yyyy-MM-dd") + "T00:00:00"
$trigger.DaysInterval = 1
$trigger.Enabled = $true

$taskDefinition.Settings.AllowStartIfOnBatteries = $true
$taskDefinition.Settings.DontStopIfGoingOnBatteries = $true
$taskDefinition.Settings.StartWhenAvailable = $true
$taskDefinition.Settings.ExecutionTimeLimit = "PT2H"

$taskDefinition.Principal.UserId = "SYSTEM"
$taskDefinition.Principal.LogonType = 5  # TASK_LOGON_SERVICE_ACCOUNT
$taskDefinition.Principal.RunLevel = 1  # TASK_RUNLEVEL_HIGHEST

$taskFolder.RegisterTaskDefinition("APS_DBBackup_Nightly", $taskDefinition, 6, "SYSTEM", $null, 5, $null)

Write-Host "Task 'APS_DBBackup_Nightly' registered successfully!" -ForegroundColor Green
Write-Host "Run time: every night at 00:00" -ForegroundColor Cyan
Write-Host "Dumps land in: C:\xampp\htdocs\apsdreamhome\backups\db" -ForegroundColor Cyan
Write-Host "To test manually: scripts\backup_db.bat" -ForegroundColor Cyan
