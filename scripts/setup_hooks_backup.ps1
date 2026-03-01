# APS Dream Home - Git Hooks Setup Script
# Enables Git hooks for collaborative development

param(
    [switch]$Force = $false
)

Write-Host "🔧 APS Dream Home - Git Hooks Setup" -ForegroundColor Green
Write-Host "===================================" -ForegroundColor Green

$projectRoot = Split-Path -Parent $MyInvocation.MyCommand.Path
# If we're in scripts directory, go up one level to project root
if ((Split-Path $projectRoot -Leaf) -eq "scripts") {
    $projectRoot = Split-Path -Parent $projectRoot
}
$githooksDir = Join-Path $projectRoot ".githooks"
$gitConfigPath = Join-Path $projectRoot ".git\config"
$scriptsDir = Join-Path $projectRoot "scripts"

function Write-Log {
    param([string]$Message, [string]$Color = "White")
    Write-Host $Message -ForegroundColor $Color
}

function Test-AdminPrivileges {
    $currentUser = [Security.Principal.WindowsIdentity]::GetCurrent()
    $principal = New-Object Security.Principal.WindowsPrincipal($currentUser)
    return $principal.IsInRole([Security.Principal.WindowsBuiltInRole]::Administrator)
}

function Set-GitHooksPath {
    Write-Log "🔗 Configuring Git hooks path..."

    # Check if hooks path is already configured
    $configContent = Get-Content $gitConfigPath -Raw -ErrorAction SilentlyContinue
    $hooksPathConfigured = $configContent -match "\[core\]\s*hooksPath = \.githooks"

    if (!$hooksPathConfigured -or $Force) {
        # Add hooks path configuration
        $hooksConfig = @"

[core]
	hooksPath = .githooks
"@

        if (!(Test-Path $gitConfigPath)) {
            New-Item -ItemType File -Path $gitConfigPath -Force | Out-Null
        }

        Add-Content -Path $gitConfigPath -Value $hooksConfig
        Write-Log "✅ Git hooks path configured" -ForegroundColor Green
    } else {
        Write-Log "ℹ️ Git hooks path already configured" -ForegroundColor Yellow
    }
}

function Set-HookPermissions {
    Write-Log "🔒 Setting hook file permissions..."

    if (Test-Path $githooksDir) {
        $hookFiles = Get-ChildItem -Path $githooksDir -File
        foreach ($hookFile in $hookFiles) {
            # Make executable on Windows (though not strictly necessary)
            Write-Log "  - $hookFile"
        }
        Write-Log "✅ Hook permissions set" -ForegroundColor Green
    } else {
        Write-Log "❌ Githooks directory not found" -ForegroundColor Red
        exit 1
    }
}

function Test-HookFiles {
    Write-Log "🧪 Testing hook files..."

    $hookFiles = @("pre-commit", "post-commit", "post-merge")
    $allExist = $true

    foreach ($hook in $hookFiles) {
        $hookPath = Join-Path $githooksDir $hook
        if (Test-Path $hookPath) {
            Write-Log "  ✅ $hook found" -ForegroundColor Green
        } else {
            Write-Log "  ❌ $hook missing" -ForegroundColor Red
            $allExist = $false
        }
    }

    if (!$allExist) {
        Write-Log "❌ Some hook files are missing. Please run this script from the project root." -ForegroundColor Red
        exit 1
    }
}

