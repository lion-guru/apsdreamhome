import { chromium } from 'playwright';
import fs from 'fs';
import path from 'path';
import http from 'http';

const BASE = 'http://localhost/apsdreamhome';
const OUT = path.resolve('testing/nightly_vision/shots');
fs.mkdirSync(OUT, { recursive: true });

const PASSWORD = 'Aps@2026';
const ROLES = [
  { name: 'admin', loginUrl: '/admin/login?test_login=1', user: null, pass: null,
    dashMarker: '/admin/',
    pages: ['/admin/dashboard', '/admin/erp', '/admin/users', '/admin/properties', '/admin/plots', '/admin/mlm', '/admin/finance', '/admin/sales/bookings', '/admin/leads', '/admin/settings'] },
  { name: 'customer', loginUrl: '/auth/login', user: 'testuser@example.com', pass: PASSWORD, idField: 'input[name="identity"]', captcha: true,
    dashMarker: '/user/dashboard',
    pages: ['/user/dashboard', '/user/properties', '/user/inquiries', '/user/profile'] },
  { name: 'associate', loginUrl: '/associate/login', user: 'testassociate@example.com', pass: PASSWORD, idField: 'input[name="email"]', captcha: true,
    dashMarker: '/associate/dashboard',
    pages: ['/associate/dashboard'] },
  { name: 'agent', loginUrl: '/agent/login', user: 'agent@apsdreamhome.com', pass: PASSWORD, idField: 'input[name="email"]',
    dashMarker: '/agent/dashboard',
    pages: ['/agent/dashboard'] },
];

const PUBLIC_PAGES = [
  '/', '/properties', '/plots', '/projects', '/services', '/about', '/team',
  '/blog', '/news', '/contact', '/careers', '/faq', '/calc', '/compare',
  '/testimonials', '/tools-hub', '/privacy', '/terms',
];

const manifest = [];
const issues = [];

function slug(s) {
  return s.replace(BASE, '').replace(/^\/+/, '').replace(/[^a-zA-Z0-9]+/g, '_').slice(0, 50) || 'home';
}

function ollama(prompt, imgB64) {
  return new Promise((resolve, reject) => {
    const body = JSON.stringify({ model: 'moondream', prompt, images: imgB64 ? [imgB64] : undefined, stream: false });
    const req = http.request({
      host: 'localhost', port: 11434, path: '/api/generate', method: 'POST',
      headers: { 'Content-Type': 'application/json', 'Content-Length': Buffer.byteLength(body) },
      timeout: 180000,
    }, res => {
      let data = '';
      res.on('data', c => { data += c; });
      res.on('end', () => {
        try { resolve((JSON.parse(data).response || '').trim()); } catch (e) { reject(new Error('bad json')); }
      });
    });
    req.on('error', reject);
    req.on('timeout', () => req.destroy(new Error('timeout')));
    req.write(body);
    req.end();
  });
}

async function shot(page, tag) {
  const file = `${tag}.png`;
  await page.screenshot({ path: path.join(OUT, file), fullPage: false });
  manifest.push({ tag, url: page.url(), title: await page.title().catch(() => ''), file });
}

async function ocrCaptcha(page) {
  const img = page.locator('img[src*="captcha"]').first();
  if (!(await img.count())) return null;
  const buf = await img.screenshot();
  const raw = await ollama('Read the text characters shown in this image. Reply with ONLY the characters, nothing else.', buf.toString('base64'));
  return raw.replace(/[^A-Za-z0-9]/g, '').toUpperCase();
}

// Read captcha code directly from the PHP session file on disk (dev machine only)
async function solveCaptchaFromSession(ctx) {
  const cookies = await ctx.cookies(BASE);
  const sid = (cookies.find(c => c.name === 'PHPSESSID') || {}).value;
  if (!sid) return null;
  const sessFile = `C:\\xampp\\tmp\\sess_${sid}`;
  if (!fs.existsSync(sessFile)) return null;
  const content = fs.readFileSync(sessFile, 'utf8');
  const m = content.match(/captcha_code\|s:\d+:"([^"]+)"/);
  return m ? m[1] : null;
}

async function tryLogin(browser, role) {
  const res = { ok: false, detail: '' };
  const ctx = await browser.newContext({ viewport: { width: 1366, height: 768 } }); // FRESH context per role
  let page = await ctx.newPage();
  try {
    if (!role.user) {
      await page.goto(BASE + role.loginUrl, { waitUntil: 'domcontentloaded', timeout: 45000 });
      await page.waitForTimeout(2500);
      res.ok = page.url().includes('/admin/dashboard') || page.url().includes('/admin/');
      res.detail = `bypass -> ${page.url()}`;
      return { res, ctx };
    }
    for (let attempt = 1; attempt <= 3 && !res.ok; attempt++) {
      await page.goto(BASE + role.loginUrl, { waitUntil: 'domcontentloaded', timeout: 45000 });
      const idSel = role.idField;
      await page.waitForSelector(idSel, { timeout: 15000 });
      await page.fill(idSel, role.user);
      await page.fill('input[name="password"]', role.pass);
      if (role.captcha) {
        const code = await solveCaptchaFromSession(ctx);
        console.log(`  [${role.name}] captcha attempt ${attempt}: session="${code ? 'found' : 'MISSING'}"`);
        if (!code) { await page.waitForTimeout(1000); continue; }
        const capInput = page.locator('input[name="captcha_code"]');
        if (!(await capInput.count())) { continue; }
        await capInput.first().fill(code);
      }
      await Promise.all([
        page.waitForNavigation({ waitUntil: 'domcontentloaded', timeout: 30000 }).catch(() => {}),
        page.click('form button[type="submit"]:not([class*="toggle"])'),
      ]);
      await page.waitForTimeout(2500);
      res.ok = page.url().includes(role.dashMarker);
      res.detail = `attempt ${attempt} -> ${page.url()}`;
    }
  } catch (e) {
    res.detail = `ERR ${e.message.slice(0, 120)}`;
  }
  return { res, ctx };
}

