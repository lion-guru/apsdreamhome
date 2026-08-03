const { chromium } = require('playwright');
const fs = require('fs');

(async () => {
  const browser = await chromium.launch();
  const context = await browser.newContext();
  const page = await context.newPage();

  // Login first
  await page.goto('http://localhost/apsdreamhome/admin/login?test_login=1');
  await page.waitForURL('**/admin/erp');

  const urls = fs.readFileSync('C:\\Users\\abhay\\AppData\\Local\\Temp\\admin_urls.txt', 'utf8')
    .trim().split('\n').filter(Boolean);

  let pass = 0, fail = 0;
  let failedUrls = [];

  for (const path of urls) {
    try {
      const response = await page.goto('http://localhost/apsdreamhome' + path, {
        waitUntil: 'domcontentloaded', timeout: 15000
      });
      const status = response.status();
      if (status === 200) {
        pass++;
      } else if (status === 302) {
        // Redirect (e.g. to login) - still counts as pass if logged in
        pass++;
      } else {
        fail++;
        failedUrls.push(path + ' => ' + status);
        console.log('FAIL ' + path + ' => ' + status);
      }
    } catch (e) {
      fail++;
      failedUrls.push(path + ' => TIMEOUT');
      console.log('FAIL ' + path + ' => TIMEOUT');
    }
  }

  console.log('\n=== RESULTS ===');
  console.log('Total: ' + urls.length);
  console.log('Pass: ' + pass);
  console.log('Fail: ' + fail);
  if (failedUrls.length > 0) {
    console.log('\nFailed URLs:');
    failedUrls.forEach(u => console.log('  ' + u));
    fs.writeFileSync('C:\\Users\\abhay\\AppData\\Local\\Temp\\failed_urls.txt', failedUrls.join('\n'));
  }

  await browser.close();
})();
