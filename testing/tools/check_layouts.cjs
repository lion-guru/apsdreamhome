const { chromium } = require('playwright');
(async () => {
  const browser = await chromium.launch({ headless: true });
  const urls = ['/login', '/register', '/associate/login', '/employee/login', '/list-property', '/services', '/contact', '/support', '/whatsapp-chat'];
  for (const url of urls) {
    const page = await browser.newPage({ viewport: { width: 390, height: 844 } });
    await page.goto('http://localhost/apsdreamhome' + url, { waitUntil: 'load', timeout: 10000 });
    await page.waitForTimeout(1000);
    const result = await page.evaluate(() => {
      return {
        hasAI: !!document.querySelector('.ai-float-btn'),
        hasWA: !!document.querySelector('.whatsapp-float-btn'),
        hasBaseChatbot: !!document.querySelector('#ai-chatbot'),
        hasHeader: !!document.querySelector('#mainHeader'),
      };
    });
    console.log(url + ': AI=' + (result.hasAI ? 'YES' : 'NO') + ' WA=' + (result.hasWA ? 'YES' : 'NO') + ' baseLayout=' + (result.hasBaseChatbot ? 'YES' : 'NO') + ' header=' + (result.hasHeader ? 'YES' : 'NO'));
    await page.close();
  }
  await browser.close();
})();
