# APS Dream Home - Salary Grants Monthly Cron Task Setup
# Run as Administrator

$action = New-ScheduledTaskAction -Execute "C:\xampp\php\php.exe" -Argument "C:\xampp\htdocs\apsdreamhome\scripts\cron_salary_grants.php"

# Create trigger using COM object for monthly schedule (PowerShell 5.1 compatible)
$taskService = New-Object -ComObject "Schedule.Service"
$taskService.Connect()
$taskFolder = $taskService.GetFolder("\")
$taskDefinition = $taskService.NewTask(0)

$action = $taskDefinition.Actions.Create(0)  # TASK_ACTION_EXEC = 0
$action.Path = "C:\xampp\php\php.exe"
$action.Arguments = "C:\xampp\htdocs\apsdreamhome\scripts\cron_salary_grants.php"

$trigger = $taskDefinition.Triggers.Create(4)  # TASK_TRIGGER_MONTHLY = 4
$trigger.StartBoundary = (Get-Date).ToString("yyyy-MM-dd") + "T02:00:00"
$trigger.DaysOfMonth = 1  # 1st of month
$trigger.Enabled = $true

$taskDefinition.Settings.AllowStartIfOnBatteries = $true
$taskDefinition.Settings.DontStopIfGoingOnBatteries = $true
$taskDefinition.Settings.StartWhenAvailable = $true
$taskDefinition.Settings.RunOnlyIfNetworkAvailable = $true

$taskDefinition.Principal.UserId = "SYSTEM"
$taskDefinition.Principal.LogonType = 5  # TASK_LOGON_SERVICE_ACCOUNT
$taskDefinition.Principal.RunLevel = 1  # TASK_RUNLEVEL_HIGHEST

$taskFolder.RegisterTaskDefinition("APS_SalaryGrants_Monthly", $taskDefinition, 6, "SYSTEM", $null, 5, $null)

Write-Host "Task 'APS_SalaryGrants_Monthly' registered successfully!" -ForegroundColor Green
Write-Host "Run time: 1st of every month at 02:00" -ForegroundColor Cyan
Write-Host "To test manually: php C:\xampp\htdocs\apsdreamhome\scripts\cron_salary_grants.php" -ForegroundColor Cyan