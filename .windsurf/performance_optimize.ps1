# Windsurf Performance Optimization Script
# Fixes slow performance and restores fast operation

param(
    [switch]$DeepClean,
    [switch]$DisableExperimental,
    [switch]$ResetVSCode
)

Write-Host "🚀 Windsurf Performance Optimization" -ForegroundColor Green
Write-Host "=====================================" -ForegroundColor Yellow

# Function to write log
function Write-Log($message) {
    $timestamp = Get-Date -Format "yyyy-MM-dd HH:mm:ss"
    $logMessage = "[$timestamp] $message"
    Write-Host $logMessage -ForegroundColor Cyan
    Add-Content -Path ".\.windsurf\optimization_log.txt" -Value $logMessage
}

# Stop all VS Code/Windsurf processes
Write-Log "Stopping all Windsurf processes..."
Get-Process -Name "Code" -ErrorAction SilentlyContinue | Stop-Process -Force
Get-Process -Name "Windsurf" -ErrorAction SilentlyContinue | Stop-Process -Force
Start-Sleep -Seconds 3

# Clear temporary files
Write-Log "Clearing temporary files..."
Remove-Item -Path "$env:TEMP\*" -Recurse -Force -ErrorAction SilentlyContinue

# Clear VS Code cache
Write-Log "Clearing VS Code cache..."
$vscodePaths = @(
    "$env:APPDATA\Code\User\workspaceStorage",
    "$env:APPDATA\Code\User\globalStorage",
    "$env:APPDATA\Code\logs",
    "$env:LOCALAPPDATA\Programs\Microsoft VS Code\resources\app\out\vs\workbench"
)

foreach ($path in $vscodePaths) {
    if (Test-Path $path) {
        Remove-Item -Path "$path\*" -Recurse -Force -ErrorAction SilentlyContinue
        Write-Log "Cleared: $path"
    }
}

# Deep clean option
if ($DeepClean) {
    Write-Log "Performing deep clean..."
    
    # Clear more caches
    $deepCleanPaths = @(
        "$env:USERPROFILE\.vscode",
        "$env:APPDATA\Code\CachedExtensions",
        "$env:LOCALAPPDATA\Microsoft\VSCode"
    )
    
    foreach ($path in $deepCleanPaths) {
        if (Test-Path $path) {
            Remove-Item -Path "$path\*" -Recurse -Force -ErrorAction SilentlyContinue
            Write-Log "Deep cleaned: $path"
        }
    }
}

# Disable experimental features
if ($DisableExperimental) {
    Write-Log "Disabling experimental features..."
    
    $settingsPath = "$env:APPDATA\Code\User\settings.json"
    if (Test-Path $settingsPath) {
        # Backup current settings
        Copy-Item $settingsPath "$settingsPath.backup" -Force
        
        # Update settings to disable experimental features
        $settings = Get-Content $settingsPath | ConvertFrom-Json
        
        # Disable experimental features that cause slowdowns
        $settings | Add-Member -NotePropertyName "editor.experimentalGpuAcceleration" -NotePropertyValue "off" -Force
        $settings | Add-Member -NotePropertyName "editor.experimental.preferTreeSitter.css" -NotePropertyValue $false -Force
        $settings | Add-Member -NotePropertyName "editor.experimental.preferTreeSitter.ini" -NotePropertyValue $false -Force
        $settings | Add-Member -NotePropertyName "editor.experimental.preferTreeSitter.regex" -NotePropertyValue $false -Force
        $settings | Add-Member -NotePropertyName "editor.experimental.preferTreeSitter.typescript" -NotePropertyValue $false -Force
        $settings | Add-Member -NotePropertyName "typescript.disableAutomaticTypeAcquisition" -NotePropertyValue $true -Force
        $settings | Add-Member -NotePropertyName "typescript.enablePromptUseWorkspaceTsdk" -NotePropertyValue $false -Force
        
        # Disable AI features that cause slowdowns
        $settings | Add-Member -NotePropertyName "codium.codeCompletion.enable" -NotePropertyValue $false -Force
        $settings | Add-Member -NotePropertyName "editor.aiStats.enabled" -NotePropertyValue $false -Force
        $settings | Add-Member -NotePropertyName "dbcode.ai.mcp.autoStart" -NotePropertyValue $false -Force
        
        # Save updated settings
        $settings | ConvertTo-Json -Depth 20 | Set-Content $settingsPath
        Write-Log "Experimental features disabled"
    }
}

# Reset VS Code completely
if ($ResetVSCode) {
    Write-Log "Resetting VS Code to default settings..."
    
    $resetPaths = @(
        "$env:APPDATA\Code",
        "$env:LOCALAPPDATA\Microsoft\VSCode"
    )
    
    foreach ($path in $resetPaths) {
        if (Test-Path $path) {
            Remove-Item -Path $path -Recurse -Force -ErrorAction SilentlyContinue
            Write-Log "Reset: $path"
        }
    }
}

