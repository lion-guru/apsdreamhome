# APS Dream Home - Salary Grants Monthly Cron Task Setup
# Run as Administrator

$action = New-ScheduledTaskAction -Execute "C:\xampp\php\php.exe" -Argument "C:\xampp\htdocs\apsdreamhome\scripts\cron_salary_grants.php"
$trigger = New-ScheduledTaskTrigger -Monthly -DaysOfMonth 1 -At 02:00
$settings = New-ScheduledTaskSettingsSet -AllowStartIfOnBatteries -DontStopIfGoingOnBatteries -StartWhenAvailable -RunOnlyIfNetworkAvailable
$principal = New-ScheduledTaskPrincipal -UserId "SYSTEM" -LogonType ServiceAccount -RunLevel Highest
Register-ScheduledTask -TaskName "APS_SalaryGrants_Monthly" -Action $action -Trigger $trigger -Settings $settings -Principal $principal -Description "Monthly salary grant payout processing for APS Dream Home"

Write-Host "Task 'APS_SalaryGrants_Monthly' registered successfully!" -ForegroundColor Green
Write-Host "Run time: 1st of every month at 02:00" -ForegroundColor Cyan
Write-Host "To test manually: php C:\xampp\htdocs\apsdreamhome\scripts\cron_salary_grants.php" -ForegroundColor Cyan