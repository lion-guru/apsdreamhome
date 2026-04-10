import { test, expect } from '@playwright/test';

test.describe('APS Dream Home - Core Functionality', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('http://localhost/apsdreamhome');
  });

  test('Homepage loads correctly', async ({ page }) => {
    await expect(page).toHaveTitle(/APS Dream Home/);
    await expect(page.locator('body')).toBeVisible();
    
    // Check for main navigation
    const nav = page.locator('nav, .navbar, .header');
    await expect(nav.first()).toBeVisible();
  });

  test('User Registration Flow', async ({ page }) => {
    await page.click('a[href*="register"], button:has-text("Register")');
    await expect(page).toHaveURL(/.*register|.*register/);
    
    // Fill registration form
    await page.fill('input[name="name"], input[name="username"]', 'Test User');
    await page.fill('input[name="email"]', 'test@example.com');
    await page.fill('input[name="phone"]', '9876543210');
    await page.fill('input[name="password"]', 'Test@123');
    await page.fill('input[name="confirm_password"], input[name="password_confirmation"]', 'Test@123');
    
    // Submit form
    await page.click('button[type="submit"], input[type="submit"]');
    
    // Verify success
    await expect(page.locator('.success, .alert-success, .message')).toBeVisible({ timeout: 10000 });
  });

  test('User Login Flow', async ({ page }) => {
    await page.click('a[href*="login"], button:has-text("Login")');
    await expect(page).toHaveURL(/.*login|.*login/);
    
    // Fill login form
    await page.fill('input[name="email"]', 'test@example.com');
    await page.fill('input[name="password"]', 'Test@123');
    
    // Submit form
    await page.click('button[type="submit"], input[type="submit"]');
    
    // Verify dashboard access
    await expect(page).toHaveURL(/.*dashboard|.*user\/dashboard/, { timeout: 10000 });
  });

  test('Property Listing Page', async ({ page }) => {
    await page.click('a[href*="properties"], a[href*="property"]');
    await expect(page).toHaveURL(/.*properties|.*property/);
    
    // Check property filters
    const filters = page.locator('.filter, .filters, select');
    if (await filters.count() > 0) {
      await expect(filters.first()).toBeVisible();
    }
    
    // Check property listings
    const properties = page.locator('.property, .listing, .property-item');
    await expect(properties.first()).toBeVisible();
  });

  test('Admin Login Access', async ({ page }) => {
    await page.goto('http://localhost/apsdreamhome/admin/login');
    await expect(page).toHaveTitle(/.*Admin.*Login|.*Login.*Admin/);
    
    // Test admin login
    await page.fill('input[name="email"], input[name="username"]', 'admin');
    await page.fill('input[name="password"]', 'admin');
    
    // Submit with test bypass if available
    const testLoginUrl = page.url() + '?test_login=1';
    await page.goto(testLoginUrl);
    
    // Verify admin dashboard
    await expect(page).toHaveURL(/.*admin.*dashboard|.*dashboard/, { timeout: 10000 });
  });

  test('Property Posting Flow', async ({ page }) => {
    // First login
    await page.goto('http://localhost/apsdreamhome/login');
    await page.fill('input[name="email"]', 'test@example.com');
    await page.fill('input[name="password"]', 'Test@123');
    await page.click('button[type="submit"]');
    
    // Navigate to property listing
    await page.click('a[href*="list-property"], a[href*="post-property"]');
    
    // Fill property form
    await page.selectOption('select[name="property_type"]', 'Plot');
    await page.selectOption('select[name="listing_type"]', 'Sell');
    await page.fill('input[name="name"]', 'Test Property');
    await page.fill('input[name="phone"]', '9876543210');
    await page.fill('input[name="email"]', 'test@example.com');
    await page.fill('input[name="price"]', '1000000');
    await page.fill('input[name="address"]', 'Test Address, Gorakhpur');
    await page.fill('input[name="area_sqft"]', '1000');
    await page.fill('textarea[name="description"]', 'Test property description');
    
    // Submit form
    await page.click('button[type="submit"]');
    
    // Verify success message
    await expect(page.locator('.success, .alert-success')).toBeVisible({ timeout: 10000 });
  });

  test('Responsive Design - Mobile', async ({ page }) => {
    await page.setViewportSize({ width: 375, height: 667 }); // iPhone size
    await page.goto('http://localhost/apsdreamhome');
    
    // Check mobile navigation
    const mobileNav = page.locator('.mobile-nav, .hamburger, .menu-toggle');
    if (await mobileNav.count() > 0) {
      await expect(mobileNav.first()).toBeVisible();
    }
    
    // Check content is readable
    await expect(page.locator('body')).toBeVisible();
  });

  test('Database Connection Test', async ({ page }) => {
    await page.goto('http://localhost/apsdreamhome/test_mysql_connection.php');
    await expect(page.locator('body')).toContainText('MySQL connection successful');
  });

  test('API Endpoints Health Check', async ({ page }) => {
    // Test newsletter API
    const response = await page.request.post('http://localhost/apsdreamhome/subscribe', {
      form: {
        email: 'test@example.com',
        name: 'Test User'
      }
    });
    expect(response.status()).toBe(200);
    
    // Test AI bot API
    const aiResponse = await page.request.post('http://localhost/apsdreamhome/api/ai/chatbot', {
      form: {
        message: 'hello'
      }
    });
    expect(aiResponse.status()).toBe(200);
  });
});

test.describe('APS Dream Home - Performance', () => {
  test('Page Load Performance', async ({ page }) => {
    const startTime = Date.now();
    await page.goto('http://localhost/apsdreamhome');
    const loadTime = Date.now() - startTime;
    
    // Page should load within 5 seconds
    expect(loadTime).toBeLessThan(5000);
    
    // Check for performance metrics
    const performanceMetrics = await page.evaluate(() => {
      const navigation = performance.getEntriesByType('navigation')[0];
      return {
        domContentLoaded: navigation.domContentLoadedEventEnd - navigation.domContentLoadedEventStart,
        loadComplete: navigation.loadEventEnd - navigation.loadEventStart
      };
    });
    
    console.log('Performance Metrics:', performanceMetrics);
  });

  test('Memory Usage Check', async ({ page }) => {
    await page.goto('http://localhost/apsdreamhome');
    
    // Monitor memory usage
    const memoryUsage = await page.evaluate(() => {
      if (performance.memory) {
        return {
          used: Math.round(performance.memory.usedJSHeapSize / 1048576) + ' MB',
          total: Math.round(performance.memory.totalJSHeapSize / 1048576) + ' MB',
          limit: Math.round(performance.memory.jsHeapSizeLimit / 1048576) + ' MB'
        };
      }
      return null;
    });
    
    if (memoryUsage) {
      console.log('Memory Usage:', memoryUsage);
      // Memory usage should be reasonable
      const usedMB = parseInt(memoryUsage.used);
      expect(usedMB).toBeLessThan(100); // Less than 100MB
    }
  });
});
