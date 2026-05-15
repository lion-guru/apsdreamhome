#!/bin/bash
# APS Dream Home - MySQL MCP Server Installation

echo '🔧 Installing MySQL MCP Server...'
echo '=================================='

# Check if Node.js is installed
if ! command -v node &> /dev/null; then
    echo '❌ Node.js is not installed. Please install Node.js first.'
    exit 1
fi

# Check if npm is installed
if ! command -v npm &> /dev/null; then
    echo '❌ npm is not installed. Please install npm first.'
    exit 1
fi

echo '📦 Installing mysql-mcp-server globally...'
npm install -g mysql-mcp-server

if [ $? -eq 0 ]; then
    echo '✅ MySQL MCP Server installed successfully!'
    echo ''
    echo '🔄 Next Steps:'
    echo '1. Restart your Windsurf IDE'
    echo '2. Check if MySQL MCP shows green status'
    echo '3. Test database operations'
    echo ''
    echo '🎉 Installation complete!'
else
    echo '❌ Installation failed!'
    echo 'Try running: npm install -g mysql-mcp-server --force'
    exit 1
fi
