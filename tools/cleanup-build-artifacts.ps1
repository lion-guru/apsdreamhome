#!/usr/bin/env pwsh
<#
.SYNOPSIS
    Clean regeneratable build artifacts to free disk space.
.DESCRIPTION
    Removes Flutter Android Gradle build cache, .dart_tool, and other
    regeneratable artifacts. All targets are git-ignored and will be
    recreated by `flutter pub get` + `flutter build apk` on next build.
.PARAMETER DryRun
    Report what would be removed without deleting anything.
.EXAMPLE
    .\tools\cleanup-build-artifacts.ps1 -DryRun
    .\tools\cleanup-build-artifacts.ps1
.NOTES
    Author: APS Dream Home
    Date: 2026-06-06
#>
[CmdletBinding()]
param(
    [switch]$DryRun
)

$ErrorActionPreference = 'Stop'

$targets = @(
    'mobile/apsdreamhome_app_v2/android/build',
    'mobile/apsdreamhome_app_v2/android/.gradle',
    'mobile/apsdreamhome_app_v2/build',
    'mobile/apsdreamhome_app_v2/.dart_tool',
    'mobile/apsdreamhome_app_v2/ios/Pods',
    'mobile/apsdreamhome_app_v2/ios/Flutter/Flutter.framework',
    'mobile/apsdreamhome_app_v2/ios/.symlinks'
)

$fileTargets = @(
    @{ Glob = 'storage/cache/*.cache'; Description = 'Runtime cache (regenerated on next request)' }
)

$root = (Get-Location).Path
$totalBytes = 0
$removedCount = 0
$skippedCount = 0

Write-Host '=== APS Dream Home - Build Artifact Cleanup ===' -ForegroundColor Cyan
Write-Host "Root: $root"
$mode = if ($DryRun) { 'DRY RUN' } else { 'DELETE' }
Write-Host "Mode: $mode"
Write-Host ''

foreach ($path in $targets) {
    if (-not (Test-Path -LiteralPath $path)) {
        Write-Host ('[skip]   ' + $path.PadRight(60) + ' not found') -ForegroundColor DarkGray
        $skippedCount++
        continue
    }

    $size = (Get-ChildItem -LiteralPath $path -Recurse -ErrorAction SilentlyContinue |
             Measure-Object -Property Length -Sum).Sum
    $files = (Get-ChildItem -LiteralPath $path -Recurse -ErrorAction SilentlyContinue |
              Measure-Object).Count
    $sizeGB = [math]::Round($size / 1GB, 3)
    $sizeMB = [math]::Round($size / 1MB, 1)
    $line = ('  ' + $sizeGB.ToString().PadLeft(8) + ' GB / ' + $files.ToString().PadLeft(6) + ' files')

    if ($DryRun) {
        Write-Host ('[dryrun] ' + $path.PadRight(60) + $line) -ForegroundColor Yellow
    } else {
        Write-Host ('[remove] ' + $path.PadRight(60) + $line) -ForegroundColor Red
        Remove-Item -LiteralPath $path -Recurse -Force -ErrorAction SilentlyContinue
        $totalBytes += $size
        $removedCount++
    }
}

foreach ($ft in $fileTargets) {
    $glob = $ft.Glob
    $description = $ft.Description
    $files = @(Get-ChildItem -Path $glob -ErrorAction SilentlyContinue)
    if ($files.Count -eq 0) {
        Write-Host ('[skip]   ' + $glob.PadRight(60) + ' not found') -ForegroundColor DarkGray
        $skippedCount++
        continue
    }

    $size = ($files | Measure-Object -Property Length -Sum).Sum
    $sizeGB = [math]::Round($size / 1GB, 3)
    $sizeMB = [math]::Round($size / 1MB, 1)
    $line = ('  ' + $sizeGB.ToString().PadLeft(8) + ' GB / ' + $files.Count.ToString().PadLeft(6) + ' files')

    if ($DryRun) {
        Write-Host ('[dryrun] ' + $glob.PadRight(60) + $line) -ForegroundColor Yellow
    } else {
        Write-Host ('[remove] ' + $glob.PadRight(60) + $line) -ForegroundColor Red
        Remove-Item -Path $glob -Force -ErrorAction SilentlyContinue
        $totalBytes += $size
        $removedCount++
    }
}

Write-Host ''
if ($DryRun) {
    Write-Host 'Dry run complete. No files were deleted.' -ForegroundColor Yellow
    Write-Host 'Re-run without -DryRun to perform the cleanup.'
} else {
    $freedGB = [math]::Round($totalBytes / 1GB, 2)
    $freedMB = [math]::Round($totalBytes / 1MB, 1)
    $disk = Get-PSDrive C
    $usedGB = [math]::Round($disk.Used / 1GB, 1)
    $freeGB = [math]::Round($disk.Free / 1GB, 1)
    $pct = [math]::Round(($disk.Used / ($disk.Used + $disk.Free)) * 100, 2)
    Write-Host ('Cleanup complete: ' + $removedCount + ' dirs removed, ' + $skippedCount + ' skipped, ' + $freedGB + ' GB (' + $freedMB + ' MB) freed') -ForegroundColor Green
    Write-Host ('Disk C: ' + $usedGB + ' GB used / ' + $freeGB + ' GB free (' + $pct + '% full)')
}