async function auditPage(page, scope, p, shotTag) {
  const resp = await page.goto(BASE + p, { waitUntil: 'domcontentloaded', timeout: 45000 });
  await page.waitForTimeout(2000);
  const status = resp ? resp.status() : 0;
  const finalUrl = page.url();
  const broken = await page.evaluate(() =>
    Array.from(document.querySelectorAll('img'))
      .filter(i => i.complete && i.naturalWidth === 0 && i.src.indexOf('data:') !== 0).length);
  const bouncedToLogin = /\/(login|auth\/login)(\?|$)/.test(finalUrl.replace(BASE, ''));
  if (status !== 200 || broken > 0 || bouncedToLogin) {
    issues.push({ scope, page: p, type: bouncedToLogin ? 'auth_bounce' : status !== 200 ? 'http_status' : 'broken_images', detail: `status=${status} brokenImgs=${broken} url=${finalUrl}` });
  }
  await shot(page, shotTag || `${scope}__${slug(p)}`);
  console.log(`[${status}] ${p} brokenImgs=${broken}${bouncedToLogin ? ' BOUNCED-TO-LOGIN' : ''}`);
}

async function run() {
  const browser = await chromium.launch({ headless: true });

  // ---------- Public pages ----------
  let ctx = await browser.newContext();
  let page = await ctx.newPage();
  console.log('=== PUBLIC PAGES ===');
  for (const p of PUBLIC_PAGES) {
    try {
      await auditPage(page, 'public', p);
    } catch (e) {
      issues.push({ scope: 'public', page: p, type: 'exception', detail: e.message.slice(0, 150) });
      console.log(`[ERR] ${p}: ${e.message.slice(0, 100)}`);
    }
  }

  // ---------- Workflows ----------
  console.log('\n=== WORKFLOW CLICKS ===');
  try {
    await page.goto(BASE + '/properties', { waitUntil: 'domcontentloaded' });
    await page.waitForTimeout(1800);
    // login as customer first so compare/favorites work end-to-end
    await page.goto(BASE + '/properties', { waitUntil: 'domcontentloaded' });
    const cmp = page.locator('.add-to-compare').first();
    if (await cmp.count()) {
      await cmp.click();
      await page.waitForTimeout(1500);
      const badge = ((await page.evaluate(() => (document.getElementById('compareBadge') || {}).textContent)) || '').trim();
      console.log(`Compare badge after click: "${badge}"`);
      if (/object|NaN/.test(badge)) issues.push({ scope: 'workflow', page: '/properties', type: 'compare_badge_text', detail: badge });
    }
  } catch (e) { /* non-fatal */ }

  try {
    await page.goto(BASE + '/', { waitUntil: 'domcontentloaded' });
    await page.waitForTimeout(1200);
    const searchInput = page.locator('#heroSearch, input[name="search"], input[name="q"]').first();
    if (await searchInput.count()) {
      await searchInput.fill('plot');
      await Promise.all([
        page.waitForNavigation({ waitUntil: 'domcontentloaded', timeout: 20000 }).catch(() => {}),
        searchInput.press('Enter'),
      ]);
      await page.waitForTimeout(1500);
      console.log(`Hero search -> ${page.url()}`);
      await shot(page, 'public__search_results');
    } else {
      console.log('No hero search input found');
    }
  } catch (e) { issues.push({ scope: 'workflow', page: '/', type: 'search_fail', detail: e.message.slice(0, 120) }); }

  await ctx.close();

  // ---------- Role logins + dashboards ----------
  for (const role of ROLES) {
    console.log(`\n=== ROLE: ${role.name} ===`);
    const { res, ctx: rctx } = await tryLogin(browser, role);
    console.log(`login ${res.ok ? 'OK' : 'FAIL'}: ${res.detail}`);
    if (!res.ok) issues.push({ scope: role.name, page: role.loginUrl, type: 'login_failed', detail: res.detail });
    const rp = await rctx.newPage();
    for (const p of role.pages) {
      try {
        await auditPage(rp, role.name, p);
      } catch (e) {
        issues.push({ scope: role.name, page: p, type: 'exception', detail: e.message.slice(0, 150) });
        console.log(`[ERR] ${p}: ${e.message.slice(0, 100)}`);
      }
    }
    await rp.close().catch(() => {});
    await rctx.close().catch(() => {}); // kills session cookies too
  }

  await browser.close();

  fs.writeFileSync(path.resolve('testing/nightly_vision/manifest.json'), JSON.stringify(manifest, null, 2));
  fs.writeFileSync(path.resolve('testing/nightly_vision/issues_auto.json'), JSON.stringify(issues, null, 2));
  console.log(`\nDONE: ${manifest.length} screenshots, ${issues.length} auto-detected issues`);
}

run().catch(e => { console.error(e); process.exit(1); });