function New-TaskSchedulerJob {
    Write-Log "⏰ Setting up Task Scheduler for auto-sync..."

    $taskName = "APS Dream Home Git Auto Sync"
    $scriptPath = Join-Path $scriptsDir "auto_sync.ps1"

    # Check if task already exists
    $existingTask = Get-ScheduledTask -TaskName $taskName -ErrorAction SilentlyContinue

    if ($existingTask -and !$Force) {
        Write-Log "INFO: Task '$taskName' already exists. Use -Force to recreate." -ForegroundColor Yellow
        return
    }

    # Remove existing task if force is used
    if ($existingTask -and $Force) {
        Unregister-ScheduledTask -TaskName $taskName -Confirm:$false
        Write-Log "REMOVED: Existing task removed" -ForegroundColor Yellow
    }

    # Create new task
    $action = New-ScheduledTaskAction -Execute "powershell.exe" -Argument "-ExecutionPolicy Bypass -File `"$scriptPath`" -Continuous"
    $trigger = New-ScheduledTaskTrigger -Once -At (Get-Date) -RepetitionInterval (New-TimeSpan -Minutes 5) -RepetitionDuration (New-TimeSpan -Days 365)
    $settings = New-ScheduledTaskSettingsSet -AllowStartIfOnBatteries -DontStopIfGoingOnBatteries -StartWhenAvailable

    Register-ScheduledTask -TaskName $taskName -Action $action -Trigger $trigger -Settings $settings -Description "Automatic Git synchronization for APS Dream Home collaborative development"

    Write-Log "✅ Auto-sync task created (runs every 5 minutes)" -ForegroundColor Green
}

function New-DesktopShortcut {
    Write-Log "🔗 Creating desktop shortcuts..."

    $desktopPath = [Environment]::GetFolderPath("Desktop")
    $wshShell = New-Object -ComObject WScript.Shell

    # Collaboration Dashboard shortcut
    $dashboardShortcut = $wshShell.CreateShortcut((Join-Path $desktopPath "APS Dashboard.lnk"))
    $dashboardShortcut.TargetPath = "http://localhost/apsdreamhome/collaboration_dashboard.php"
    $dashboardShortcut.IconLocation = "C:\Windows\System32\SHELL32.dll,13"
    $dashboardShortcut.Save()

    # Auto Sync shortcut
    $syncShortcut = $wshShell.CreateShortcut((Join-Path $desktopPath "APS Sync.lnk"))
    $syncShortcut.TargetPath = "powershell.exe"
    $syncShortcut.Arguments = "-ExecutionPolicy Bypass -File `"$scriptsDir\auto_sync.ps1`""
    $syncShortcut.IconLocation = "C:\Windows\System32\SHELL32.dll,23"
    $syncShortcut.Save()

    Write-Log "✅ Desktop shortcuts created" -ForegroundColor Green
}

# Main execution
try {
    # Check admin privileges (optional)
    if (!(Test-AdminPrivileges)) {
        Write-Log "⚠️ Not running as administrator. Some features may not work." -ForegroundColor Yellow
    }

    # Change to project directory
    Set-Location $projectRoot
    Write-Log "📁 Project directory: $projectRoot"

    # Test hook files exist
    Test-HookFiles

    # Configure Git hooks path
    Set-GitHooksPath

    # Set hook permissions
    Set-HookPermissions

    # Create Task Scheduler job
    New-TaskSchedulerJob

    # Create desktop shortcuts
    New-DesktopShortcut

    Write-Log "" -ForegroundColor Green
    Write-Log "🎉 Git hooks and auto-sync setup complete!" -ForegroundColor Green
    Write-Log "" -ForegroundColor Green
    Write-Log "🚀 Features enabled:" -ForegroundColor Cyan
    Write-Log "  ✅ PHP syntax checking on commit" -ForegroundColor White
    Write-Log "  ✅ Auto-push after commits" -ForegroundColor White
    Write-Log "  ✅ Route health check after merges" -ForegroundColor White
    Write-Log "  ✅ Auto-pull every 5 minutes" -ForegroundColor White
    Write-Log "  ✅ Desktop shortcuts created" -ForegroundColor White
    Write-Log "" -ForegroundColor Green
    Write-Log "🎯 Ready for collaborative development!" -ForegroundColor Green

} catch {
    Write-Log "❌ Setup failed: $($_.Exception.Message)" -ForegroundColor Red
    exit 1
}
