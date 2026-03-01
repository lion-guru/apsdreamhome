# APS Dream Home - Auto Pull Script
# Continuous synchronization for collaborative development

param(
    [switch]$Continuous = $false,
    [int]$Interval = 30
)

Write-Host "🔄 APS Dream Home - Auto Pull Sync" -ForegroundColor Cyan
Write-Host "=================================" -ForegroundColor Cyan

$projectRoot = Split-Path -Parent $MyInvocation.MyCommand.Path
$logFile = Join-Path $projectRoot "auto_pull.log"

function Write-Log {
    param([string]$Message)
    $timestamp = Get-Date -Format "yyyy-MM-dd HH:mm:ss"
    $logEntry = "[$timestamp] $Message"
    Write-Host $logEntry
    Add-Content -Path $logFile -Value $logEntry
}

function Test-GitClean {
    $status = & git status --porcelain 2>$null
    return [string]::IsNullOrEmpty($status)
}

function Pull-Changes {
    Write-Log "📥 Checking for remote changes..."

    # Check if working directory is clean
    if (!(Test-GitClean)) {
        Write-Log "⚠️ Working directory not clean - skipping pull"
        return $false
    }

    # Fetch latest changes
    $fetchResult = & git fetch origin 2>&1
    if ($LASTEXITCODE -ne 0) {
        Write-Log "❌ Fetch failed: $fetchResult"
        return $false
    }

    # Check if there are changes to pull
    $behind = & git rev-list HEAD..origin/main --count 2>$null
    if ($behind -gt 0) {
        Write-Log "📦 Found $behind commits behind - pulling..."

        # Pull with rebase to avoid merge commits
        $pullResult = & git pull --rebase origin main 2>&1
        if ($LASTEXITCODE -eq 0) {
            Write-Log "✅ Pull successful"
            return $true
        } else {
            Write-Log "❌ Pull failed: $pullResult"
            return $false
        }
    } else {
        Write-Log "✅ Already up to date"
        return $true
    }
}

# Change to project directory
Set-Location $projectRoot

Write-Log "🚀 Starting auto-pull sync (Interval: ${Interval}s)"
Write-Log "📁 Project: $projectRoot"

if ($Continuous) {
    Write-Log "🔁 Running in continuous mode"

    while ($true) {
        $changes = Pull-Changes

        if ($changes) {
            Write-Log "🎉 Synchronization complete"
        }

        Write-Log "⏰ Waiting ${Interval} seconds..."
        Start-Sleep -Seconds $Interval
    }
} else {
    Write-Log "🔄 Running single sync"
    Pull-Changes
    Write-Log "🎉 Single sync complete"
}

Write-Log "🏁 Auto-pull sync finished"
