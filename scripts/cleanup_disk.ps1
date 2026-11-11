<#
.SYNOPSIS
    Frees disk space on the dev machine by removing REGENERABLE artifacts.

.DESCRIPTION
    The C: drive keeps hitting 0 bytes free, which silently breaks MariaDB
    (Errcode 28 "No space left on device" during ALTER TABLE) and the Flutter
    toolchain. This script removes only artifacts that can be regenerated:

      1. Flutter/Android build outputs (android\app\build, build, .gradle)
      2. Android SDK 'sources' (framework source, not needed for APK builds)
      3. Android SDK 'emulator.backup' (redundant copy of emulator/)
      4. Windows temp files older than 1 day
      5. Old DB dumps in backups\db (keeps the newest N)

    It NEVER touches: the live database, .git, source code, vendor/, node_modules,
    the Android SDK platforms/build-tools/emulator, the Gradle dependency cache,
    or the Pub cache (all needed for incremental builds).

.PARAMETER KeepDumps
    How many recent DB dumps to keep in backups\db. Default 3.

.PARAMETER MinFreeGB
    Only run cleanup if free space is below this threshold. Default 5.

.PARAMETER Aggressive
    Also clear the Gradle transform/build cache (~3 GB). This makes the next
    Android build much slower, so it is opt-in.

.PARAMETER HostCaches
    Also clear HOST-level regenerable caches that are NOT part of this repo:
    npm cache, puppeteer/Playwright browser downloads, Dart analysis-server
    cache, uv/pip caches, emulator quick-boot snapshots, opencode desktop
    logs, Windows setup leftovers, and STALE MySQL datadir copies from past
    crash recoveries (the live datadir is verified and excluded).

    This is the big lever (~20 GB). It is opt-in because it touches paths
    outside the project. It never touches: the live MySQL datadir, any
    project source, .git, vendor/, node_modules/, the Gradle dependency
    cache, the Pub cache, or the OpenCode session database (that one has
    its own script: scripts\vacuum_opencode_db.ps1).

.PARAMETER DryRun
    Report exactly what WOULD be deleted and how much space each target holds,
    then exit without deleting anything.

.EXAMPLE
    powershell -ExecutionPolicy Bypass -File scripts\cleanup_disk.ps1
    powershell -ExecutionPolicy Bypass -File scripts\cleanup_disk.ps1 -KeepDumps 5 -MinFreeGB 10
    powershell -ExecutionPolicy Bypass -File scripts\cleanup_disk.ps1 -Aggressive
    powershell -ExecutionPolicy Bypass -File scripts\cleanup_disk.ps1 -HostCaches -MinFreeGB 1
    powershell -ExecutionPolicy Bypass -File scripts\cleanup_disk.ps1 -HostCaches -MinFreeGB 1 -DryRun
#>
param(
    [int]$KeepDumps = 3,
    [double]$MinFreeGB = 5,
    [switch]$Aggressive,
    [switch]$HostCaches,
    [switch]$DryRun
)

$ErrorActionPreference = 'Continue'
$root = Split-Path -Parent $PSScriptRoot
$log = @()

function Get-FreeGB {
    return [math]::Round(([System.IO.DriveInfo]::new('C').AvailableFreeSpace / 1GB), 2)
}

function Remove-Target {
    param([string]$Path, [string]$Label)
    if (-not (Test-Path $Path)) { return 0 }
    $bytes = (Get-ChildItem $Path -Recurse -File -Force -ErrorAction SilentlyContinue |
              Measure-Object -Property Length -Sum).Sum
    $mb = [math]::Round($bytes / 1MB, 1)
    if ($DryRun) {
        if ($mb -gt 0) { $script:log += "  WOULD FREE {0,-52} {1,8} MB" -f $Label, $mb }
        return 0
    }
    Remove-Item $Path -Recurse -Force -ErrorAction SilentlyContinue
    if ($mb -gt 0) { $script:log += "  freed {0,-58} {1,8} MB" -f $Label, $mb }
    return $mb
}

$before = Get-FreeGB
$log += "Free before: $before GB"

if ($before -ge $MinFreeGB) {
    $log += "Free space OK (>= $MinFreeGB GB) - nothing to do."
    $log -join "`n" | Write-Output
    exit 0
}

