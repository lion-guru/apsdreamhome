import { test, expect } from '@playwright/test';

test.describe('Basic Playwright Tests', () => {
  test('has title', async ({ page }) => {
    await page.goto('https://playwright.dev/');
    await expect(page).toHaveTitle(/Playwright/);
  });

  test('get started link', async ({ page }) => {
    await page.goto('https://playwright.dev/');
    await page.getByRole('link', { name: 'Get started' }).click();
    await expect(page.getByRole('heading', { name: 'Installation' })).toBeVisible();
  });

  test('local APS Dream Home test', async ({ page }) => {
    // Test if local server is running
    const response = await page.goto('http://localhost/apsdreamhome');
    
    if (response && response.status() === 200) {
      console.log('Local server is running!');
      await expect(page.locator('body')).toBeVisible();
    } else {
      console.log('Local server not running - skipping local tests');
      test.skip();
    }
  });
});
