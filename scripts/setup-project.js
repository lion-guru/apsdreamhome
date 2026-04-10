#!/usr/bin/env node

/**
 * APS Dream Home - Project Setup Script
 * One-click setup for local development environment
 */

const fs = require('fs');
const path = require('path');
const { execSync } = require('child_process');

class ProjectSetup {
  constructor() {
    this.projectRoot = __dirname;
    this.steps = [
      'Check Node.js and npm',
      'Install dependencies',
      'Setup Playwright',
      'Configure environment',
      'Setup MCP tools',
      'Initialize database',
      'Run initial tests',
      'Generate documentation'
    ];
  }

  async setup() {
    console.log(' APS Dream Home - Project Setup');
    console.log('='.repeat(50));
    
    for (let i = 0; i < this.steps.length; i++) {
      const step = this.steps[i];
      console.log(`\n Step ${i + 1}/${this.steps.length}: ${step}`);
      console.log('-'.repeat(40));
      
      try {
        await this.executeStep(i);
        console.log(`  Success!`);
      } catch (error) {
        console.error(`  Error: ${error.message}`);
        process.exit(1);
      }
    }
    
    console.log('\n Setup completed successfully!');
    console.log('\n Available commands:');
    console.log('  npm run dev           - Start development server');
    console.log('  npm run test          - Run Playwright tests');
    console.log('  npm run workflow:test - Run full system test');
    console.log('  npm run lint          - Check code quality');
    console.log('  npm run format        - Format code');
  }

  async executeStep(stepIndex) {
    switch (stepIndex) {
      case 0:
        this.checkNodeJS();
        break;
      case 1:
        this.installDependencies();
        break;
      case 2:
        this.setupPlaywright();
        break;
      case 3:
        this.configureEnvironment();
        break;
      case 4:
        this.setupMCP();
        break;
      case 5:
        this.initializeDatabase();
        break;
      case 6:
        this.runInitialTests();
        break;
      case 7:
        this.generateDocumentation();
        break;
      default:
        throw new Error(`Unknown step: ${stepIndex}`);
    }
  }

  checkNodeJS() {
    try {
      const nodeVersion = execSync('node --version', { encoding: 'utf8' }).trim();
      const npmVersion = execSync('npm --version', { encoding: 'utf8' }).trim();
      
      console.log(`  Node.js: ${nodeVersion}`);
      console.log(`  npm: ${npmVersion}`);
      
      const nodeMajor = parseInt(nodeVersion.slice(1).split('.')[0]);
      if (nodeMajor < 18) {
        throw new Error('Node.js version 18 or higher required');
      }
    } catch (error) {
      throw new Error(`Node.js check failed: ${error.message}`);
    }
  }

  installDependencies() {
    try {
      console.log('  Installing npm dependencies...');
      execSync('npm install', { stdio: 'inherit', cwd: this.projectRoot });
    } catch (error) {
      throw new Error(`Dependency installation failed: ${error.message}`);
    }
  }

  setupPlaywright() {
    try {
      console.log('  Installing Playwright browsers...');
      execSync('npx playwright install', { stdio: 'inherit', cwd: this.projectRoot });
    } catch (error) {
      throw new Error(`Playwright setup failed: ${error.message}`);
    }
  }

  configureEnvironment() {
    try {
      const envExample = path.join(this.projectRoot, '.env.local');
      const envFile = path.join(this.projectRoot, '.env');
      
      if (!fs.existsSync(envFile) && fs.existsSync(envExample)) {
        fs.copyFileSync(envExample, envFile);
        console.log('  Environment file created (.env)');
      }
      
      // Create necessary directories
      const dirs = ['logs', 'reports', 'storage/uploads', 'storage/cache'];
      dirs.forEach(dir => {
        const dirPath = path.join(this.projectRoot, dir);
        if (!fs.existsSync(dirPath)) {
          fs.mkdirSync(dirPath, { recursive: true });
          console.log(`  Created directory: ${dir}`);
        }
      });
    } catch (error) {
      throw new Error(`Environment setup failed: ${error.message}`);
    }
  }

  setupMCP() {
    try {
      console.log('  Setting up MCP tools...');
      
      // Check MCP configuration
      const mcpConfig = path.join(this.projectRoot, 'mcp_config.json');
      if (fs.existsSync(mcpConfig)) {
        console.log('  MCP configuration found');
      }
      
      // Test MCP tools
      const testScript = path.join(this.projectRoot, 'scripts', 'test-mcp-tools.js');
      if (fs.existsSync(testScript)) {
        try {
          execSync('node scripts/test-mcp-tools.js', { 
            encoding: 'utf8', 
            cwd: this.projectRoot,
            timeout: 10000
          });
          console.log('  MCP tools test passed');
        } catch (error) {
          console.log('  MCP tools test failed (non-critical)');
        }
      }
    } catch (error) {
      throw new Error(`MCP setup failed: ${error.message}`);
    }
  }

  initializeDatabase() {
    try {
      console.log('  Testing database connection...');
      
      const dbTest = execSync('php test_mysql_connection.php', { 
        encoding: 'utf8', 
        cwd: this.projectRoot 
      });
      
      if (dbTest.includes('successful')) {
        console.log('  Database connection successful');
      } else {
        throw new Error('Database connection failed');
      }
    } catch (error) {
      throw new Error(`Database initialization failed: ${error.message}`);
    }
  }

  runInitialTests() {
    try {
      console.log('  Running initial tests...');
      
      // Run a quick test suite
      try {
        execSync('npx playwright test --reporter=line', { 
          stdio: 'inherit', 
          cwd: this.projectRoot,
          timeout: 60000
        });
        console.log('  Playwright tests passed');
      } catch (error) {
        console.log('  Playwright tests failed (check configuration)');
      }
    } catch (error) {
      throw new Error(`Initial tests failed: ${error.message}`);
    }
  }

  generateDocumentation() {
    try {
      console.log('  Generating documentation...');
      
      const docs = {
        setup: {
          nodeVersion: execSync('node --version', { encoding: 'utf8' }).trim(),
          npmVersion: execSync('npm --version', { encoding: 'utf8' }).trim(),
          playwrightVersion: execSync('npx playwright --version', { encoding: 'utf8' }).trim(),
          setupDate: new Date().toISOString(),
          projectRoot: this.projectRoot
        },
        commands: [
          'npm run dev - Start development server on localhost:8080',
          'npm run test - Run Playwright tests',
          'npm run workflow:test - Run full system test',
          'npm run lint - Check code quality',
          'npm run format - Format code with Prettier'
        ],
        structure: {
          controllers: 'app/Http/Controllers/',
          models: 'app/Models/',
          views: 'app/Views/',
          tests: 'tests/',
          scripts: 'scripts/',
          config: 'config/'
        }
      };
      
      const docsPath = path.join(this.projectRoot, 'docs', 'setup-info.json');
      if (!fs.existsSync(path.dirname(docsPath))) {
        fs.mkdirSync(path.dirname(docsPath), { recursive: true });
      }
      
      fs.writeFileSync(docsPath, JSON.stringify(docs, null, 2));
      console.log('  Documentation generated');
    } catch (error) {
      throw new Error(`Documentation generation failed: ${error.message}`);
    }
  }
}

// Run setup if called directly
if (require.main === module) {
  const setup = new ProjectSetup();
  setup.setup().catch(error => {
    console.error('Setup failed:', error.message);
    process.exit(1);
  });
}

module.exports = ProjectSetup;
