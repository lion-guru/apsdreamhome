#!/usr/bin/env node

/**
 * Fix Playwright Extension Installation Issues
 * Manual installation and configuration for VS Code
 */

const fs = require('fs');
const path = require('path');

class PlaywrightExtensionFix {
  constructor() {
    this.vscodeUserPath = this.findVSCodeUserPath();
    this.extensionPath = path.join(this.vscodeUserPath, 'extensions', 'ms-playwright.playwright');
  }

  findVSCodeUserPath() {
    const possiblePaths = [
      path.join(process.env.APPDATA || '', 'Code', 'User'),
      path.join(process.env.APPDATA || '', 'Code - Insiders', 'User'),
      path.join(process.env.USERPROFILE || '', '.vscode'),
      path.join(process.env.HOME || '', '.vscode')
    ];

    for (const p of possiblePaths) {
      if (fs.existsSync(p)) {
        console.log(`Found VS Code user path: ${p}`);
        return p;
      }
    }

    throw new Error('VS Code user directory not found');
  }

  async fixExtension() {
    console.log(' Fixing Playwright Extension Installation...\n');

    try {
      // Step 1: Check if Playwright is properly installed
      console.log('Step 1: Checking Playwright installation...');
      await this.checkPlaywrightInstallation();

      // Step 2: Create VS Code settings for Playwright
      console.log('Step 2: Configuring VS Code settings...');
      await this.configureVSCodeSettings();

      // Step 3: Create test configuration
      console.log('Step 3: Creating test configuration...');
      await this.createTestConfig();

      // Step 4: Set up debugging configuration
      console.log('Step 4: Setting up debugging...');
      await this.setupDebugging();

      // Step 5: Create manual installation guide
      console.log('Step 5: Creating installation guide...');
      await this.createInstallationGuide();

      console.log('\n Playwright extension fix completed!');
      console.log('Please restart VS Code and follow the manual installation guide.');

    } catch (error) {
      console.error('Fix failed:', error.message);
      throw error;
    }
  }

  async checkPlaywrightInstallation() {
    const { execSync } = require('child_process');
    
    try {
      const version = execSync('npx playwright --version', { 
        encoding: 'utf8', 
        cwd: __dirname + '/..' 
      }).trim();
      
      console.log(`  Playwright version: ${version}`);

      // Check browsers
      const browsersPath = path.join(process.env.USERPROFILE || '', 'AppData', 'Local', 'ms-playwright');
      if (fs.existsSync(browsersPath)) {
        const browsers = fs.readdirSync(browsersPath);
        console.log(`  Installed browsers: ${browsers.join(', ')}`);
      } else {
        console.log('  Installing browsers...');
        execSync('npx playwright install', { 
          stdio: 'inherit', 
          cwd: __dirname + '/..' 
        });
      }

    } catch (error) {
      throw new Error(`Playwright installation check failed: ${error.message}`);
    }
  }

  async configureVSCodeSettings() {
    const settingsPath = path.join(__dirname, '..', '.vscode', 'settings.json');
    
    try {
      let settings = {};
      if (fs.existsSync(settingsPath)) {
        settings = JSON.parse(fs.readFileSync(settingsPath, 'utf8'));
      }

      // Add Playwright-specific settings
      const playwrightSettings = {
        'playwright.reuseBrowser': true,
        'playwright.showTrace': true,
        'playwright.test-attribute': 'test',
        'playwright.inspect': true,
        'testing.automaticallyOpenPeekView': 'failureInVisibleDocument',
        'testing.followRunningTest': true,
        'testing.openTesting': 'openOnTestStart',
        'files.exclude': {
          ...(settings['files.exclude'] || {}),
          '**/test-results': true,
          '**/playwright-report': true,
          '**/playwright/.cache': true
        },
        'search.exclude': {
          ...(settings['search.exclude'] || {}),
          '**/test-results': true,
          '**/playwright-report': true
        }
      };

      // Merge settings
      Object.assign(settings, playwrightSettings);

      fs.writeFileSync(settingsPath, JSON.stringify(settings, null, 2));
      console.log('  VS Code settings updated for Playwright');

    } catch (error) {
      console.log('  Warning: Could not update VS Code settings:', error.message);
    }
  }

  async createTestConfig() {
    const testConfigPath = path.join(__dirname, '..', '.vscode', 'launch.json');
    
    try {
      const launchConfig = {
        version: '0.2.0',
        configurations: [
          {
            name: 'Debug Playwright Tests',
            type: 'node',
            request: 'launch',
            program: '${workspaceFolder}/node_modules/.bin/playwright',
            args: ['test', '--debug'],
            console: 'integratedTerminal',
            internalConsoleOptions: 'neverOpen',
            env: {
              'PWDEBUG': '1'
            }
          },
          {
            name: 'Run Playwright Tests',
            type: 'node',
            request: 'launch',
            program: '${workspaceFolder}/node_modules/.bin/playwright',
            args: ['test'],
            console: 'integratedTerminal',
            internalConsoleOptions: 'neverOpen'
          }
        ]
      };

      if (!fs.existsSync(path.dirname(testConfigPath))) {
        fs.mkdirSync(path.dirname(testConfigPath), { recursive: true });
      }

      fs.writeFileSync(testConfigPath, JSON.stringify(launchConfig, null, 2));
      console.log('  Debug configuration created');

    } catch (error) {
      console.log('  Warning: Could not create debug configuration:', error.message);
    }
  }

