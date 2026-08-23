import { chromium } from 'playwright';

const BASE = 'http://localhost/apsdreamhome';
const browser = await chromium.launch({ headless: true });
const ctx = await browser.newContext();
const page = await ctx.newPage();

page.on('request', r => { if (r.method() === 'POST') console.log('POST', r.url()); });
page.on('response', r => {
  if (r.request().method() === 'POST') {
    console.log('RESP', r.status(), r.url(), 'location=', r.headers()['location'] || '-');
  }
});

await page.goto(BASE + '/associate/login', { waitUntil: 'domcontentloaded' });
await page.fill('input[name="email"]', 'testassociate@example.com');
await page.fill('input[name="password"]', 'Aps@2026');

const forms = await page.evaluate(() => Array.from(document.querySelectorAll('form')).map(f => ({
  id: f.id, action: f.action, method: f.method,
  submitBtns: Array.from(f.querySelectorAll('button[type="submit"]')).map(b => ({ id: b.id, text: b.textContent.trim().slice(0, 30) }))
})));
console.log('forms:', JSON.stringify(forms, null, 1));

await Promise.all([
  page.waitForNavigation({ waitUntil: 'domcontentloaded', timeout: 30000 }).catch(e => console.log('nav err:', e.message.slice(0, 60))),
  page.click('#submitBtn'),
]);
await page.waitForTimeout(3000);
console.log('final:', page.url());
const flash = await page.evaluate(() =>
  Array.from(document.querySelectorAll('.alert,.error,[class*="alert"],[role="alert"],div[class*="error"]'))
    .map(e => e.textContent.trim().slice(0, 150)).filter(Boolean));
console.log('flash:', JSON.stringify(flash));
// session state via JS
console.log('cookies:', (await ctx.cookies()).map(c => c.name).join(','));
await browser.close();
