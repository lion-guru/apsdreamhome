// Extended page test - check more pages for errors
const { chromium } = require('playwright');

async function testPage(browser, url, label, checks) {
  const page = await browser.newPage();
  try {
    const res = await page.goto(url, { waitUntil: 'domcontentloaded', timeout: 10000 });
    const status = res ? res.status() : 0;
    const html = await page.content();
    let pass = status >= 200 && status < 400;
    if (checks) {
      for (const c of checks) {
        if (!html.includes(c)) { pass = false; console.log(`  WARN: "${c}" not found in ${label}`); }
      }
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
  console.log('\n=== EXTENDED PAGE TEST ===\n');

  // More public pages
  await testPage(browser, 'http://localhost/apsdreamhome/about', 'About', ['About']);
  await testPage(browser, 'http://localhost/apsdreamhome/services', 'Services', ['Services']);
  await testPage(browser, 'http://localhost/apsdreamhome/projects', 'Projects', ['Projects']);
  await testPage(browser, 'http://localhost/apsdreamhome/plots', 'Plots', ['Plot']);
  await testPage(browser, 'http://localhost/apsdreamhome/testimonials', 'Testimonials', ['Testimonial']);
  await testPage(browser, 'http://localhost/apsdreamhome/team', 'Team', ['Team']);
  await testPage(browser, 'http://localhost/apsdreamhome/invest', 'Invest', ['Invest']);
  await testPage(browser, 'http://localhost/apsdreamhome/terms', 'Terms', ['Terms']);
  await testPage(browser, 'http://localhost/apsdreamhome/privacy', 'Privacy', ['Privacy']);

  // AI Bot chat API
  await testApiPost(browser, 'http://localhost/apsdreamhome/api/ai/chatbot', 'AI Chatbot API',
    { message: 'I want to buy a plot', lang: 'en' },
    (j, s) => j !== null && (j.success !== undefined || j.reply !== undefined || s >= 200));

  // Test /plots page
  await testPage(browser, 'http://localhost/apsdreamhome/plots', 'Plots Page', ['Plot', 'Available']);

  await browser.close();
  console.log('\n=== EXTENDED TEST COMPLETE ===\n');
}

main().catch(e => { console.error('Test runner error:', e.message); process.exit(1); });
