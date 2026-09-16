import { defineConfig } from '@playwright/test';

export default defineConfig({
  testDir: './testing/visual_tests',
  testMatch: '**/visual_regression.spec.js',
  timeout: 60000,
  expect: {
    timeout: 10000,
  },
  use: {
    baseURL: process.env.BASE_URL || 'http://localhost/apsdreamhome',
    trace: 'on-first-retry',
  },
  reporter: 'line',
});