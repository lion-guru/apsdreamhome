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

.EXAMPLE
    powershell -ExecutionPolicy Bypass -File scripts\cleanup_disk.ps1
    powershell -ExecutionPolicy Bypass -File scripts\cleanup_disk.ps1 -KeepDumps 5 -MinFreeGB 10
    powershell -ExecutionPolicy Bypass -File scripts\cleanup_disk.ps1 -Aggressive
#>
param(
    [int]$KeepDumps = 3,
    [double]$MinFreeGB = 5,
    [switch]$Aggressive
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
    $bytes = (Get-ChildItem $Path -Recurse -File -ErrorAction SilentlyContinue |
              Measure-Object -Property Length -Sum).Sum
    Remove-Item $Path -Recurse -Force -ErrorAction SilentlyContinue
    $mb = [math]::Round($bytes / 1MB, 1)
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
$tmpFiles | Remove-Item -Force -ErrorAction SilentlyContinue
if ($tmpBytes -gt 0) {
    $log += "  freed {0,-58} {1,8} MB" -f 'Windows temp (>1 day old)', [math]::Round($tmpBytes / 1MB, 1)
}

# 5. Old DB dumps (keep newest N)
$dumps = Get-ChildItem "$root\backups\db" -Filter *.sql -ErrorAction SilentlyContinue |
         Sort-Object LastWriteTime -Descending
if ($dumps.Count -gt $KeepDumps) {
    $old = $dumps | Select-Object -Skip $KeepDumps
    $oldBytes = ($old | Measure-Object -Property Length -Sum).Sum
    $old | Remove-Item -Force -ErrorAction SilentlyContinue
    $log += "  freed {0,-58} {1,8} MB  ({2} old dumps removed, kept {3})" -f `
            'backups/db old dumps', [math]::Round($oldBytes / 1MB, 1), $old.Count, $KeepDumps
}

# 6. Optional: Gradle transform/build cache (regenerable, slows next build)
if ($Aggressive) {
    $null = Remove-Target "$env:USERPROFILE\.gradle\caches\8.14" 'gradle caches/8.14 (aggressive)'
    $null = Remove-Target "$env:USERPROFILE\.gradle\caches\transforms-*" 'gradle transforms (aggressive)'
}

$after = Get-FreeGB
$log += "Free after : $after GB  (freed $([math]::Round($after - $before, 2)) GB)"
$log -join "`n" | Write-Output
