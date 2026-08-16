const puppeteer = require('puppeteer');

const BASE = 'http://localhost/apsdreamhome';
const SHOT = 'C:/Users/abhay/AppData/Local/Temp/aps_screenshots';

const roles = [
  { name: 'admin',     code: '1', dash: '/admin/dashboard' },
  { name: 'customer',  code: '7', dash: '/user/dashboard' },
  { name: 'associate', code: '5', dash: '/associate/dashboard' },
  { name: 'agent',     code: '6', dash: '/agent/dashboard' },
  { name: 'employee',  code: '4', dash: '/employee/dashboard' },
  { name: 'ceo',       code: '2', dash: '/admin/dashboard' },
];

(async () => {
  const browser = await puppeteer.launch({
    executablePath: 'C:/Program Files/Google/Chrome/Application/chrome.exe',
    headless: true,
    args: ['--no-sandbox', '--disable-setuid-sandbox']
  });

  for (const role of roles) {
    const page = await browser.newPage();
    await page.setViewport({ width: 1440, height: 900 });

    try {
      // Use test_login bypass - redirects to /admin/dashboard for all
      await page.goto(BASE + '/admin/login?test_login=' + role.code, { waitUntil: 'domcontentloaded', timeout: 15000 });
      await new Promise(r => setTimeout(r, 3000));
      
      const adminUrl = page.url();
      console.log(role.name + ' after test_login: ' + adminUrl);
      
      // Navigate to the correct dashboard
      await page.goto(BASE + role.dash, { waitUntil: 'domcontentloaded', timeout: 15000 });
      await new Promise(r => setTimeout(r, 3000));
      
      console.log(role.name + ' dashboard: ' + page.url());
      await page.screenshot({ path: SHOT + '/role_' + role.name + '_dashboard.png', fullPage: false });
      console.log('  Saved: role_' + role.name + '_dashboard.png');

      // Logout
      await page.goto(BASE + '/auth/logout', { waitUntil: 'domcontentloaded', timeout: 10000 }).catch(() => {});
      await new Promise(r => setTimeout(r, 1000));

    } catch (e) {
      console.log(role.name + ' ERROR: ' + e.message.substring(0, 120));
    }

    await page.close();
  }

  await browser.close();
  console.log('\nDone');
})();
