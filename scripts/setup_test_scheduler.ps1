<#
.SYNOPSIS
    Setup Windows Task Scheduler for APS Dream Home nightly test suite
    Run as Administrator to install scheduled tasks

.DESCRIPTION
    Creates a nightly task that runs:
    - php testing/master_test_runner.php (10/10 suites)
    - flutter test is NOT scheduled (heavy, run manually)
    - DB counts snapshot (users/plots/leads) to logs/testing/
    - Failure email alert if log contains FAIL

.NOTES
    Must run as Administrator
    Requires PHP in system PATH
#>

param(
    [string]$PhpPath = "C:\xampp\php\php.exe",
    [string]$ProjectPath = "C:\xampp\htdocs\apsdreamhome",
    [switch]$Force = $false
)

$isAdmin = ([Security.Principal.WindowsPrincipal][Security.Principal.WindowsIdentity]::GetCurrent()).IsInRole([Security.Principal.WindowsBuiltInRole]::Administrator)
if (-not $isAdmin) {
    Write-Error "This script must be run as Administrator!"
    exit 1
}
if (-not (Test-Path $PhpPath)) {
    Write-Error "PHP not found at $PhpPath. Please install PHP or update the path."
    exit 1
}
if (-not (Test-Path $ProjectPath)) {
    Write-Error "Project not found at $ProjectPath"
    exit 1
}

$TaskName = "APS Nightly Test Suite"
$TaskPath = "\APS Dream Home\"
$ScriptPath = Join-Path $ProjectPath "scripts\nightly_test_suite.php"

if (-not (Test-Path $ScriptPath)) {
    Write-Error "Nightly script not found: $ScriptPath"
    exit 1
}

$existingTask = Get-ScheduledTask -TaskName $TaskName -TaskPath $TaskPath -ErrorAction SilentlyContinue
if ($existingTask -and -not $Force) {
    Write-Host "Task '$TaskName' already exists. Use -Force to overwrite." -ForegroundColor Yellow
    exit 0
}

$trigger = New-ScheduledTaskTrigger -Daily -At "02:00" -DaysInterval 1
$action = New-ScheduledTaskAction -Execute $PhpPath -Argument "`"$ScriptPath`"" -WorkingDirectory $ProjectPath
$settings = New-ScheduledTaskSettingsSet -AllowStartIfOnBatteries -DontStopIfGoingOnBatteries -StartWhenAvailable -RunOnlyIfNetworkAvailable -Priority BelowNormal

try {
    Register-ScheduledTask -TaskName $TaskName -TaskPath $TaskPath -Action $action -Trigger $trigger -Settings $settings -User "SYSTEM" -RunLevel Highest -Force:$Force
    Write-Host "✅ Created task: $TaskName (daily 2:00 AM)" -ForegroundColor Green
    Write-Host "Verify: Get-ScheduledTask -TaskPath '\APS Dream Home\'" -ForegroundColor Gray
    Write-Host "Run now: Start-ScheduledTask -TaskName 'APS Nightly Test Suite' -TaskPath '\APS Dream Home\'" -ForegroundColor Gray
} catch {
    Write-Error "Failed to create task '$TaskName': $_"
}
