// Simple Playwright visual test for header UI across breakpoints
// Prerequisites: Node.js and Playwright installed in the project environment
// Install: npm init -y; npm i -D @playwright/test
const { chromium } = require('playwright');

(async () => {
  const browser = await chromium.launch({ headless: true, args: ['--no-sandbox'] });
  const page = await browser.newPage();

  const scenarios = [
    { name: 'Desktop', w: 1280, h: 800 },
    { name: 'Tablet', w: 1024, h: 768 },
    { name: 'Mobile', w: 412, h: 915 },
  ];

  for (const s of scenarios) {
    await page.setViewportSize({ width: s.w, height: s.h });
    await page.goto('http://localhost/apsdreamhome', { waitUntil: 'networkidle' });
    const path = `testing/visual_tests/header_${s.name}.png`;
    await page.screenshot({ path, fullPage: false });
    console.log(`Saved screenshot: ${path}`);
  }

  await browser.close();
})();
