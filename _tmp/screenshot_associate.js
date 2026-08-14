const { chromium } = require('playwright');

(async () => {
  const browser = await chromium.launch();
  const context = await browser.newContext({ viewport: { width: 1920, height: 1080 } });
  const page = await context.newPage();
  
  // Go to associate login
  await page.goto('http://localhost/apsdreamhome/associate/login', { waitUntil: 'networkidle', timeout: 30000 });
  
  // Fill login form
  await page.fill('input[name="email"], input[name="username"], input[type="email"]', 'associate@apsdreamhome.com');
  await page.fill('input[name="password"], input[type="password"]', 'Aps@2026');
  
  // Submit
  await page.click('button[type="submit"], input[type="submit"]');
  await page.waitForLoadState('networkidle', { timeout: 15000 }).catch(() => {});
  
  // Wait a moment for any redirects
  await page.waitForTimeout(2000);
  
  // Take screenshot of whatever page we landed on
  await page.screenshot({ path: 'C:\\xampp\\htdocs\\apsdreamhome\\_tmp\\associate_dashboard.png', fullPage: true });
  console.log('Current URL:', page.url());
  
  await browser.close();
})();
