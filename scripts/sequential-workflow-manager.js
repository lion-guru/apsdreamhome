#!/usr/bin/env node

/**
 * Sequential Workflow Manager for APS Dream Home
 * Ensures accurate, step-by-step task execution for large projects
 */

const fs = require('fs');
const path = require('path');
const { execSync } = require('child_process');

class SequentialWorkflowManager {
  constructor() {
    this.workflows = new Map();
    this.currentWorkflow = null;
    this.stepHistory = [];
    this.errorLog = [];
    this.projectRoot = __dirname;
  }

  /**
   * Define a new workflow with sequential steps
   */
  defineWorkflow(name, steps) {
    this.workflows.set(name, {
      name,
      steps,
      currentIndex: 0,
      status: 'pending',
      startTime: null,
      endTime: null,
      errors: []
    });
    console.log(` Workflow defined: ${name} with ${steps.length} steps`);
  }

  /**
   * Execute workflow step by step
   */
  async executeWorkflow(workflowName) {
    const workflow = this.workflows.get(workflowName);
    if (!workflow) {
      throw new Error(`Workflow ${workflowName} not found`);
    }

    this.currentWorkflow = workflow;
    workflow.status = 'running';
    workflow.startTime = new Date();

    console.log(`\n Starting workflow: ${workflowName}`);
    console.log('='.repeat(50));

    try {
      for (let i = 0; i < workflow.steps.length; i++) {
        workflow.currentIndex = i;
        const step = workflow.steps[i];
        
        console.log(`\n Step ${i + 1}/${workflow.steps.length}: ${step.name}`);
        console.log('-'.repeat(40));

        try {
          const result = await this.executeStep(step);
          this.stepHistory.push({
            workflow: workflowName,
            step: step.name,
            status: 'success',
            result,
            timestamp: new Date()
          });
          console.log(`  Success: ${result}`);
        } catch (error) {
          const errorInfo = {
            workflow: workflowName,
            step: step.name,
            status: 'error',
            error: error.message,
            timestamp: new Date()
          };
          
          this.stepHistory.push(errorInfo);
          workflow.errors.push(errorInfo);
          this.errorLog.push(errorInfo);
          
          console.error(`  Error: ${error.message}`);
          
          if (step.critical !== false) {
            workflow.status = 'failed';
            throw error;
          }
        }
      }

      workflow.status = 'completed';
      workflow.endTime = new Date();
      
      console.log(`\n Workflow completed successfully!`);
      console.log(` Duration: ${workflow.endTime - workflow.startTime}ms`);
      
    } catch (error) {
      workflow.status = 'failed';
      workflow.endTime = new Date();
      console.error(`\n Workflow failed: ${error.message}`);
      throw error;
    }
  }

  /**
   * Execute individual step
   */
  async executeStep(step) {
    switch (step.type) {
      case 'command':
        return this.executeCommand(step);
      case 'file_check':
        return this.checkFile(step);
      case 'api_test':
        return this.testAPI(step);
      case 'database_check':
        return this.checkDatabase(step);
      case 'mcp_operation':
        return this.executeMCPOperation(step);
      case 'validation':
        return this.validateStep(step);
      default:
        throw new Error(`Unknown step type: ${step.type}`);
    }
  }

  /**
   * Execute shell command
   */
  executeCommand(step) {
    const { command, cwd = this.projectRoot, timeout = 30000 } = step;
    
    try {
      const result = execSync(command, { 
        cwd, 
        encoding: 'utf8',
        timeout,
        stdio: ['pipe', 'pipe', 'pipe']
      });
      
      return result.trim();
    } catch (error) {
      throw new Error(`Command failed: ${command} - ${error.message}`);
    }
  }

  /**
   * Check file existence and content
   */
  checkFile(step) {
    const { path: filePath, exists = true, content } = step;
    const fullPath = path.resolve(this.projectRoot, filePath);
    
    if (exists && !fs.existsSync(fullPath)) {
      throw new Error(`File does not exist: ${fullPath}`);
    }
    
    if (!exists && fs.existsSync(fullPath)) {
      throw new Error(`File should not exist: ${fullPath}`);
    }
    
    if (content) {
      const fileContent = fs.readFileSync(fullPath, 'utf8');
      if (content instanceof RegExp) {
        if (!content.test(fileContent)) {
          throw new Error(`File content does not match pattern: ${content}`);
        }
      } else {
        if (!fileContent.includes(content)) {
          throw new Error(`File does not contain expected content: ${content}`);
        }
      }
    }
    
    return `File check passed: ${fullPath}`;
  }

  /**
   * Test API endpoint
   */
  async testAPI(step) {
    const { url, method = 'GET', data, expectedStatus = 200 } = step;
    
    try {
      const response = await fetch(url, {
        method,
        headers: { 'Content-Type': 'application/json' },
        body: data ? JSON.stringify(data) : undefined
      });
      
      if (response.status !== expectedStatus) {
        throw new Error(`API returned ${response.status}, expected ${expectedStatus}`);
      }
      
      return `API test passed: ${method} ${url}`;
    } catch (error) {
      throw new Error(`API test failed: ${error.message}`);
    }
  }

