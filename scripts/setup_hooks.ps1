# APS Dream Home - Git Hooks and Auto-Sync Setup
# Clean version - no reserved operators

param(
    [switch]$Force = $false
)

# Function to write colored logs
function Write-Log {
    param([string]$Message, [string]$Color = "White")
    Write-Host $Message -ForegroundColor $Color
}

# Function to test admin privileges
function Test-AdminPrivileges {
    $principal = New-Object Security.Principal.WindowsPrincipal([Security.Principal.WindowsIdentity]::GetCurrent())
    return $principal.IsInRole([Security.Principal.WindowsBuiltInRole]::Administrator)
}

# Function to set git hooks path
function Set-GitHooksPath {
    $projectRoot = Get-Location
    if ((Split-Path $projectRoot -Leaf) -eq "scripts") {
        $projectRoot = Split-Path -Parent $projectRoot
    }
    
    $githooksDir = Join-Path $projectRoot ".githooks"
    
    if (!(Test-Path $githooksDir)) {
        New-Item -ItemType Directory -Path $githooksDir -Force | Out-Null
        Write-Log "✅ Created .githooks directory" -ForegroundColor Green
    }
    
    # Set git hooks path in config
    $hooksPath = "githooks/"
    & git config core.hooksPath $hooksPath
    Write-Log "✅ Git hooks path configured to: $hooksPath" -ForegroundColor Green
}

