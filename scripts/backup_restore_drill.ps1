<#
.SYNOPSIS
    Backup-Restore Drill (non-destructive) for APS Dream Home
    - Takes safety dump to backups/db/
    - Verifies counts (users/plots/leads) + master test 10/10
    - Does NOT overwrite live DB (full restore is owner call with RESTORE PROTOCOL)
.EXAMPLE
    powershell -ExecutionPolicy Bypass -File scripts/backup_restore_drill.ps1
#>
param([switch]$FullRestore = $false)
$root = "C:\xampp\htdocs\apsdreamhome"
$backupDir = Join-Path $root "backups\db"
if (-not (Test-Path $backupDir)) { New-Item -ItemType Directory $backupDir -Force | Out-Null }
$stamp = Get-Date -Format "yyyy-MM-dd_HH-mm"
$dump = Join-Path $backupDir "apsdreamhome_pre-restore_$stamp.sql"
Write-Host "Taking safety dump: $dump" -ForegroundColor Cyan
& "C:\xampp\mysql\bin\mysqldump.exe" -u root apsdreamhome --single-transaction --quick --routines --triggers > $dump
if (-not (Test-Path $dump) -or (Get-Item $dump).Length -lt 1MB) {
    Write-Error "Dump failed or too small"
    exit 1
}
Write-Host ("Dump size: {0:N2} MB" -f ((Get-Item $dump).Length / 1MB)) -ForegroundColor Green

# Verify counts (Rule 1 step 3 — verify after any restore, here pre-restore baseline)
Write-Host "Verifying baseline counts..." -ForegroundColor Cyan
Set-Location $root
$countOut = & "C:\xampp\mysql\bin\mysql.exe" -u root apsdreamhome -e "SELECT (SELECT COUNT(*) FROM users) as users, (SELECT COUNT(*) FROM plots) as plots, (SELECT COUNT(*) FROM leads) as leads;"
Write-Host $countOut

Write-Host "Running master tests (10/10 gate)..." -ForegroundColor Cyan
& php testing/master_test_runner.php
if ($LASTEXITCODE -ne 0) { Write-Error "Master tests failed"; exit $LASTEXITCODE }

if ($FullRestore) {
    Write-Warning "Full restore NOT executed (owner must announce + witness per RESTORE PROTOCOL)."
} else {
    Write-Host "Drill PASS (dump + verify, no live restore). For full restore drill, announce window first, then restore $dump and re-verify counts + 10/10." -ForegroundColor Green
}
