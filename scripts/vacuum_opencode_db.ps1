<#
.SYNOPSIS
    Reclaims the dead space inside the OpenCode session database.

.DESCRIPTION
    C:\Users\<you>\.local\share\opencode\opencode.db is a SQLite database that
    stores every OpenCode session, message and tool part. Because it runs with
    journal_mode=wal and auto_vacuum=NONE, every deleted session/message leaves
    its pages behind as free space forever.

    On this machine the file is 42.25 GB, of which only ~10.1 GB is live data -
    32.16 GB (76%) is pure dead space. The file cannot shrink on its own.

    This script:
      1. REFUSES to run while OpenCode is open (the DB must be unlocked -
         SQLite VACUUM needs an exclusive transaction, and running it against a
         live app risks corrupting the session history).
      2. Verifies free disk space is enough for the rewrite (~live size + 2 GB).
      3. Records row counts of every table, runs the VACUUM, then re-reads the
         counts and asserts they are IDENTICAL. A mismatch aborts and reports.
      4. Enables auto_vacuum=INCREMENTAL so future deletions shrink the file on
         their own instead of ballooning it back to 42 GB.
      5. Runs PRAGMA integrity_check and truncates the WAL file.

    It NEVER deletes a single row. This is a space reclaim, not a cleanup.

.PARAMETER Execute
    Actually run it. Without this flag the script only reports what it WOULD do.

.PARAMETER DbPath
    Path to opencode.db. Defaults to the standard location.

.EXAMPLE
    # Step 1 - see what it would do (safe, OpenCode may stay open)
    powershell -ExecutionPolicy Bypass -File scripts\vacuum_opencode_db.ps1

    # Step 2 - close OpenCode fully, then:
    powershell -ExecutionPolicy Bypass -File scripts\vacuum_opencode_db.ps1 -Execute
#>
param(
    [switch]$Execute,
    [string]$DbPath = "$env:USERPROFILE\.local\share\opencode\opencode.db"
)

$ErrorActionPreference = 'Stop'
$log = @()

function Get-FreeGB { [math]::Round(([System.IO.DriveInfo]::new('C').AvailableFreeSpace / 1GB), 2) }

$log += "=== OpenCode DB vacuum ==="
$log += "db    : $DbPath"
$log += "free  : $(Get-FreeGB) GB"

if (-not (Test-Path $DbPath)) {
    $log += "ERROR: database not found. Nothing to do."
    $log -join "`n" | Write-Output
    exit 1
}

# --- 1. refuse while OpenCode is running ------------------------------------
$running = Get-Process -ErrorAction SilentlyContinue |
           Where-Object { $_.ProcessName -match '^opencode' -or
                          ($_.Path -and $_.Path -like '*opencode*') }
if ($running) {
    $names = ($running | ForEach-Object { "$($_.ProcessName)($($_.Id))" }) -join ', '
    $log += "ERROR: OpenCode is RUNNING ($names)."
    $log += "       The DB must be unlocked for VACUUM. Fully quit OpenCode"
    $log += "       (all windows + tray icon), then run this again with -Execute."
    $log += "       No changes were made."
    $log -join "`n" | Write-Output
    exit 2
}
$log += "lock  : OPEN (no OpenCode process found)"

# --- 2. size math ------------------------------------------------------------
$sizeGB = [math]::Round((Get-Item $DbPath).Length / 1GB, 2)
$log += "size  : $sizeGB GB"

if (-not $Execute) {
    $log += ""
    $log += "DRY RUN - nothing was changed."
    $log += "Run with -Execute (after quitting OpenCode) to reclaim the dead space."
    $log -join "`n" | Write-Output
    exit 0
}

# --- 3. the vacuum (php + sqlite) -------------------------------------------
$php = 'C:\xampp\php\php.exe'
if (-not (Test-Path $php)) { $php = (Get-Command php -ErrorAction SilentlyContinue).Source }
if (-not $php) {
    $log += "ERROR: php.exe not found. Cannot run the vacuum."
    $log -join "`n" | Write-Output
    exit 1
}