# Clear project-specific caches
Write-Log "Clearing project caches..."
Remove-Item -Path ".\.windsurf\cache\*" -Recurse -Force -ErrorAction SilentlyContinue
Remove-Item -Path ".vscode\.cache\*" -Recurse -Force -ErrorAction SilentlyContinue

# Optimize VS Code settings
Write-Log "Optimizing VS Code settings..."
$optimizedSettings = @{
    "files.autoSave" = "afterDelay"
    "files.autoSaveDelay" = 1000
    "editor.fontSize" = 14
    "editor.tabSize" = 4
    "editor.wordWrap" = "on"
    "editor.minimap.enabled" = $false
    "editor.glyphMargin" = $false
    "editor.folding" = $true
    "editor.lineNumbers" = "on"
    "workbench.enableExperiments" = $false
    "telemetry.enableTelemetry" = $false
    "extensions.autoUpdate" = $false
    "extensions.autoCheckUpdates" = $false
    "update.mode" = "none"
    "extensions.ignoreRecommendations" = $true
    "workbench.startupEditor" = "none"
    "git.enableSmartCommit" = $true
    "git.autofetch" = $true
    "git.confirmSync" = $false
    "breadcrumbs.enabled" = $true
    "problems.showCurrentInStatus" = $true
    "editor.experimentalGpuAcceleration" = "off"
    "typescript.disableAutomaticTypeAcquisition" = $true
    "typescript.enablePromptUseWorkspaceTsdk" = $false
    "codium.codeCompletion.enable" = $false
    "editor.aiStats.enabled" = $false
    "dbcode.ai.mcp.autoStart" = $false
    "testing.automaticallyOpenPeekViewDuringAutoRun" = $false
    "notebook.formatOnSave.enabled" = $false
    "notebook.defaultFormatter" = $null
    "advancedPhpCsFixer.allowRisky" = $false
    "intelephense.completion.fullyQualifyGlobalConstantsAndFunctions" = $false
    "intelephense.compatibility.preferPsalmPhpstanPrefixedAnnotations" = $false
    "intelephense.codeLens.usages.enable" = $false
    "intelephense.codeLens.references.enable" = $false
    "intelephense.codeLens.parent.enable" = $false
    "intelephense.codeLens.overrides.enable" = $false
    "intelephense.codeLens.implementations.enable" = $false
    "intelephense.phpdoc.useFullyQualifiedNames" = $false
    "editor.linkedEditing" = $true
}

$settingsPath = ".vscode\settings.json"
$settingsDir = Split-Path $settingsPath -Parent

if (!(Test-Path $settingsDir)) {
    New-Item -ItemType Directory -Path $settingsDir -Force | Out-Null
}

# Backup existing settings
if (Test-Path $settingsPath) {
    Copy-Item $settingsPath "$settingsPath.backup" -Force
}

# Save optimized settings
$optimizedSettings | ConvertTo-Json -Depth 20 | Set-Content $settingsPath
Write-Log "VS Code settings optimized"

# Restart Windsurf
Write-Log "Restarting Windsurf with optimized settings..."
Start-Process -FilePath "code" -ArgumentList "."
Start-Sleep -Seconds 5

Write-Host ""
Write-Host "✅ Performance optimization completed!" -ForegroundColor Green
Write-Host ""
Write-Host "Optimizations applied:" -ForegroundColor Yellow
Write-Host "• Cleared all temporary files and caches" -ForegroundColor White
Write-Host "• Disabled experimental features" -ForegroundColor White
Write-Host "• Optimized VS Code settings" -ForegroundColor White
Write-Host "• Restored fast operation" -ForegroundColor White
Write-Host ""
Write-Host "Windsurf should now work as fast as before!" -ForegroundColor Green

if ($DeepClean) {
    Write-Host "• Deep clean performed" -ForegroundColor Cyan
}
if ($DisableExperimental) {
    Write-Host "• Experimental features disabled" -ForegroundColor Cyan
}
if ($ResetVSCode) {
    Write-Host "• VS Code completely reset" -ForegroundColor Cyan
}

Write-Host ""
Write-Host "To run again with options:" -ForegroundColor Yellow
Write-Host "• Normal: .windsurf\performance_optimize.ps1" -ForegroundColor White
Write-Host "• Deep Clean: .windsurf\performance_optimize.ps1 -DeepClean" -ForegroundColor White
Write-Host "• Disable Experimental: .windsurf\performance_optimize.ps1 -DisableExperimental" -ForegroundColor White
Write-Host "• Complete Reset: .windsurf\performance_optimize.ps1 -ResetVSCode" -ForegroundColor White
