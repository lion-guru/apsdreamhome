#!/usr/bin/env node

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
  console.log(`Playwright debug session ended with code ${code}`);
});

playwright.on('error', (error) => {
  console.error('Playwright debug error:', error);
});
