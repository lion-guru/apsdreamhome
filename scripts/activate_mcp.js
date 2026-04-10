// MCP Activation Script for APS Dream Home
const fs = require('fs');
const path = require('path');

console.log('🚀 Activating MCP Tools for APS Dream Home Project...\n');

// Check MCP config
const mcpConfigPath = path.join(__dirname, '..', '..', '.codeium', 'windsurf', 'mcp_config.json');
if (fs.existsSync(mcpConfigPath)) {
    console.log('✅ MCP Config file exists');
    const config = JSON.parse(fs.readFileSync(mcpConfigPath, 'utf8'));
    console.log('📋 Configured MCP Servers:');
    Object.keys(config.mcpServers).forEach(server => {
        console.log(`   - ${server}`);
    });
} else {
    console.log('❌ MCP Config file not found');
}

console.log('\n🔧 Available MCP Tools:');
console.log('   - Database: MySQL operations on port 3307');
console.log('   - Filesystem: File operations in project directory');
console.log('   - Git: Version control operations');
console.log('   - Puppeteer: Web automation and testing');
console.log('   - Memory: Knowledge graph storage');
console.log('   - Brave Search: Web search capabilities');

console.log('\n📝 Next Steps:');
console.log('1. Restart your IDE/Windsurf to load MCP servers');
console.log('2. Test MCP tools with: node scripts/test_mcp_tools.js');
console.log('3. Start using MCP-powered features in your project');
