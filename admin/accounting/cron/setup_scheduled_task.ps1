# Create a new scheduled task to run EMI automation daily
$Action = New-ScheduledTaskAction -Execute "C:\xampp\htdocs\apsdreamhome\admin\accounting\cron\schedule_emi_automation.bat"
$Trigger = New-ScheduledTaskTrigger -Daily -At 12am
$Principal = New-ScheduledTaskPrincipal -UserId "SYSTEM" -LogonType ServiceAccount -RunLevel Highest
$Settings = New-ScheduledTaskSettingsSet -MultipleInstances IgnoreNew -ExecutionTimeLimit (New-TimeSpan -Minutes 30)

Register-ScheduledTask -TaskName "APS Dream Home EMI Automation" -Action $Action -Trigger $Trigger -Principal $Principal -Settings $Settings -Description "Runs EMI automation tasks daily including payment reminders and report generation"
