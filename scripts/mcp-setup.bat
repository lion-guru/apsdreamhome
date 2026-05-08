@echo off
chcp 65001 >nul
echo ========================================
echo   MCP Servers Setup - APS Dream Home
echo ========================================
echo.
echo Installing required MCP servers...
echo.

echo [1/5] Installing MySQL MCP Server...
call npx install -g @f4ww4z/mcp-mysql-server
if %errorlevel% neq 0 (
    echo ⚠️  MySQL server failed, trying npm...
    call npm install -g @f4ww4z/mcp-mysql-server
)

echo.
echo [2/5] Installing Filesystem MCP Server...
call npx install -g @modelcontextprotocol/server-filesystem
if %errorlevel% neq 0 (
    echo ⚠️  Filesystem server failed, trying npm...
    call npm install -g @modelcontextprotocol/server-filesystem
)

echo.
echo [3/5] Installing Memory MCP Server...
call npx install -g @modelcontextprotocol/server-memory
if %errorlevel% neq 0 (
    echo ⚠️  Memory server failed, trying npm...
    call npm install -g @modelcontextprotocol/server-memory
)

echo.
echo [4/5] Installing Playwright MCP Server...
call npx install -g @playwright/mcp
if %errorlevel% neq 0 (
    echo ⚠️  Playwright server failed, trying npm...
    call npm install -g @playwright/mcp
)

echo.
echo [5/5] Installing Sequential Thinking MCP Server...
call npx install -g @modelcontextprotocol/server-sequential-thinking
if %errorlevel% neq 0 (
    echo ⚠️  Sequential thinking server failed, trying npm...
    call npm install -g @modelcontextprotocol/server-sequential-thinking
)

echo.
echo ========================================
echo   Setup Complete!
echo ========================================
echo.
echo Next steps:
echo 1. Restart VS Code/Windsurf
echo 2. Check MCP status in status bar
echo 3. Test with: "Show me MySQL tables"
echo.
pause
