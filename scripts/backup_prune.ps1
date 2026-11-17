# backup_prune.ps1 - Prune old automatic DB dumps from backups/db with restore-safety.
#
# Policy:
#   - Never deletes NAMED safety dumps (anything containing '_pre-' or '_pre_' or 'pre_' or 'pre-restore')
#     — those are rollback anchors tied to specific sessions.
#   - Deletes only generic scheduled dumps matching 'apsdreamhome_YYYY-MM-DD_HH-mm-ss.sql'
#     older than -Days (default 30).
#   - Keeps at least -KeepLatest newest generic dumps regardless of age (default 3).
#   - DRY-RUN by default: prints what would be deleted; nothing is deleted without -Delete.
#
# Usage:
#   powershell -ExecutionPolicy Bypass -File scripts\backup_prune.ps1            # inventory + plan
#   powershell -ExecutionPolicy Bypass -File scripts\backup_prune.ps1 -Delete     # execute the plan
#   powershell -ExecutionPolicy Bypass -File scripts\backup_prune.ps1 -Days 14    # tighter window
#   powershell -ExecutionPolicy Bypass -File scripts\backup_prune.ps1 -Days 90    # keep longer
#
# Exit codes: 0 ok, 1 bad params, 2 target dir missing.

param(
    [int]$Days = 30,
    [int]$KeepLatest = 3,
    [switch]$Delete
)

$root   = Split-Path -Parent $PSScriptRoot
$dbDir  = Join-Path $root 'backups\db'
if (-not (Test-Path $dbDir)) { Write-Error "backups\db not found: $dbDir"; exit 2 }

$files = Get-ChildItem -LiteralPath $dbDir -File | Sort-Object LastWriteTime -Descending
$cutoff = (Get-Date).AddDays(-$Days)
# name constellation: apsdreamhome_2026-11-17_00-00-02.sql (generic scheduled dumps only)
$genericPattern = '^apsdreamhome_\d{4}-\d{2}-\d{2}_\d{2}-\d{2}-\d{2}\.sql$'
$namedKeep   = $files | Where-Object { $_.Name -match 'pre[-_]|pre-restore|current' }
$generic     = $files | Where-Object { $_.Name -match $genericPattern -and $_.Name -notmatch 'pre[-_]|pre-restore|current' }
$toDelete    = @()
$kept        = @()
$skippedNew  = @()

$genericSorted = $generic | Sort-Object LastWriteTime -Descending
$newestGeneric = $genericSorted | Select-Object -First $KeepLatest

foreach ($f in $generic) {
    if ($newestGeneric -contains $f) { $kept += $f; continue }
    if ($f.LastWriteTime -ge $cutoff)  { $skippedNew += $f; continue }
    $toDelete += $f
}

$totalGeneric   = ($generic | Measure-Object Length -Sum).Sum
$totalDelete    = ($toDelete | Measure-Object Length -Sum).Sum
$totalNamed     = ($namedKeep | Measure-Object Length -Sum).Sum

Write-Output "==============================================================="
Write-Output " APS Dream Home - Backup Prune Plan"
Write-Output "==============================================================="
Write-Output " dir            : $dbDir"
Write-Output " mode           : $(if ($Delete) { 'DELETE' } else { 'DRY-RUN (nothing will be removed)' })"
Write-Output " threshold      : $Days days (cutoff $($cutoff.ToString('yyyy-MM-dd HH:mm')))"
Write-Output " keep-latest    : $KeepLatest generic dumps always kept"
Write-Output ""
Write-Output (" total files              : {0}" -f $files.Count)
Write-Output (" named safety dumps kept  : {0}  ({1:N1} MB)" -f $namedKeep.Count, ($totalNamed/1MB))
Write-Output (" generic dumps            : {0}  ({1:N1} MB)" -f $generic.Count, ($totalGeneric/1MB))
Write-Output (" generic kept (newest)    : {0}" -f ($newestGeneric.Count))
Write-Output (" generic kept (under {0,2}d): {1}" -f $Days, $skippedNew.Count)
Write-Output (" TO PRUNE                 : {0}  ({1:N1} MB)" -f $toDelete.Count, ($totalDelete/1MB))
Write-Output ""

if ($toDelete.Count -eq 0) {
    Write-Output " nothing to prune."
    exit 0
}

foreach ($f in $toDelete) { Write-Output (" {0}  {1,10:N0} B  ({2})" -f $f.Name, $f.Length, $f.LastWriteTime.ToString('yyyy-MM-dd')) }

if (-not $Delete) {
    Write-Output ""
    Write-Output " DRY-RUN complete. Re-run with -Delete to actually remove the above."
    exit 0
}

Write-Output ""
$failed = 0
foreach ($f in $toDelete) {
    try { Remove-Item -LiteralPath $f.FullName -Force -ErrorAction Stop; Write-Output " deleted: $($f.Name)" }
    catch { $failed++; Write-Output " FAILED: $($f.Name) -- $($_.Exception.Message)" }
}
if ($failed -gt 0) { Write-Output (" {0} file(s) failed" -f $failed); exit 1 }
Write-Output " prune complete. Named safety dumps untouched."
exit 0
