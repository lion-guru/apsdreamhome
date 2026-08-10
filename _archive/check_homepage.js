const { chromium } = require('playwright');
(async () => {
  const browser = await chromium.launch({ headless: false });
  const ctx = await browser.newContext({
    viewport: { width: 1920, height: 1080 }
  });
  const page = await ctx.newPage();
  const errors = [];
  const notFound = [];
  const consoleMessages = [];
  page.on('pageerror', err => errors.push(err.message));
  page.on('response', resp => { if (resp.status() === 404) notFound.push(resp.url()); });
  page.on('console', msg => {
    if (msg.type() === 'error') consoleMessages.push(msg.text());
  });
  await page.goto('http://localhost/apsdreamhome/', { waitUntil: 'domcontentloaded', timeout: 30000 });
  await page.waitForTimeout(3000);
  
  console.log('=== JS ERRORS ===');
  errors.forEach(e => console.log(e));
  console.log('Total JS errors:', errors.length);
  
  console.log('=== CONSOLE ERRORS ===');
  consoleMessages.forEach(e => console.log(e));
  console.log('Total console errors:', consoleMessages.length);
  
  console.log('=== 404s ===');
  notFound.forEach(n => console.log(n));
  console.log('Total 404s:', notFound.length);

  // Screenshot the homepage
  await page.screenshot({ path: 'C:\\Users\\abhay\\AppData\\Local\\Temp\\opencode\\homepage.png', fullPage: true });
  console.log('Homepage screenshot saved');
  
  await browser.close();
})();
