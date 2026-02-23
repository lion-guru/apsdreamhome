# PowerShell script to schedule automated tasks for APS Dream Homes
$TaskNamePrefix = "APSDreamHome_"
$ProjectRoot = "C:\xampp\htdocs\apsdreamhome"

# Schedule daily backup at 2:00 AM
$backupTrigger = New-ScheduledTaskTrigger -Daily -At 2am
Register-ScheduledTask -TaskName ($TaskNamePrefix + "DailyBackup") -Trigger $backupTrigger -Action (New-ScheduledTaskAction -Execute "bash.exe" -ArgumentList "$ProjectRoot\scripts\backup.sh") -Description "Daily code and DB backup for APS Dream Homes" -Force

# Schedule duplicate scan every Sunday at 3:00 AM
$dupTrigger = New-ScheduledTaskTrigger -Weekly -DaysOfWeek Sunday -At 3am
Register-ScheduledTask -TaskName ($TaskNamePrefix + "WeeklyDuplicateScan") -Trigger $dupTrigger -Action (New-ScheduledTaskAction -Execute "powershell.exe" -ArgumentList "-File $ProjectRoot\scripts\ci_cd.ps1") -Description "Weekly duplicate file scan and CI/CD pipeline" -Force

# Schedule health check every day at 4:00 AM
$healthTrigger = New-ScheduledTaskTrigger -Daily -At 4am
Register-ScheduledTask -TaskName ($TaskNamePrefix + "DailyHealthCheck") -Trigger $healthTrigger -Action (New-ScheduledTaskAction -Execute "php.exe" -ArgumentList "$ProjectRoot\admin\health_check.php") -Description "Daily health check for APS Dream Homes" -Force
