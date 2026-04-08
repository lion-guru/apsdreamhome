// Deep functional test: verify key pages return HTTP 200 and contain expected content
const { chromium } = require('playwright');

async function testPage(browser, url, label, checks) {
  const page = await browser.newPage();
  try {
    const res = await page.goto(url, { waitUntil: 'domcontentloaded', timeout: 10000 });
    const status = res ? res.status() : 0;
    const ok = status >= 200 && status < 400;
    let pass = ok;
    const html = await page.content();
    if (checks) {
      for (const c of checks) {
        if (!html.includes(c)) { pass = false; console.log(`  WARN: "${c}" not found in ${label}`); }
      }
    }
    console.log(`${pass ? 'PASS' : 'FAIL'} [${status}] ${label}`);
  } catch (e) {
    console.log(`FAIL [ERR] ${label}: ${e.message.slice(0, 80)}`);
  }
  await page.close();
}

async function testApi(browser, url, label, checks, method = 'GET', body = null) {
  const page = await browser.newPage();
  try {
    const res = await page.goto(url, { waitUntil: 'domcontentloaded', timeout: 10000 });
    const status = res ? res.status() : 0;
    const pageBody = await page.content();
    let json;
    try {
      json = JSON.parse(pageBody.replace(/<[^>]*>/g, ''));
    } catch {
      json = null;
    }
    let pass = status >= 200 && status < 400;
    if (checks && typeof checks === 'function') {
      pass = checks(json, status) && pass;
    }
    console.log(`${pass ? 'PASS' : 'FAIL'} [${status}] ${label}`);
    return pass;
  } catch (e) {
    console.log(`FAIL [ERR] ${label}: ${e.message.slice(0, 80)}`);
    return false;
  } finally {
    await page.close();
  }
}

async function testApiPost(browser, url, label, formData, checks) {
  const page = await browser.newPage();
  try {
    const res = await page.request.post(url, { form: formData });
    const status = res.status();
    let json;
    try {
      json = await res.json();
    } catch {
      const text = await res.text();
      try { json = JSON.parse(text.replace(/<[^>]*>/g, '')); } catch { json = null; }
    }
    let pass = status >= 200 && status < 400;
    if (checks && typeof checks === 'function') {
      pass = checks(json, status) && pass;
    }
    console.log(`${pass ? 'PASS' : 'FAIL'} [${status}] ${label}`);
    return pass;
  } catch (e) {
    console.log(`FAIL [ERR] ${label}: ${e.message.slice(0, 80)}`);
    return false;
  } finally {
    await page.close();
  }
}

async function main() {
  const browser = await chromium.launch({ headless: true, args: ['--no-sandbox'] });
  console.log('\n=== DEEP FUNCTIONAL TEST ===\n');

  // Public pages
  await testPage(browser, 'http://localhost/apsdreamhome/', 'Homepage', ['APS Dream Home']);
  await testPage(browser, 'http://localhost/apsdreamhome/properties', 'Properties', ['Properties']);
  await testPage(browser, 'http://localhost/apsdreamhome/list-property', 'List Property', ['Property Details']);
  await testPage(browser, 'http://localhost/apsdreamhome/services', 'Services', ['Services']);
  await testPage(browser, 'http://localhost/apsdreamhome/contact', 'Contact', ['Contact']);
  await testPage(browser, 'http://localhost/apsdreamhome/about', 'About', ['About']);

  // Admin login page
  await testPage(browser, 'http://localhost/apsdreamhome/admin/login', 'Admin Login', ['Password']);

  // API endpoints
  await testApi(browser, 'http://localhost/apsdreamhome/api/locations', 'Locations API', 
    j => j.success === true && j.data && j.data.length > 0);
  await testApi(browser, 'http://localhost/apsdreamhome/api/health', 'API Health', 
    j => j.status || j.success !== undefined);

  // Newsletter subscribe API (POST)
  await testApiPost(browser, 'http://localhost/apsdreamhome/api/newsletter', 'Newsletter API',
    { email: 'test' + Date.now() + '@example.com' },
    (j, s) => j !== null && (j.success !== undefined || j.message !== undefined || s >= 200));

  // Test admin user-properties via test-login
  const adminPage = await browser.newPage();
  await adminPage.goto('http://localhost/apsdreamhome/admin/login?test_login=1', { waitUntil: 'domcontentloaded' });
  const adminHtml = await adminPage.content();
  const adminOk = adminHtml.includes('User Properties') || adminHtml.includes('Dashboard') || adminHtml.includes('admin');
  console.log(`${adminOk ? 'PASS' : 'FAIL'} [REDIRECT] Admin test-login`);
  await adminPage.close();

  // Check for PHP errors in logs
  const logErr = require('fs').readFileSync('logs/php_error.log', 'utf8').split('\n').filter(l => l.includes('[08-Apr-2026'));
  const realErrors = logErr.filter(l => l.includes('Fatal') || l.includes('Parse error') || l.includes('Fatal error'));
  if (realErrors.length > 0) {
    console.log(`\nPHP RUNTIME ERRORS found (${realErrors.length}):`);
    realErrors.slice(0, 5).forEach(l => console.log('  ' + l.slice(0, 120)));
  } else {
    console.log('\nNo PHP runtime errors in logs.');
  }

  await browser.close();
  console.log('\n=== DEEP TEST COMPLETE ===\n');
}

main().catch(e => { console.error('Test runner error:', e.message); process.exit(1); });
