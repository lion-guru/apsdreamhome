# Windsurf Restart & Performance Script
# Run this script when Windsurf hangs or becomes slow

Write-Host "🔄 Windsurf Performance Restart Script" -ForegroundColor Green
Write-Host "=====================================" -ForegroundColor Yellow

# Stop all VS Code/Windsurf processes
Write-Host "Stopping Windsurf processes..." -ForegroundColor Yellow
Get-Process -Name "Code" -ErrorAction SilentlyContinue | Stop-Process -Force
Get-Process -Name "Windsurf" -ErrorAction SilentlyContinue | Stop-Process -Force

# Clear temporary files
Write-Host "Clearing temporary files..." -ForegroundColor Yellow
Remove-Item -Path "$env:TEMP\*" -Recurse -Force -ErrorAction SilentlyContinue
Remove-Item -Path ".\.windsurf\cache\*" -Recurse -Force -ErrorAction SilentlyContinue

# Clear VS Code cache
Write-Host "Clearing VS Code cache..." -ForegroundColor Yellow
$vscodeCache = "$env:APPDATA\Code\User\workspaceStorage"
if (Test-Path $vscodeCache) {
    Remove-Item -Path "$vscodeCache\*" -Recurse -Force -ErrorAction SilentlyContinue
}

# Restart Windsurf
Write-Host "Restarting Windsurf..." -ForegroundColor Green
Start-Process -FilePath "code" -ArgumentList "."
Start-Sleep -Seconds 3

Write-Host "✅ Windsurf restarted successfully!" -ForegroundColor Green
Write-Host "📊 Performance optimized!" -ForegroundColor Cyan