# Function to set hook permissions
function Set-HookPermissions {
    $projectRoot = Get-Location
    if ((Split-Path $projectRoot -Leaf) -eq "scripts") {
        $projectRoot = Split-Path -Parent $projectRoot
    }
    
    $githooksDir = Join-Path $projectRoot ".githooks"
    
    if (Test-Path $githooksDir) {
        # Set execute permissions on Unix-like systems
        if ($IsLinux -or $IsMacOS) {
            chmod +x $githooksDir/*
        }
        Write-Log "✅ Hook permissions set" -ForegroundColor Green
    } else {
        Write-Log "❌ .githooks directory not found" -ForegroundColor Red
        exit 1
    }
}

# Function to test hook files
function Test-HookFiles {
    $projectRoot = Get-Location
    if ((Split-Path $projectRoot -Leaf) -eq "scripts") {
        $projectRoot = Split-Path -Parent $projectRoot
    }
    
    $githooksDir = Join-Path $projectRoot ".githooks"
    $requiredHooks = @("pre-commit", "post-commit", "pre-push", "post-merge")
    
    foreach ($hook in $requiredHooks) {
        $hookPath = Join-Path $githooksDir $hook
        if (!(Test-Path $hookPath)) {
            Write-Log "❌ Missing hook: $hook" -ForegroundColor Red
            return $false
        }
    }
    
    Write-Log "✅ All required hook files are present" -ForegroundColor Green
    return $true
}

# Function to create task scheduler job
function New-TaskSchedulerJob {
    Write-Log "⏰ Setting up Task Scheduler for auto-sync..."
    
    $projectRoot = Get-Location
    if ((Split-Path $projectRoot -Leaf) -eq "scripts") {
        $projectRoot = Split-Path -Parent $projectRoot
    }
    
    $scriptsDir = Join-Path $projectRoot "scripts"
    $taskName = "APS Dream Home Git Auto Sync"
    $scriptPath = Join-Path $scriptsDir "auto_sync.ps1"
    
    # Check if task already exists
    $existingTask = Get-ScheduledTask -TaskName $taskName -ErrorAction SilentlyContinue
    
    if ($existingTask -and !$Force) {
        Write-Log "ℹ️ Task '$taskName' already exists. Use -Force to recreate." -ForegroundColor Yellow
        return
    }
    
    # Remove existing task if force is used
    if ($existingTask -and $Force) {
        Unregister-ScheduledTask -TaskName $taskName -Confirm:$false
        Write-Log "🗑️ Removed existing task." -ForegroundColor Yellow
    }
    
    # Create new task
    $action = New-ScheduledTaskAction -Execute "powershell.exe" -Argument "-ExecutionPolicy Bypass -File `"$scriptPath`" -Continuous"
    $trigger = New-ScheduledTaskTrigger -Once -At (Get-Date) -RepetitionInterval (New-TimeSpan -Minutes 5) -RepetitionDuration (New-TimeSpan -Days 365)
    $settings = New-ScheduledTaskSettingsSet -AllowStartIfOnBatteries -DontStopIfGoingOnBatteries -StartWhenAvailable
    
    Register-ScheduledTask -TaskName $taskName -Action $action -Trigger $trigger -Settings $settings -Description "Automatic Git synchronization for APS Dream Home collaborative development"
    
    Write-Log "✅ Auto-sync task created (runs every 5 minutes)" -ForegroundColor Green
}

# Function to create desktop shortcuts
function New-DesktopShortcut {
    Write-Log "🔗 Creating desktop shortcuts..."
    
    $projectRoot = Get-Location
    if ((Split-Path $projectRoot -Leaf) -eq "scripts") {
        $projectRoot = Split-Path -Parent $projectRoot
    }
    
    $desktopPath = [Environment]::GetFolderPath("Desktop")
    $wshShell = New-Object -ComObject WScript.Shell
    
    # Create shortcut for project folder
    $shortcutPath = Join-Path $desktopPath "APS Dream Home.lnk"
    $shortcut = $wshShell.CreateShortcut($shortcutPath)
    $shortcut.TargetPath = $projectRoot
    $shortcut.WorkingDirectory = $projectRoot
    $shortcut.Description = "APS Dream Home Project"
    $shortcut.Save()
    
    # Create shortcut for auto-sync script
    $scriptsDir = Join-Path $projectRoot "scripts"
    $syncShortcutPath = Join-Path $desktopPath "APS Auto Sync.lnk"
    $syncShortcut = $wshShell.CreateShortcut($syncShortcutPath)
    $syncShortcut.TargetPath = Join-Path $scriptsDir "auto_sync.ps1"
    $syncShortcut.WorkingDirectory = $scriptsDir
    $syncShortcut.Description = "APS Dream Home Auto Sync"
    $syncShortcut.Save()
    
    Write-Log "✅ Desktop shortcuts created" -ForegroundColor Green
}

# Main execution
try {
    Write-Log "🚀 Setting up APS Dream Home development environment..." -ForegroundColor Cyan
    
    # Check admin privileges
    if (!(Test-AdminPrivileges)) {
        Write-Log "⚠️ This script requires administrator privileges for Task Scheduler setup." -ForegroundColor Yellow
        Write-Log "💡 Run as administrator to enable auto-sync task creation." -ForegroundColor Yellow
    }
    
    # Setup git hooks
    Set-GitHooksPath
    Set-HookPermissions
    
    # Test hook files
    if (!(Test-HookFiles)) {
        Write-Log "❌ Some hook files are missing. Please run this script from the project root." -ForegroundColor Red
        exit 1
    }
    
    # Create task scheduler job
    New-TaskSchedulerJob
    
    # Create desktop shortcuts
    New-DesktopShortcut
    
    Write-Log "" -ForegroundColor Green
    Write-Log "🎯 Setup complete!" -ForegroundColor Green
    Write-Log "  ✅ Git hooks configured" -ForegroundColor White
    Write-Log "  ✅ Auto-pull every 5 minutes" -ForegroundColor White
    Write-Log "  ✅ Desktop shortcuts created" -ForegroundColor White
    Write-Log "" -ForegroundColor Green
    Write-Log "🎯 Ready for collaborative development!" -ForegroundColor Green
    
} catch {
    Write-Log "❌ Setup failed: $($_.Exception.Message)" -ForegroundColor Red
    exit 1
}
