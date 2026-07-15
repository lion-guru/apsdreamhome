# OpenCode Fix Script for Abhay User
# Run this script AS ABHAY USER

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "  OpenCode Language Server Fix" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Step 1: Stop OpenCode
Write-Host "[1/6] Stopping OpenCode..." -ForegroundColor Yellow
Stop-Process -Name "OpenCode" -Force -ErrorAction SilentlyContinue
Stop-Process -Name "opencode-cli" -Force -ErrorAction SilentlyContinue
Stop-Process -Name "opencode" -Force -ErrorAction SilentlyContinue
Start-Sleep -Seconds 3
Write-Host "  Done" -ForegroundColor Green

# Step 2: Clear Language Server Cache
Write-Host "[2/6] Clearing Language Server Cache..." -ForegroundColor Yellow
$cachePaths = @(
    "$env:LOCALAPPDATA\ai.opencode.desktop\opencode\data\language-server",
    "$env:LOCALAPPDATA\ai.opencode.desktop\opencode\data\lsp",
    "$env:LOCALAPPDATA\ai.opencode.desktop\opencode\data\.cache",
    "$env:LOCALAPPDATA\ai.opencode.desktop\opencode\data\language_models",
    "$env:LOCALAPPDATA\ai.opencode.desktop\logs\*.log"
)

foreach ($path in $cachePaths) {
    if ($path -like "*\*") {
        Get-ChildItem -Path $path -ErrorAction SilentlyContinue | Remove-Item -Recurse -Force -ErrorAction SilentlyContinue
    } elseif (Test-Path $path) {
        Remove-Item -Path $path -Force -ErrorAction SilentlyContinue
    }
}
Write-Host "  Done" -ForegroundColor Green

# Step 3: Recreate Data Folder
Write-Host "[3/6] Recreating Data Folder..." -ForegroundColor Yellow
$opencodeData = "$env:LOCALAPPDATA\ai.opencode.desktop\opencode\data"
if (!(Test-Path $opencodeData)) {
    New-Item -ItemType Directory -Path $opencodeData -Force | Out-Null
}
Write-Host "  Done" -ForegroundColor Green

# Step 4: Create Fresh MCP Config
Write-Host "[4/6] Creating MCP Config..." -ForegroundColor Yellow
$mcpJson = @"
{
    "mcpServers": {
        "playwright": {
            "command": "node",
            "args": ["C:\\xampp\\htdocs\\apsdreamhome\\node_modules\\@playwright\\mcp\\dist\\index.js"],
            "env": {"PLAYWRIGHT_BROWSERS_PATH": "0"}
        },
        "filesystem": {
            "command": "node",
            "args": ["C:\\xampp\\htdocs\\apsdreamhome\\node_modules\\@modelcontextprotocol\\server-filesystem\\dist\\index.js", "C:\\xampp\\htdocs\\apsdreamhome"]
        },
        "mysql": {
            "command": "node",
            "args": ["C:\\xampp\\htdocs\\apsdreamhome\\node_modules\\@morris131\\mysql-mcp-server\\dist\\index.js"],
            "env": {"MYSQL_HOST": "127.0.0.1", "MYSQL_PORT": "3307", "MYSQL_USER": "root", "MYSQL_PASSWORD": "", "MYSQL_DATABASE": "apsdreamhome"}
        }
    }
}
"@
$mcpJson | Out-File -FilePath "$opencodeData\mcp.json" -Encoding UTF8 -Force
Write-Host "  Done" -ForegroundColor Green

# Step 5: Clear AppData
Write-Host "[5/6] Clearing AppData Cache..." -ForegroundColor Yellow
$appCachePaths = @(
    "$env:LOCALAPPDATA\ai.opencode.desktop\EBWebView\Default\*",
    "$env:LOCALAPPDATA\ai.opencode.desktop\opencode\*.lock"
)
foreach ($path in $appCachePaths) {
    Get-ChildItem -Path $path -ErrorAction SilentlyContinue | Where-Object { $_.Name -notlike "Preferences" } | Remove-Item -Force -ErrorAction SilentlyContinue
}
Write-Host "  Done" -ForegroundColor Green

# Step 6: Restart
Write-Host "[6/6] Ready to Restart..." -ForegroundColor Yellow

Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "  FIX COMPLETE!" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "Now run OpenCode manually from Start Menu" -ForegroundColor Yellow
Write-Host "If still not working, try reinstalling OpenCode" -ForegroundColor Yellow
Write-Host ""

# Ask to restart
$response = Read-Host "Restart PC now? (y/n)"
if ($response -eq "y") {
    Restart-Computer -Force
}
