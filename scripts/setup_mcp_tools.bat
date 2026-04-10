@echo off
echo Installing MCP tools for APS Dream Home project...

echo Installing MySQL MCP Server...
call npx -y @modelcontextprotocol/server-mysql --help

echo Installing Filesystem MCP Server...
call npx -y @modelcontextprotocol/server-filesystem --help

echo Installing Git MCP Server...
call npx -y @modelcontextprotocol/server-git --help

echo Installing Puppeteer MCP Server...
call npx -y @modelcontextprotocol/server-puppeteer --help

echo Installing Memory MCP Server...
call npx -y @modelcontextprotocol/server-memory --help

echo Installing Brave Search MCP Server...
call npx -y @modelcontextprotocol/server-brave-search --help

echo.
echo MCP tools installation completed!
echo Please restart your IDE/Windsurf to activate the MCP servers.
echo.
pause