$log += "Cleaning regenerable artifacts..."

# 1. Flutter / Android build outputs
$buildTargets = @(
    @{ p = "$root\mobile\apsdreamhome_app_v2\android\app\build"; l = 'mobile android/app/build' },
    @{ p = "$root\mobile\apsdreamhome_app_v2\android\.gradle"; l = 'mobile android/.gradle' },
    @{ p = "$root\mobile\apsdreamhome_app_v2\build"; l = 'mobile build' },
    @{ p = "$root\build"; l = 'root build' }
)
foreach ($t in $buildTargets) { $null = Remove-Target $t.p $t.l }

# 2/3. Android SDK safe-to-delete folders
$sdk = "$env:LOCALAPPDATA\Android\Sdk"
foreach ($t in @(
    @{ p = "$sdk\sources"; l = 'Android SDK sources' },
    @{ p = "$sdk\emulator.backup"; l = 'Android SDK emulator.backup' },
    @{ p = "$sdk\.temp"; l = 'Android SDK .temp' }
)) { $null = Remove-Target $t.p $t.l }

# 4. Windows temp (older than 1 day)
$tmpCut = (Get-Date).AddDays(-1)
$tmpFiles = Get-ChildItem "$env:TEMP" -File -Recurse -ErrorAction SilentlyContinue |
             Where-Object { $_.LastWriteTime -lt $tmpCut }
$tmpBytes = ($tmpFiles | Measure-Object -Property Length -Sum).Sum
if (-not $DryRun) { $tmpFiles | Remove-Item -Force -ErrorAction SilentlyContinue }
if ($tmpBytes -gt 0) {
    $verb = if ($DryRun) { 'WOULD FREE' } else { 'freed    ' }
    $log += "  $verb {0,-52} {1,8} MB" -f 'Windows temp (>1 day old)', [math]::Round($tmpBytes / 1MB, 1)
}

# 5. Old DB dumps (keep newest N)
$dumps = Get-ChildItem "$root\backups\db" -Filter *.sql -ErrorAction SilentlyContinue |
         Sort-Object LastWriteTime -Descending
