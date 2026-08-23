import { chromium } from 'playwright';

const BASE = 'http://localhost/apsdreamhome';
const PAGES = [
  { path: '/', name: 'Homepage' },
  { path: '/properties', name: 'Properties Listing' },
  { path: '/colonies', name: 'Colonies' },
  { path: '/plots', name: 'Plots' },
  { path: '/colony/suryoday-colony', name: 'Suryoday Colony Detail' },
  { path: '/colony/braj-radha-nagri', name: 'Braj Radha Colony' },
  { path: '/projects', name: 'Projects' },
  { path: '/services', name: 'Services' },
  { path: '/about', name: 'About Us' },
  { path: '/team', name: 'Team' },
  { path: '/blog', name: 'Blog' },
  { path: '/news', name: 'News' },
  { path: '/contact', name: 'Contact' },
  { path: '/careers', name: 'Careers' },
  { path: '/careers/apply', name: 'Careers Apply' },
  { path: '/faq', name: 'FAQ' },
  { path: '/calc', name: 'EMI Calculator' },
  { path: '/compare', name: 'Property Compare' },
  { path: '/testimonials', name: 'Testimonials' },
  { path: '/auth/login', name: 'Login' },
  { path: '/register', name: 'Register' },
  { path: '/list-property', name: 'Post Property' },
];

const browser = await chromium.launch({ headless: true });
const ctx = await browser.newContext({ viewport: { width: 1280, height: 900 } });
const page = await ctx.newPage();

console.log('=== PUBLIC FRONTEND UI/UX DEEP AUDIT ===\n');

const allIssues = [];

for (const { path, name } of PAGES) {
  const url = BASE + path;
  try {
    const resp = await page.goto(url, { waitUntil: 'domcontentloaded', timeout: 12000 });
    await page.waitForTimeout(1500);
    const status = resp?.status() || 0;

    // Scroll to load lazy images
    await page.evaluate(() => window.scrollTo(0, document.body.scrollHeight / 2));
    await page.waitForTimeout(600);
    await page.evaluate(() => window.scrollTo(0, 0));
    await page.waitForTimeout(400);

    const audit = await page.evaluate(() => {
      const issues = [];

      // 1. Translation leaks (visible only)
      const walker = document.createTreeWalker(document.body, NodeFilter.SHOW_TEXT);
      let n;
      while (n = walker.nextNode()) {
        if (n.nodeValue?.includes('__(') && n.parentElement?.offsetParent) {
          issues.push(`TRANSLATION_LEAK: ${n.nodeValue.trim().slice(0, 50)}`);
          break;
        }
      }

      // 2. Horizontal overflow
      const over = document.documentElement.scrollWidth - document.documentElement.clientWidth;
      if (over > 5) issues.push(`H_OVERFLOW: ${over}px`);

      // 3. Broken images (only in-viewport + completed but failed)
      const broken = [...document.images].filter(img => {
        const r = img.getBoundingClientRect();
        const inViewport = r.top < window.innerHeight + 200 && r.bottom > -200;
        return inViewport && img.complete && img.naturalWidth === 0;
      }).slice(0, 3).map(i => i.src.split('/').pop() || i.alt?.slice(0, 20));
      if (broken.length) issues.push(`BROKEN_IMG: ${broken.join(', ')}`);

      // 4. Empty clickable - must have no text AND no icon AND no aria-label/title
      const empties = [...document.querySelectorAll('a, button')].filter(el => {
        const r = el.getBoundingClientRect();
        return r.width > 0 && r.height > 0 && !el.textContent.trim() && !el.querySelector('img, svg, i') && !el.getAttribute('aria-label') && !el.getAttribute('title');
      });
      if (empties.length > 0) issues.push(`EMPTY_CLICKABLE: ${empties.length} items`);

      // 5. Missing alt on large visible images
      const noAlt = [...document.images].filter(img => {
        const r = img.getBoundingClientRect();
        return r.width > 30 && r.height > 30 && !img.alt;
      }).length;
      if (noAlt > 2) issues.push(`MISSING_ALT: ${noAlt} large images`);

      // 6. Bad title
      if (!document.title || document.title.includes('404') || document.title.includes('Error')) {
        issues.push(`BAD_TITLE: ${document.title.slice(0, 40)}`);
      }

      // 7. PHP errors in DOM
      const html = document.documentElement.outerHTML;
      if (html.includes('Undefined variable') || html.includes('Fatal error') || html.includes('Warning:')) {
        const m = html.match(/(Undefined variable[^<]{0,40}|Fatal error[^<]{0,40}|Warning:[^<]{0,40})/);
        if (m) issues.push(`PHP_ERROR: ${m[0].slice(0, 50)}`);
      }

      // 8. Multiple H1
      const h1s = document.querySelectorAll('h1').length;
      if (h1s > 1) issues.push(`MULTI_H1: ${h1s}`);

      // 9. Footer check (skip standalone auth pages)
      const isStandalone = document.querySelector('.login-wrapper, .register-wrapper, .auth-wrapper, [class*="standalone"]');
      const isAuthPage = window.location.pathname.includes('/login') || window.location.pathname.includes('/register');
      const footer = document.querySelector('footer');
      if (!footer && !isStandalone && !isAuthPage) issues.push('NO_FOOTER');

      return issues;
    });

    const tag = audit.length === 0 ? '✅ CLEAN' : '❌ ' + audit.join(' | ');
    const sTag = status === 200 ? '200' : `${status}`;
    console.log(`${tag} [${sTag}] ${name} (${path})`);
    if (audit.length > 0) {
      allIssues.push({ path, name, issues: audit, status });
    }

    // Mobile overflow check for key pages
    if (['/', '/properties', '/contact', '/auth/login'].includes(path)) {
      await page.setViewportSize({ width: 375, height: 812 });
      await page.waitForTimeout(800);
      const mobileOver = await page.evaluate(() => document.documentElement.scrollWidth - document.documentElement.clientWidth);
      if (mobileOver > 5) {
        console.log(`   📱 MOBILE OVERFLOW: ${mobileOver}px on ${path}`);
        allIssues.push({ path: path + ' [MOBILE]', name: name + ' Mobile', issues: [`MOBILE_OVERFLOW: ${mobileOver}px`], status });
      }
      await page.setViewportSize({ width: 1280, height: 900 });
      await page.waitForTimeout(500);
    }

  } catch (e) {
    console.log(`❌ ERROR [ERR] ${name} (${path}): ${e.message.slice(0, 60)}`);
    allIssues.push({ path, name, issues: ['LOAD_ERROR: ' + e.message.slice(0, 40)], status: 0 });
  }
}

console.log(`\n=== SUMMARY ===`);
console.log(`Total pages: ${PAGES.length}`);
console.log(`Clean: ${PAGES.length - allIssues.filter(a => !a.path.includes('[MOBILE]')).length}/${PAGES.length}`);
if (allIssues.length) {
  console.log(`Issues found: ${allIssues.length}`);
  allIssues.forEach(a => console.log(`  ${a.path}: ${a.issues.join(' | ')}`));
} else {
  console.log('No issues found — all pages pixel-perfect!');
}

await browser.close();
