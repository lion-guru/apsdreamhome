# ============================================================================
# APS Dream Home - Payment Reconciliation Cron Installer (Windows)
# ============================================================================
# Adds a Windows Scheduled Task that runs scripts/payment_reconciliation_cron.php
# every 2 hours during business hours, every 6 hours overnight.
#
# Usage (run PowerShell as Administrator):
#     cd C:\xampp\htdocs\apsdreamhome
#     .\scripts\setup_payment_reconciliation_cron.ps1           # install
#     .\scripts\setup_payment_reconciliation_cron.ps1 -Uninstall  # remove
#     .\scripts\setup_payment_reconciliation_cron.ps1 -RunNow     # run once now
#
# Default schedule:  every 2 hours between 08:00-22:00, every 6 hours overnight
# Override with -Hours '0,6,8,10,12,14,16,18,20,22' (cron-style 24h list)
# ============================================================================

[CmdletBinding()]
param(
    [switch]$Uninstall,
    [switch]$RunNow,
    [string]$TaskName = 'APS_PaymentReconciliation',
    [string]$PhpExe   = 'C:\xampp\php\php.exe',
    [string]$Script   = 'C:\xampp\htdocs\apsdreamhome\scripts\payment_reconciliation_cron.php',
    [string]$Hours    = '0,2,4,6,8,10,12,14,16,18,20,22'
)

$ErrorActionPreference = 'Stop'

function Write-Step($msg) { Write-Host "==> $msg" -ForegroundColor Cyan }
function Write-OK($msg)   { Write-Host " [OK] $msg" -ForegroundColor Green }
function Write-Warn($msg) { Write-Host " [WARN] $msg" -ForegroundColor Yellow }
function Write-Err($msg)  { Write-Host " [ERR] $msg" -ForegroundColor Red }

# ----- Validate prerequisites ---------------------------------------------
if (-not (Test-Path -LiteralPath $PhpExe)) {
    Write-Err "PHP not found at $PhpExe. Override with -PhpExe 'C:\path\to\php.exe'."
    exit 1
}
if (-not (Test-Path -LiteralPath $Script)) {
    Write-Err "Cron script not found at $Script."
    exit 1
}

# ----- Uninstall ----------------------------------------------------------
if ($Uninstall) {
    Write-Step "Uninstalling scheduled task '$TaskName'..."
    $existing = Get-ScheduledTask -TaskName $TaskName -ErrorAction SilentlyContinue
    if ($existing) {
        Unregister-ScheduledTask -TaskName $TaskName -Confirm:$false
        Write-OK "Task removed."
    } else {
        Write-Warn "Task was not registered. Nothing to do."
    }
    exit 0
}

# ----- Run-now helper -----------------------------------------------------
if ($RunNow) {
    Write-Step "Running $Script once now..."
    & $PhpExe $Script @args
    exit $LASTEXITCODE
}

# ----- Install ------------------------------------------------------------
Write-Step "Installing scheduled task '$TaskName'"

# Build a list of daily triggers - one per hour in the $Hours list
$hourList = $Hours -split ',' | ForEach-Object { [int]$_ } | Where-Object { $_ -ge 0 -and $_ -le 23 } | Sort-Object -Unique
if ($hourList.Count -eq 0) {
    Write-Err "Invalid -Hours value '$Hours'. Use comma-separated 0-23 hours, e.g. '0,6,12,18'."
    exit 1
}

$triggers = @()
foreach ($h in $hourList) {
    $triggers += New-ScheduledTaskTrigger -Daily -At "$($h.ToString('00')):00"
}

$action = New-ScheduledTaskAction `
    -Execute $PhpExe `
    -Argument "`"$Script`"" `
    -WorkingDirectory (Split-Path -Parent $Script)

# Run as SYSTEM with highest privileges (most reliable for cron-style jobs)
$principal = New-ScheduledTaskPrincipal `
    -UserId 'SYSTEM' `
    -LogonType ServiceAccount `
    -RunLevel Highest

$settings = New-ScheduledTaskSettingsSet `
    -AllowStartIfOnBatteries `
    -DontStopIfGoingOnBatteries `
    -StartWhenAvailable `
    -ExecutionTimeLimit (New-TimeSpan -Minutes 10) `
    -RestartCount 3 `
    -RestartInterval (New-TimeSpan -Minutes 5)

try {
    Register-ScheduledTask `
        -TaskName $TaskName `
        -Action $action `
        -Trigger $triggers `
        -Principal $principal `
        -Settings $settings `
        -Description "Reconciles Razorpay orders with local payment_orders table. Runs $($hourList.Count)x per day at: $($hourList -join ', '):00. Logs to logs\payment_reconciliation.log" `
        -Force | Out-Null
} catch {
    Write-Err "Failed to register task: $_"
    Write-Host "Hint: re-run PowerShell as Administrator (the task needs to register under SYSTEM)." -ForegroundColor Yellow
    exit 1
}

Write-OK "Task installed successfully."
Write-Host ""
Write-Host "  Name:        $TaskName" -ForegroundColor White
Write-Host "  Schedule:    $($hourList.Count)x per day at hours $($hourList -join ', '):00" -ForegroundColor White
Write-Host "  PHP:         $PhpExe" -ForegroundColor White
Write-Host "  Script:      $Script" -ForegroundColor White
Write-Host "  Working dir: $(Split-Path -Parent $Script)" -ForegroundColor White
Write-Host "  Run as:      SYSTEM (highest privilege)" -ForegroundColor White
Write-Host "  Log:         $Script\..\logs\payment_reconciliation.log" -ForegroundColor White
Write-Host ""

# Verify by listing the task
$verify = Get-ScheduledTask -TaskName $TaskName -ErrorAction SilentlyContinue
if ($verify) {
    Write-OK "Verification: task exists. State = $($verify.State)"
} else {
    Write-Warn "Task not visible via Get-ScheduledTask. May still be registering."
}

Write-Host ""
Write-Host "Next steps:" -ForegroundColor Yellow
Write-Host "  1. Run once now to verify:    .\scripts\setup_payment_reconciliation_cron.ps1 -RunNow"
Write-Host "  2. Tail the log:              Get-Content logs\payment_reconciliation.log -Wait"
Write-Host "  3. Trigger manually:          Start-ScheduledTask -TaskName '$TaskName'"
Write-Host "  4. Remove when done:          .\scripts\setup_payment_reconciliation_cron.ps1 -Uninstall"