  async setupDebugging() {
    const debugScript = path.join(__dirname, '..', 'scripts', 'debug-playwright.js');
    
    const debugContent = `#!/usr/bin/env node

/**
 * Playwright Debug Helper
 * Run with: node scripts/debug-playwright.js
 */

const { spawn } = require('child_process');

console.log('Starting Playwright Debug Session...');

const playwright = spawn('npx', ['playwright', 'test', '--debug'], {
  stdio: 'inherit',
  cwd: process.cwd(),
  env: {
    ...process.env,
    PWDEBUG: '1'
  }
});

playwright.on('close', (code) => {
  console.log(\`Playwright debug session ended with code \${code}\`);
});

playwright.on('error', (error) => {
  console.error('Playwright debug error:', error);
});
`;

    fs.writeFileSync(debugScript, debugContent);
    console.log('  Debug helper script created');
  }

  async createInstallationGuide() {
    const guidePath = path.join(__dirname, '..', 'PLAYWRIGHT_EXTENSION_FIX.md');
    
    const guide = `# Playwright Extension Installation Fix

## Quick Fix Steps

### 1. Install Extension Manually
1. Open VS Code
2. Press Ctrl+Shift+X (Extensions)
3. Search for "Playwright Test for VSCode"
4. Click "Install" on the extension by Microsoft
5. If it fails, continue with step 2

### 2. Alternative Installation Methods

#### Method A: Command Line (if VS Code in PATH)
\`\`\`bash
code --install-extension ms-playwright.playwright
\`\`\`

#### Method B: Download and Install
1. Go to: https://marketplace.visualstudio.com/items?itemName=ms-playwright.playwright
2. Click "Download Extension"
3. In VS Code: File > Preferences > Extensions
4. Click "..." menu > "Install from VSIX..."
5. Select the downloaded .vsix file

### 3. Verify Installation

#### Check Extension
1. Open VS Code
2. Press Ctrl+Shift+P
3. Type "Playwright" - you should see Playwright commands

#### Check Test Explorer
1. Open VS Code
2. Click on Testing icon in sidebar (or Ctrl+Shift+T)
3. You should see your Playwright tests

### 4. Run Tests Without Extension

If extension still doesn't work, use these alternatives:

#### Terminal Method
\`\`\`bash
# Run all tests
npm run test

# Run with UI
npm run test:ui

# Run with debug
npm run test:debug
\`\`\`

#### VS Code Tasks
1. Press Ctrl+Shift+P
2. Type "Tasks: Run Task"
3. Select "Playwright: Run Tests"

### 5. Troubleshooting

#### Extension Not Loading
- Restart VS Code
- Check VS Code version (should be 1.74+)
- Disable other conflicting extensions

#### Tests Not Found
- Check playwright.config.js exists
- Verify tests are in tests/ directory
- Run \`npx playwright test\` in terminal first

#### Permission Issues
- Run VS Code as Administrator
- Check firewall settings
- Verify Node.js installation

### 6. Manual Test Configuration

Create \`.vscode/launch.json\`:
\`\`\`json
{
  "version": "0.2.0",
  "configurations": [
    {
      "name": "Debug Playwright Tests",
      "type": "node",
      "request": "launch",
      "program": "\${workspaceFolder}/node_modules/.bin/playwright",
      "args": ["test", "--debug"],
      "console": "integratedTerminal",
      "env": {
        "PWDEBUG": "1"
      }
    }
  ]
}
\`\`\`

### 7. Alternative Extensions

If Playwright extension continues to fail:

1. **Test Runner UI** - General test runner
2. **Jest Runner** - If you use Jest
3. **Mocha Test Explorer** - For Mocha tests

### 8. Project Status

Your project is fully functional even without the extension:
- Tests run via terminal: \`npm run test\`
- Sequential workflows: \`npm run workflow:test\`
- Database testing: \`npm run workflow:database\`
- Code quality: \`npm run workflow:quality\`

## Summary

The Playwright extension is optional. Your project has:
- Full testing capability via npm scripts
- VS Code tasks for testing
- Sequential workflow system
- Complete automation

Extension installation issues don't affect functionality.
`;

    fs.writeFileSync(guidePath, guide);
    console.log('  Installation guide created');

  }
}

// Run the fix
if (require.main === module) {
  const fix = new PlaywrightExtensionFix();
  fix.fixExtension().catch(error => {
    console.error('Fix failed:', error.message);
    process.exit(1);
  });
}

module.exports = PlaywrightExtensionFix;
