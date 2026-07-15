# OpenCode Language Server Fix Script
# Run as Administrator in guest_1 user

Write-Host "=== OpenCode Language Server Fix ===" -ForegroundColor Cyan

# Stop OpenCode
Write-Host "`nStopping OpenCode..." -ForegroundColor Yellow
Stop-Process -Name "OpenCode" -Force -ErrorAction SilentlyContinue
Stop-Process -Name "opencode-cli" -Force -ErrorAction SilentlyContinue
Start-Sleep -Seconds 2

# Clear language server cache
Write-Host "Clearing language server cache..." -ForegroundColor Yellow
$cachePaths = @(
    "C:\Users\guest_1\AppData\Local\ai.opencode.desktop\opencode\data\language-server",
    "C:\Users\guest_1\AppData\Local\ai.opencode.desktop\opencode\data\lsp",
    "C:\Users\guest_1\AppData\Local\ai.opencode.desktop\opencode\data\.cache"
)

foreach ($path in $cachePaths) {
    if (Test-Path $path) {
        Remove-Item -Path $path -Recurse -Force -ErrorAction SilentlyContinue
        Write-Host "  Removed: $path" -ForegroundColor Green
    }
}

# Recreate data folder
Write-Host "`nRecreating data folder..." -ForegroundColor Yellow
New-Item -ItemType Directory -Path "C:\Users\guest_1\AppData\Local\ai.opencode.desktop\opencode\data" -Force | Out-Null

# Recreate MCP config
Write-Host "Creating MCP config..." -ForegroundColor Yellow
$mcpConfig = @'
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
'@

$mcpConfig | Out-File -FilePath "C:\Users\guest_1\AppData\Local\ai.opencode.desktop\opencode\data\mcp.json" -Encoding UTF8

Write-Host "MCP config created" -ForegroundColor Green

# Clear logs
Write-Host "`nClearing logs..." -ForegroundColor Yellow
Get-ChildItem "C:\Users\guest_1\AppData\Local\ai.opencode.desktop\logs" -Filter "*.log" -ErrorAction SilentlyContinue | Remove-Item -Force -ErrorAction SilentlyContinue

Write-Host "`n=== Fix Complete ===" -ForegroundColor Cyan
Write-Host "Restart OpenCode manually" -ForegroundColor Green
Write-Host "Press any key to exit..."
$null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")