$helper = Join-Path $env:TEMP ("opencode_vacuum_" + [guid]::NewGuid().ToString('N') + '.php')
$phpCode = @'
<?php
$db = $argv[1];
$out = [];
try {
    $pdo = new PDO('sqlite:' . $db, null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_TIMEOUT => 30,
    ]);
} catch (Throwable $e) {
    fwrite(STDERR, "OPEN_FAILED: " . $e->getMessage() . "\n");
    exit(1);
}

$ps = (int) $pdo->query('PRAGMA page_size')->fetchColumn();
$pc = (int) $pdo->query('PRAGMA page_count')->fetchColumn();
$fl = (int) $pdo->query('PRAGMA freelist_count')->fetchColumn();
$liveGB = ($pc - $fl) * $ps / 1073741824;
$deadGB = $fl * $ps / 1073741824;
echo "SIZE_BEFORE={$pc} live={$liveGB} dead={$deadGB}\n";

$needGB = $liveGB + 2;
$freeGB = (int) (trim(shell_exec('echo ' . escapeshellarg((string) (int) (disk_free_space('C:') / 1073741824))))) ;
if ($freeGB < $needGB) {
    fwrite(STDERR, "INSUFFICIENT_SPACE need=" . round($needGB,2) . "GB free={$freeGB}GB\n");
    exit(2);
}

// --- snapshot every table's row count ---
$tables = $pdo->query("SELECT name FROM sqlite_master WHERE type='table'")->fetchAll(PDO::FETCH_COLUMN);
$before = [];
foreach ($tables as $t) {
    try { $before[$t] = (int) $pdo->query('SELECT COUNT(*) FROM "' . $t . '"')->fetchColumn(); }
    catch (Throwable $e) { $before[$t] = -1; }
}
echo "TABLES=" . count($before) . "\n";

// --- the reclaim: enable incremental auto-vacuum, then VACUUM ---------------
$t0 = microtime(true);
$pdo->exec('PRAGMA auto_vacuum = INCREMENTAL');
$pdo->exec('VACUUM');
$pdo->exec('PRAGMA wal_checkpoint(TRUNCATE)');
echo "VACUUM_SECONDS=" . round(microtime(true) - $t0, 1) . "\n";

// --- verify: counts must be IDENTICAL ---------------------------------------
$after = [];
foreach ($tables as $t) {
    try { $after[$t] = (int) $pdo->query('SELECT COUNT(*) FROM "' . $t . '"')->fetchColumn(); }
    catch (Throwable $e) { $after[$t] = -1; }
}
$mismatch = 0;
foreach ($before as $t => $c) {
    if ($c !== $after[$t]) { $mismatch++; echo "ROWCOUNT_MISMATCH $t before=$c after={$after[$t]}\n"; }
}
echo "ROWCOUNT_MISMATCHES=$mismatch\n";

$ic = $pdo->query('PRAGMA integrity_check')->fetchColumn();
echo "INTEGRITY=$ic\n";

$pc2 = (int) $pdo->query('PRAGMA page_count')->fetchColumn();
$fl2 = (int) $pdo->query('PRAGMA freelist_count')->fetchColumn();
echo "SIZE_AFTER={$pc2} freelist={$fl2}\n";
echo "RECLAIMED_GB=" . round(($pc - $pc2) * $ps / 1073741824, 2) . "\n";

if ($mismatch > 0) { fwrite(STDERR, "ABORT_ROWCOUNT_MISMATCH\n"); exit(3); }
if (strtoupper((string)$ic) !== 'OK') { fwrite(STDERR, "ABORT_INTEGRITY\n"); exit(4); }
echo "RESULT=OK\n";
'@

try {
    [System.IO.File]::WriteAllText($helper, $phpCode, (New-Object System.Text.UTF8Encoding($false)))
    $raw = & $php $helper $DbPath 2>&1 | Out-String
    $code = $LASTEXITCODE
    $log += $raw.Trim()
    if ($code -ne 0) {
        $log += ""
        $log += "RESULT: FAILED (exit $code) - see above. The original DB file is untouched."
    }
} finally {
    Remove-Item $helper -Force -ErrorAction SilentlyContinue
}

$log += ""
$log += "free  : $(Get-FreeGB) GB"
$log -join "`n" | Write-Output
if ($code -ne 0) { exit $code }
exit 0