if ($dumps.Count -gt $KeepDumps) {
    $old = $dumps | Select-Object -Skip $KeepDumps
    $oldBytes = ($old | Measure-Object -Property Length -Sum).Sum
    if (-not $DryRun) { $old | Remove-Item -Force -ErrorAction SilentlyContinue }
    $verb = if ($DryRun) { 'WOULD FREE' } else { 'freed    ' }
    $log += "  $verb {0,-52} {1,8} MB  ({2} old dumps removed, kept {3})" -f `
            'backups/db old dumps', [math]::Round($oldBytes / 1MB, 1), $old.Count, $KeepDumps
}

# 6. Optional: Gradle transform/build cache (regenerable, slows next build)
if ($Aggressive) {
    $null = Remove-Target "$env:USERPROFILE\.gradle\caches\8.14" 'gradle caches/8.14 (aggressive)'
    $null = Remove-Target "$env:USERPROFILE\.gradle\caches\transforms-*" 'gradle transforms (aggressive)'
}

# 7. Optional: HOST-level regenerable caches (outside this repo)
if ($HostCaches) {
    $u  = $env:USERPROFILE
    $la = $env:LOCALAPPDATA

    # -- SAFETY: resolve the LIVE MySQL datadir and never touch it or its parent
    $liveDatadir = $null
    foreach ($ini in @('C:\xampp\mysql\bin\my.ini', 'C:\xampp\mysql\my.ini')) {
        if (Test-Path $ini) {
            $m = Select-String -Path $ini -Pattern '^\s*datadir\s*=\s*(.+)$' |
                 Select-Object -First 1
            if ($m) { $liveDatadir = $m.Matches[0].Groups[1].Value.Trim().Trim('"') -replace '/', '\' }
        }
    }
    if ($liveDatadir) {
        $liveDatadir = [System.IO.Path]::GetFullPath($liveDatadir)
        $log += "  live MySQL datadir (protected): $liveDatadir"
    } else {
        $log += "  WARNING: could not read my.ini datadir - skipping ALL mysql dir cleanup"
    }

    function Test-SafeAgainstLiveDb {
        param([string]$Path)
        if (-not $liveDatadir) { return $false }
        $full = [System.IO.Path]::GetFullPath($Path)
        $live = $liveDatadir.TrimEnd('\')
        # reject if the target IS the live dir, or lives INSIDE it
        if ($full.TrimEnd('\').Equals($live, [StringComparison]::OrdinalIgnoreCase)) { return $false }
        if ($full.StartsWith($live + '\', [StringComparison]::OrdinalIgnoreCase)) { return $false }
        return $true
    }

    $hostTargets = @(
        @{ p = 'C:\$GetCurrent\media\sources'; l = 'Windows setup leftovers (install.esd/boot.wim)' },
        @{ p = "$la\npm-cache\_cacache";      l = 'npm cache (_cacache)' },
        @{ p = "$la\npm-cache\_npx";          l = 'npm npx cache' },
        @{ p = "$u\.cache\puppeteer";         l = 'puppeteer browser cache' },
        @{ p = "$la\.dartServer";             l = 'Dart analysis-server cache' },
        @{ p = "$la\uv\cache";                l = 'uv python package cache' },
        @{ p = "$la\pip\cache";               l = 'pip wheel cache' },
        @{ p = "$la\stonic_dsktp-updater";    l = 'stonic desktop updater installer' },
        @{ p = "$la\ai.opencode.desktop\logs";l = 'opencode desktop logs' },
        @{ p = "$u\.android\avd\aps_test.avd\snapshots"; l = 'Android emulator quick-boot snapshot' },
        @{ p = "$la\ms-playwright";           l = 'Playwright browser cache' }
    )

    foreach ($t in $hostTargets) {
        if (Test-SafeAgainstLiveDb $t.p) { $null = Remove-Target $t.p $t.l }
    }

    # Gradle daemon .out.log files only (never the dependency cache, never a
    # log a running daemon still holds open - Windows locks those anyway)
    $daemonCut = (Get-Date).AddDays(-1)
    $daemonLogs = Get-ChildItem "$u\.gradle\daemon" -Filter '*.out.log' -Recurse -File -ErrorAction SilentlyContinue |
                  Where-Object { $_.LastWriteTime -lt $daemonCut }
    $daemonBytes = ($daemonLogs | Measure-Object -Property Length -Sum).Sum
    if (-not $DryRun) { $daemonLogs | Remove-Item -Force -ErrorAction SilentlyContinue }
    if ($daemonBytes -gt 0) {
        $verb = if ($DryRun) { 'WOULD FREE' } else { 'freed    ' }
        $log += "  $verb {0,-52} {1,8} MB" -f 'gradle daemon logs (>1 day old)', [math]::Round($daemonBytes / 1MB, 1)
    }

    # STALE MySQL datadir copies from past crash recoveries.
    # 'data_backup_*' is a REAL backup and is deliberately NOT touched.
    if ($liveDatadir) {
        $mysqlRoot = Split-Path -Parent $liveDatadir
        $stale = Get-ChildItem $mysqlRoot -Directory -ErrorAction SilentlyContinue |
                 Where-Object {
                     $_.Name -ne (Split-Path -Leaf $liveDatadir) -and
                     ($_.Name -like 'data_corrupt*' -or $_.Name -like 'mysql_corrupt*' -or
                      $_.Name -eq 'data_test' -or $_.Name -eq 'data_corrupted')
                 }
        foreach ($d in $stale) {
            if (Test-SafeAgainstLiveDb $d.FullName) {
                $null = Remove-Target $d.FullName ("stale mysql datadir: " + $d.Name)
            }
        }
    }

    # Recycle Bin (per-user) - only after the above, so the size report is honest
    $null = Remove-Target 'C:\$Recycle.Bin' 'recycle bin'
}

$after = Get-FreeGB
if ($DryRun) {
    $log += ""
    $log += "DRY RUN - nothing was deleted."
    $log += "Total reclaimable above; re-run without -DryRun to apply."
} else {
    $log += "Free after : $after GB  (freed $([math]::Round($after - $before, 2)) GB)"
}
$log -join "`n" | Write-Output
