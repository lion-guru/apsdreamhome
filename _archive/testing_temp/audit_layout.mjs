// Comprehensive Layout & UI Audit
import { chromium } from 'playwright';

const BASE = 'http://localhost/apsdreamhome';

async function measureLayout(page, label) {
  await page.waitForTimeout(1000);
  
  const metrics = await page.evaluate(() => {
    const vw = window.innerWidth;
    const vh = window.innerHeight;
    const issues = [];
    
    // 1. Check all visible elements for overflow
    document.querySelectorAll('*').forEach(el => {
      const r = el.getBoundingClientRect();
      if (r.width === 0 || r.height === 0) return;
      const tag = el.tagName.toLowerCase();
      if (['html','body','script','style','link','meta','head'].includes(tag)) return;
      
      // Offscreen to the right (meaningful overflow)
      if (r.left > vw - 20 && r.left < vw + 200 && r.width > 30) {
        const text = (el.textContent || '').trim().substring(0, 40);
        if (text) issues.push({ type: 'overflow-right', tag, text, left: Math.round(r.left), width: Math.round(r.width), id: el.id, classes: el.className });
      }
    });
    
    // 2. Check navbar for truncation
    const nav = document.querySelector('.navbar-nav');
    const navIssues = [];
    if (nav) {
      const navRect = nav.getBoundingClientRect();
      const container = nav.closest('.container') || nav.closest('.container-fluid');
      if (container) {
        const cRect = container.getBoundingClientRect();
        if (navRect.right > cRect.right + 5) {
          navIssues.push(`navbar overflows container by ${Math.round(navRect.right - cRect.right)}px`);
        }
      }
      // Check each nav item
      document.querySelectorAll('.navbar-nav > .nav-item, .navbar-nav > li').forEach(li => {
        const r = li.getBoundingClientRect();
        const text = (li.textContent || '').trim().substring(0, 30);
        if (r.right > vw) {
          navIssues.push(`nav item offscreen: "${text}" at x=${Math.round(r.left)}, right=${Math.round(r.right)} vs vw=${vw}`);
        }
      });
    }
    
    // 3. Check font loading
    const fonts = document.fonts ? Array.from(document.fonts).map(f => f.family) : [];
    
    // 4. Color analysis - check primary colors used
    const styles = getComputedStyle(document.body);
    
    return { issues, navIssues, fonts, bodyFont: styles.fontFamily, bodyColor: styles.color, vw, vh };
  });
  
  console.log(`\n=== ${label} ===`);
  console.log(`Viewport: ${metrics.vw}x${metrics.vh}`);
  
  if (metrics.navIssues.length > 0) {
    console.log('NAV ISSUES:');
    metrics.navIssues.forEach(i => console.log(`  ${i}`));
  } else {
    console.log('NAV: OK');
  }
  
  if (metrics.issues.length > 0) {
    console.log('OFFSCREEN ELEMENTS:');
    metrics.issues.slice(0, 10).forEach(i => console.log(`  "${i.text}" at x=${i.left} w=${i.width} (${i.tag})`));
  } else {
    console.log('OFFSCREEN: OK');
  }
  
  console.log(`Fonts: ${metrics.fonts.slice(0, 5).join(', ') || 'none'}`);
  console.log(`Body font: ${metrics.bodyFont.substring(0, 80)}`);
}

async function main() {
  const browser = await chromium.launch({ headless: true });
  const page = await browser.newPage({ viewport: { width: 1280, height: 800 } });
  
  await page.goto(`${BASE}/`, { waitUntil: 'load', timeout: 20000 });
  await measureLayout(page, 'HOMEPAGE');
  
  await page.goto(`${BASE}/admin/login?test_login=1`, { waitUntil: 'load' });
  await page.waitForTimeout(1000);
  await page.goto(`${BASE}/admin/dashboard`, { waitUntil: 'load' });
  await measureLayout(page, 'ADMIN DASHBOARD');
  
  // Mobile viewport
  await page.setViewportSize({ width: 412, height: 915 });
  await page.goto(`${BASE}/login`, { waitUntil: 'load' });
  await measureLayout(page, 'LOGIN (mobile 412px)');
  
  // Login and check user dashboard
  await page.setViewportSize({ width: 1280, height: 800 });
  await page.goto(`${BASE}/login`, { waitUntil: 'load' });
  await page.fill('input[name="identity"]', 'testuser@example.com');
  await page.fill('input[name="password"]', 'Test@123');
  await Promise.all([
    page.waitForNavigation({ waitUntil: 'load', timeout: 15000 }),
    page.click('button[type="submit"]')
  ]);
  await measureLayout(page, 'CUSTOMER DASHBOARD');
  
  await browser.close();
}

main().catch(console.error);
