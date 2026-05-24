// UI/UX Deep Audit Script
import { chromium } from 'playwright';
import { writeFileSync, mkdirSync } from 'fs';

const BASE = 'http://localhost/apsdreamhome';
const OUT = 'audit_results';
mkdirSync(OUT, { recursive: true });

const RESULTS = [];
let pageNum = 0;

function check(name, pass, detail = '') {
  const status = pass ? 'PASS' : 'FAIL';
  RESULTS.push({ name, status, detail });
  const icon = pass ? '  ' : '  ';
  console.log(`${icon} ${status}: ${name}${detail ? ' - ' + detail : ''}`);
}

async function snapshot(page, label) {
  pageNum++;
  const path = `${OUT}/${String(pageNum).padStart(2, '0')}_${label.replace(/[^a-z0-9]/gi, '_').toLowerCase().slice(0, 50)}.png`;
  await page.screenshot({ path, fullPage: true });
  return path;
}

async function visit(page, url, label, expectedStatus = 200) {
  const errors = [];
  page.on('console', msg => {
    if (msg.type() === 'error') errors.push(msg.text());
  });
  page.on('pageerror', err => errors.push(err.message));
  
  try {
    const resp = await page.goto(url, { waitUntil: 'load', timeout: 20000 });
    await page.waitForTimeout(500);
    const status = resp ? resp.status() : 0;
    await snapshot(page, label);
    
    check(`${label} status`, status === expectedStatus, `HTTP ${status}`);
    check(`${label} console errors`, errors.length === 0, errors.slice(0, 3).join('; '));
    
    // Check viewport
    const hasViewport = (await page.content()).includes('width=device-width');
    check(`${label} viewport`, hasViewport);
    
    // Check images
    const brokenImgs = await page.evaluate(() => 
      Array.from(document.querySelectorAll('img'))
        .filter(img => !img.complete || img.naturalWidth === 0)
        .map(img => img.src?.substring(0, 80) || '(no src)')
    );
    check(`${label} broken images`, brokenImgs.length === 0, brokenImgs.join(', '));
    
    // Check empty hrefs
    const emptyLinks = await page.evaluate(() => 
      Array.from(document.querySelectorAll('a[href]')).filter(a => !a.href || a.href.endsWith('#') || a.href === 'about:blank').length
    );
    check(`${label} empty hrefs`, emptyLinks === 0, `${emptyLinks} empty links`);
    
    // Check for visible layout issues
    const overlaps = await page.evaluate(() => {
      const all = Array.from(document.querySelectorAll('*'));
      let overlapCount = 0;
      for (let i = 0; i < Math.min(all.length, 200); i++) {
        const el = all[i];
        const r = el.getBoundingClientRect();
        if (r.width === 0 || r.height === 0) continue;
        if (r.left < -5 || r.top < -5 || r.right > window.innerWidth + 5) {
          if (el.tagName !== 'HTML' && el.tagName !== 'BODY' && !el.classList.contains('position-fixed')) {
            overlapCount++;
          }
        }
      }
      return overlapCount;
    });
    check(`${label} overflow/offscreen`, overlaps < 5, `${overlaps} elements offscreen`);
    
  } catch (err) {
    check(`${label} page load`, false, err.message);
  }
}

async function main() {
  const browser = await chromium.launch({ headless: true });
  const context = await browser.newContext({ viewport: { width: 1280, height: 800 } });
  const page = await context.newPage();

  console.log('\n=== PUBLIC PAGES ===\n');
  await visit(page, `${BASE}/`, 'Homepage');
  await visit(page, `${BASE}/properties`, 'Properties');
  await visit(page, `${BASE}/plots`, 'Plots');
  await visit(page, `${BASE}/services`, 'Services');
  await visit(page, `${BASE}/contact`, 'Contact');
  await visit(page, `${BASE}/list-property`, 'ListProperty');
  
  console.log('\n=== AUTH PAGES ===\n');
  await visit(page, `${BASE}/login`, 'Login');
  await visit(page, `${BASE}/register`, 'Register');
  
  // Login flow  
  await page.goto(`${BASE}/login`, { waitUntil: 'load' });
  await page.fill('input[name="identity"]', 'testuser@example.com');
  await page.fill('input[name="password"]', 'Test@123');
  await Promise.all([
    page.waitForNavigation({ waitUntil: 'load', timeout: 15000 }),
    page.click('button[type="submit"]')
  ]);
  await page.waitForTimeout(500);
  check('Login flow', !page.url().includes('login'), `Redirected to: ${page.url()}`);
  
  console.log('\n=== USER PAGES (logged in) ===\n');
  await visit(page, `${BASE}/user/dashboard`, 'User_Dashboard');
  await visit(page, `${BASE}/user/properties`, 'User_Properties');
  await visit(page, `${BASE}/user/inquiries`, 'User_Inquiries');
  await visit(page, `${BASE}/user/profile`, 'User_Profile');
  await visit(page, `${BASE}/user/bookings`, 'User_Bookings');
  
  // Admin login
  await page.goto(`${BASE}/admin/login?test_login=1`, { waitUntil: 'load' });
  await page.waitForTimeout(1000);
  check('Admin login bypass', !page.url().includes('admin/login'), page.url());
  
  console.log('\n=== ADMIN PAGES ===\n');
  await visit(page, `${BASE}/admin/dashboard`, 'Admin_Dashboard');
  await visit(page, `${BASE}/admin/mlm-realestate`, 'Admin_MLM');
  await visit(page, `${BASE}/admin/bookings`, 'Admin_Bookings');
  await visit(page, `${BASE}/admin/plots`, 'Admin_Plots');
  await visit(page, `${BASE}/admin/colonies`, 'Admin_Colonies');
  await visit(page, `${BASE}/admin/users`, 'Admin_Users');
  
  // Summary
  console.log('\n========================================');
  console.log('AUDIT SUMMARY');
  console.log('========================================');
  const passed = RESULTS.filter(r => r.status === 'PASS').length;
  const failed = RESULTS.filter(r => r.status === 'FAIL').length;
  console.log(`Total: ${RESULTS.length} | PASS: ${passed} | FAIL: ${failed}`);
  
  writeFileSync(`${OUT}/audit_report.txt`, RESULTS.map(r => `${r.status}: ${r.name}${r.detail ? ' | ' + r.detail : ''}`).join('\n'));
  console.log(`\nScreenshots + report saved to ${OUT}/`);
  
  await browser.close();
}

main().catch(console.error);