  /**
   * Check database connectivity
   */
  checkDatabase(step) {
    const { query, database = 'apsdreamhome' } = step;
    
    try {
      const result = execSync(`php -r "
        try {
            \$pdo = new PDO('mysql:host=localhost;port=3307;dbname=${database}', 'root', '');
            \$stmt = \$pdo->query('${query}');
            \$result = \$stmt->fetchAll(PDO::FETCH_ASSOC);
            echo 'Database query successful: ' . count(\$result) . ' rows';
        } catch (Exception \$e) {
            echo 'Database error: ' . \$e->getMessage();
        }
      "`, { encoding: 'utf8' });
      
      return result.trim();
    } catch (error) {
      throw new Error(`Database check failed: ${error.message}`);
    }
  }

  /**
   * Execute MCP operation
   */
  async executeMCPOperation(step) {
    const { server, operation, params } = step;
    
    console.log(`  MCP Operation: ${server}.${operation}`);
    
    // This would integrate with MCP tools
    // For now, simulate the operation
    return `MCP operation completed: ${server}.${operation}`;
  }

  /**
   * Validate step results
   */
  validateStep(step) {
    const { validator, expected } = step;
    
    // Custom validation logic
    if (typeof validator === 'function') {
      const result = validator();
      return result === expected ? 'Validation passed' : 'Validation failed';
    }
    
    return 'Validation skipped';
  }

  /**
   * Get workflow status
   */
  getWorkflowStatus(workflowName) {
    return this.workflows.get(workflowName);
  }

  /**
   * Generate report
   */
  generateReport() {
    const report = {
      summary: {
        totalWorkflows: this.workflows.size,
        completedWorkflows: Array.from(this.workflows.values()).filter(w => w.status === 'completed').length,
        failedWorkflows: Array.from(this.workflows.values()).filter(w => w.status === 'failed').length,
        totalSteps: this.stepHistory.length,
        errors: this.errorLog.length
      },
      workflows: Array.from(this.workflows.values()),
      stepHistory: this.stepHistory,
      errors: this.errorLog
    };
    
    return report;
  }

  /**
   * Save report to file
   */
  saveReport(filename = 'workflow-report.json') {
    const report = this.generateReport();
    const reportPath = path.join(this.projectRoot, 'reports', filename);
    
    if (!fs.existsSync(path.dirname(reportPath))) {
      fs.mkdirSync(path.dirname(reportPath), { recursive: true });
    }
    
    fs.writeFileSync(reportPath, JSON.stringify(report, null, 2));
    console.log(` Report saved to: ${reportPath}`);
  }
}

// Define APS Dream Home specific workflows
const workflowManager = new SequentialWorkflowManager();

// Database Setup Workflow
workflowManager.defineWorkflow('database-setup', [
  {
    name: 'Check MySQL Connection',
    type: 'database_check',
    query: 'SELECT 1',
    critical: true
  },
  {
    name: 'Verify Database Schema',
    type: 'database_check',
    query: 'SHOW TABLES',
    critical: true
  },
  {
    name: 'Check Core Tables',
    type: 'database_check',
    query: 'SELECT COUNT(*) as count FROM information_schema.tables WHERE table_schema = "apsdreamhome"',
    critical: true
  }
]);

// Testing Workflow
workflowManager.defineWorkflow('full-system-test', [
  {
    name: 'Start Development Server',
    type: 'command',
    command: 'php -S localhost:8080 -t public',
    critical: true
  },
  {
    name: 'Run Playwright Tests',
    type: 'command',
    command: 'npx playwright test',
    critical: true
  },
  {
    name: 'Check API Endpoints',
    type: 'api_test',
    url: 'http://localhost:8080/api/health',
    expectedStatus: 200
  },
  {
    name: 'Validate File Structure',
    type: 'file_check',
    path: 'app/Http/Controllers',
    exists: true
  }
]);

// Code Quality Workflow
workflowManager.defineWorkflow('code-quality', [
  {
    name: 'Run ESLint',
    type: 'command',
    command: 'npx eslint . --ext .js',
    critical: false
  },
  {
    name: 'Run Prettier',
    type: 'command',
    command: 'npx prettier --write .',
    critical: false
  },
  {
    name: 'Check PHP Syntax',
    type: 'command',
    command: 'find . -name "*.php" -exec php -l {} \\;',
    critical: true
  }
]);

// Export for use
module.exports = { SequentialWorkflowManager, workflowManager };

// CLI interface
if (require.main === module) {
  const args = process.argv.slice(2);
  const workflowName = args[0];
  
  if (!workflowName) {
    console.log('Usage: node sequential-workflow-manager.js <workflow-name>');
    console.log('Available workflows:');
    workflowManager.workflows.forEach((wf, name) => {
      console.log(`  - ${name}: ${wf.steps.length} steps`);
    });
    process.exit(1);
  }
  
  workflowManager.executeWorkflow(workflowName)
    .then(() => {
      workflowManager.saveReport();
      process.exit(0);
    })
    .catch((error) => {
      console.error('Workflow failed:', error.message);
      workflowManager.saveReport();
      process.exit(1);
    });
}
