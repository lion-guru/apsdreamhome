# APS Dream Home - Auto Sync Script
# Continuous synchronization for collaborative development

param(
    [switch]$Continuous = $false,
    [int]$Interval = 30
)

Write-Host "APS Dream Home - Auto Sync" -ForegroundColor Cyan
Write-Host "==========================" -ForegroundColor Cyan

$projectRoot = Split-Path -Parent $MyInvocation.MyCommand.Path
$logFile = Join-Path $projectRoot "auto_sync.log"

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

function Push-Changes {
    Write-Log "Checking for local changes to push..."

    # Check if there are uncommitted changes
    if (!(Test-GitClean)) {
        Write-Log "Found local changes - committing and pushing..."

        # Auto-add all changes
        $addResult = & git add . 2>&1
        if ($LASTEXITCODE -ne 0) {
            Write-Log "Git add failed: $addResult"
            return $false
        }

        # Auto-commit with timestamp
        $timestamp = Get-Date -Format "yyyy-MM-dd HH:mm:ss"
        $commitMessage = "Auto-sync commit at $timestamp"
        $commitResult = & git commit -m $commitMessage 2>&1
        if ($LASTEXITCODE -ne 0) {
            Write-Log "Git commit failed: $commitResult"
            return $false
        }

        # Push changes
        $pushResult = & git push origin main 2>&1
        if ($LASTEXITCODE -eq 0) {
            Write-Log "Push successful"
            return $true
        } else {
            Write-Log "Push failed: $pushResult"
            return $false
        }
    } else {
        Write-Log "No local changes to push"
        return $true
    }
}

function Invoke-PullChanges {
    Write-Log "Checking for remote changes..."

    # Check if working directory is clean
    if (!(Test-GitClean)) {
        Write-Log "Working directory not clean - attempting to push first..."
        Push-Changes
        return $false
    }

    # Fetch latest changes
    $fetchResult = & git fetch origin 2>&1
    if ($LASTEXITCODE -ne 0) {
        Write-Log "Fetch failed: $fetchResult"
        return $false
    }

    # Check if there are changes to pull
    $behind = & git rev-list HEAD..origin/main --count 2>$null
    if ($behind -gt 0) {
        Write-Log "Found $behind commits behind - pulling..."

        # Pull with rebase to avoid merge commits
        $pullResult = & git pull --rebase origin main 2>&1
        if ($LASTEXITCODE -eq 0) {
            Write-Log "Pull successful"
            return $true
        } else {
            Write-Log "Pull failed: $pullResult"
            return $false
        }
    } else {
        Write-Log "Already up to date"
        return $true
    }
}

# Change to project directory
Set-Location $projectRoot

Write-Log "Starting auto-sync (Interval: ${Interval}s)"
Write-Log "Project: $projectRoot"

if ($Continuous) {
    Write-Log "Running in continuous mode"

    while ($true) {
        # First try to push local changes
        $pushSuccess = Push-Changes

        # Then try to pull remote changes
        $pullSuccess = Invoke-PullChanges

        if ($pushSuccess -and $pullSuccess) {
            Write-Log "Full synchronization complete"
        } elseif ($pushSuccess) {
            Write-Log "Push completed, pull skipped"
        } elseif ($pullSuccess) {
            Write-Log "Pull completed, no push needed"
        }

        Write-Log "Waiting ${Interval} seconds..."
        Start-Sleep -Seconds $Interval
    }
} else {
    Write-Log "Running single sync"
    Push-Changes
    Invoke-PullChanges
    Write-Log "Single sync complete"
}

Write-Log "Auto-sync finished"
