<#
.SYNOPSIS
  MariaDB auto-recovery for APS Dream Home.

.DESCRIPTION
  Detects when MariaDB refuses connections (crash-loop after unclean shutdown,
  typically from battery death) and runs the verified Aria repair:

    1. If MariaDB is already listening and answers SELECT 1 → exit 0 (no-op).
    2. Backs up the system tables (mysql/) and Aria log set to
       backups/db/aria_crash_<timestamp>/   (lossless, outside datadir tree).
    3. aria_chk --recover on every mysql/*.MAI (Aria tables);
       columns_priv.MAI uses --safe-recover (aria_sort_buffer_size is tiny).
    4. Deletes stale aria_log* / aria_log_control (already backed up) so Aria
       recreates a pristine log set on startup.
    5. Restarts mysqld with my.ini, waits for port 3306, verifies
       SELECT 1 + key table counts.

.EXAMPLE
  powershell -ExecutionPolicy Bypass -File scripts\mariadb_auto_recovery.ps1

.NOTES
  Safe to run anytime. Prints WHAT it did and WHY. Never deletes user data;
  only system-table Aria files + stale aria logs are touched (backup kept).
#>
param(
    [switch]$Force,
    [switch]$Silent
)

$ErrorActionPreference = 'Stop'
$root        = 'C:\xampp\htdocs\apsdreamhome'
$dbRoot      = 'C:\xampp\mysql\data'
$mysqlBin    = 'C:\xampp\mysql\bin'
$myIni       = 'C:\xampp\mysql\bin\my.ini'
$backupBase  = "C:\xampp\htdocs\apsdreamhome\backups\db"

function Say($msg) { if (-not $Silent) { Write-Host $msg } }

function Test-DbUp {
    try {
        $out = & "$mysqlBin\mysql.exe" -u root apsdreamhome -Nse "SELECT 1" 2>&1
        return ($LASTEXITCODE -eq 0 -and ("$out".Trim() -eq '1'))
    } catch { return $false }
}

Say "[1/5] Checking whether MariaDB is already up..."
if (-not $Force -and (Test-DbUp)) {
    Say "MariaDB already healthy (SELECT 1 OK). Nothing to do."
    exit 0
}

$ts = Get-Date -Format 'yyyyMMdd_HHmm'
Say "[2/5] MariaDB is DOWN. Backing up system tables -> backups\db\aria_crash_$ts"
$backupDir = Join-Path $backupBase "aria_crash_$ts"
New-Item -ItemType Directory -Path (Join-Path $backupDir 'mysql') -Force | Out-Null
robocopy "$dbRoot\mysql" "$backupDir\mysql" /E /R:2 /W:1 | Out-Null
Copy-Item "$dbRoot\aria_log.*","$dbRoot\aria_log_control" $backupDir -Force -ErrorAction SilentlyContinue
Say "Backup at: $backupDir"

Say "[3/5] Running aria_chk --recover on mysql/*.MAI ..."
$failed = @()
Push-Location "$dbRoot\mysql"
Get-ChildItem -Filter '*.MAI' | ForEach-Object {
    $out = & "$mysqlBin\aria_chk.exe" --recover $_.Name 2>&1
    if ($LASTEXITCODE -ne 0) {
        Say "  $($_.Name): rc=$LASTEXITCODE -> retrying with --safe-recover"
        $out = & "$mysqlBin\aria_chk.exe" --safe-recover $_.Name 2>&1
        if ($LASTEXITCODE -ne 0) { $failed += $_.Name }
    }
}
Pop-Location
if ($failed.Count -gt 0) {
    Say "FAILED tables (restore from backup $backupDir): $($failed -join ', ')"
    exit 2
}
Say "All Aria tables repaired."

Say "[4/5] Removing stale aria_log* / aria_log_control (backed up)..."
Remove-Item "$dbRoot\aria_log.*","$dbRoot\aria_log_control" -Force -ErrorAction SilentlyContinue

Say "[5/5] Starting MariaDB and waiting for port 3306..."
$proc = Start-Process "$mysqlBin\mysqld.exe" -ArgumentList "--defaults-file=$myIni" -WindowStyle Hidden -PassThru
$up = $false
for ($i = 0; $i -lt 90 -and -not $up; $i++) {
    Start-Sleep -Milliseconds 500
    $up = [bool](Get-NetTCPConnection -LocalPort 3306 -State Listen -ErrorAction SilentlyContinue)
}
if (-not $up) { Say "MariaDB did not start; inspect $dbRoot\Abhay3007.err"; exit 3 }
if (-not (Test-DbUp)) { Say "Port is up but SELECT 1 failed; inspect $dbRoot\Abhay3007.err"; exit 4 }
$counts = & "$mysqlBin\mysql.exe" -u root apsdreamhome -Nse "SELECT (SELECT COUNT(*) FROM users), (SELECT COUNT(*) FROM plots), (SELECT COUNT(*) FROM leads), (SELECT COUNT(*) FROM colonies), (SELECT COUNT(*) FROM plot_bookings)"
Say "MariaDB RECOVERED. users/plots/leads/colonies/bookings = $counts"
Say "Backup kept at: $backupDir"
exit 0
