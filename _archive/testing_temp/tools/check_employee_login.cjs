const { chromium } = require('playwright');
(async () => {
  const browser = await chromium.launch({ headless: true });
  const page = await browser.newPage();
  await page.goto('http://localhost/apsdreamhome/employee/login', { waitUntil: 'load', timeout: 10000 });
  await page.waitForTimeout(1000);
  const html = await page.content();
  console.log('Has DOCTYPE:', html.includes('<!DOCTYPE'));
  console.log('Has ai-chatbot:', html.includes('ai-chatbot'));
  console.log('Has ai-float-btn:', html.includes('ai-float-btn'));
  console.log('Has whatsapp-float-btn:', html.includes('whatsapp-float-btn'));
  console.log('Has chatbot.css:', html.includes('chatbot.css'));
  console.log('Has premium-header:', html.includes('premium-header'));
  await browser.close();
})();
