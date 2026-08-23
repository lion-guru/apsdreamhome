import { chromium } from 'playwright';
import fs from 'fs';

const BASE = 'http://localhost/apsdreamhome';
const PASSWORD = 'Aps@2026';
const out = { generated: new Date().toISOString(), pages: {}, summary: {} };

// Read captcha from PHP session file (dev machine)
function captchaFromCookie(cookie) {
  const sid = cookie.value;
  const f = `C:/xampp/tmp/sess_${sid}`;
  if (!fs.existsSync(f)) return null;
  const m = fs.readFileSync(f, 'utf8').match(/captcha_code\|s:\d+:"([^"]+)"/);
  return m ? m[1] : null;
}

async function loginCustomer(ctx, page) {
  await page.goto(BASE + '/auth/login', { waitUntil: 'domcontentloaded' });
  const code = captchaFromCookie((await ctx.cookies()).find(c => c.name === 'PHPSESSID'));
  await page.fill('input[name="identity"]', 'testuser@example.com');
  await page.fill('input[name="password"]', PASSWORD);
  await page.fill('input[name="captcha_code"]', code);
  await Promise.all([
    page.waitForNavigation({ waitUntil: 'domcontentloaded' }).catch(() => {}),
    page.click('form button[type="submit"]'),
  ]);
}

async function loginAdmin(ctx, page) {
  await page.goto(BASE + '/admin/login?test_login=1', { waitUntil: 'domcontentloaded' });
  await page.waitForTimeout(1500);
}

async function loginAssociate(ctx, page) {
  await page.goto(BASE + '/associate/login', { waitUntil: 'domcontentloaded' });
  const code = captchaFromCookie((await ctx.cookies()).find(c => c.name === 'PHPSESSID'));
  await page.fill('input[name="email"]', 'testassociate@example.com');
  await page.fill('input[name="password"]', PASSWORD);
  await page.fill('input[name="captcha_code"]', code);
  await Promise.all([
    page.waitForNavigation({ waitUntil: 'domcontentloaded' }).catch(() => {}),
    page.click('#submitBtn'),
  ]);
}

async function loginAgent(ctx, page) {
  await page.goto(BASE + '/agent/login', { waitUntil: 'domcontentloaded' });
  await page.fill('#email', 'agent@apsdreamhome.com');
  await page.fill('#password', PASSWORD);
  await Promise.all([
    page.waitForNavigation({ waitUntil: 'domcontentloaded' }).catch(() => {}),
    page.click('button[type="submit"]'),
  ]);
}

// ===== In-page audit =====
function auditJS() {
  const issues = [];
  const vw = document.documentElement.clientWidth;

  // 1. horizontal overflow
  if (document.documentElement.scrollWidth > vw + 2) {
    // find offending wide elements
    let worst = null;
    document.querySelectorAll('*').forEach(el => {
      const r = el.getBoundingClientRect();
      if (r.right > vw + 8 && r.width > 40) {
        if (!worst || r.right > worst.right) {
          worst = { right: Math.round(r.right), tag: el.tagName.toLowerCase(), cls: String(el.className || '').slice(0, 60), id: el.id || '' };
        }
      }
    });
    issues.push({ type: 'h-overflow', detail: `scrollWidth ${document.documentElement.scrollWidth} > vw ${vw}`, worst });
  }

  // 2. low contrast text (sample up to 400 visible text nodes)
  function lum(r, g, b) {
    const a = [r, g, b].map(v => { v /= 255; return v <= 0.03928 ? v / 12.92 : Math.pow((v + 0.055) / 1.055, 2.4); });
    return 0.2126 * a[0] + 0.7152 * a[1] + 0.0722 * a[2];
  }
  function parseC(c) {
    const m = c.match(/rgba?\((\d+),\s*(\d+),\s*(\d+)(?:,\s*([\d.]+))?\)/);
    if (!m) return null;
    if (m[4] !== undefined && parseFloat(m[4]) < 0.6) return null;
    return [parseInt(m[1]), parseInt(m[2]), parseInt(m[3])];
  }
  function effBg(el) {
    let node = el;
    while (node && node !== document.documentElement) {
      const cs2 = getComputedStyle(node);
      // element has its own image/gradient background -> can't compute contrast reliably
      if (cs2.backgroundImage && cs2.backgroundImage !== 'none') return 'IMAGE';
      // pseudo-element overlays (::before/::after) paint backgrounds the DOM walk can't see
      for (const pe of ['::before', '::after']) {
        try {
          const pcs = getComputedStyle(node, pe);
          if (pcs && pcs.content !== 'none' && pcs.backgroundImage && pcs.backgroundImage !== 'none') return 'IMAGE';
        } catch (e) { /* ignore */ }
      }
      const c = parseC(cs2.backgroundColor);
      if (c) return c;
      node = node.parentElement;
    }
    return [255, 255, 255];
  }
  const seen = new Set();
  let sampled = 0;
  const walker = document.createTreeWalker(document.body, NodeFilter.SHOW_TEXT);
  while (walker.nextNode() && sampled < 400) {
    const t = walker.currentNode.textContent.trim();
    if (!t || t.length < 3) continue;
    const el = walker.currentNode.parentElement;
    if (!el || seen.has(el)) continue;
    seen.add(el);
    const r = el.getBoundingClientRect();
    if (r.width === 0 || r.height === 0 || r.bottom < 0 || r.top > innerHeight) continue;
    const cs = getComputedStyle(el);
    if (cs.visibility === 'hidden' || cs.display === 'none' || parseFloat(cs.opacity) < 0.5) continue;
    sampled++;
    const fg = parseC(cs.color);
    if (!fg) continue;
    // skip gradient-clipped text (background-clip:text) - fg color irrelevant
    if (cs.webkitBackgroundClip === 'text' || cs.backgroundClip === 'text') continue;
    const bg = effBg(el);
    if (bg === 'IMAGE') continue;
    const L1 = lum(...fg), L2 = lum(...bg);
    const ratio = (Math.max(L1, L2) + 0.05) / (Math.min(L1, L2) + 0.05);
    // ratio ~1 means text color == computed bg — happens when the real painted
    // background (fixed overlay / canvas / image sibling) isn't in the ancestor
    // chain. Unreliable measurement, skip rather than false-positive.
    if (ratio <= 1.05) continue;
    const size = parseFloat(cs.fontSize);
    const bold = parseInt(cs.fontWeight) >= 600;
    const largeText = size >= 24 || (size >= 18.66 && bold);
    const minRatio = largeText ? 3.0 : 4.5;
    if (ratio < minRatio - 0.35) { // tolerance to cut noise
      issues.push({ type: 'low-contrast', ratio: Math.round(ratio * 10) / 10, need: minRatio, size: Math.round(size), text: t.slice(0, 50), tag: el.tagName.toLowerCase(), cls: String(el.className || '').slice(0, 50) });
    }
  }

  // 3. small tap targets (<36px both dims) on primary interactive elems
  document.querySelectorAll('a, button, [role="button"], input[type="submit"]').forEach(el => {
    const r = el.getBoundingClientRect();
    if (r.width === 0 || r.height === 0) return;
    const label = (el.innerText || el.value || '').trim();
    if (!label || label.length > 30) return;
    if (r.height < 30 && r.width < 90) {
      issues.push({ type: 'small-tap', h: Math.round(r.height), w: Math.round(r.width), text: label.slice(0, 30), tag: el.tagName.toLowerCase() });
    }
  });

  // 4. tiny fonts below 11px
  document.querySelectorAll('*').forEach(el => {
    if (el.children.length) return;
    const t = (el.innerText || '').trim();
    if (!t) return;
    const fs2 = parseFloat(getComputedStyle(el).fontSize);
    if (fs2 < 11) issues.push({ type: 'tiny-font', px: Math.round(fs2 * 10) / 10, text: t.slice(0, 40) });
  });

  // 5. images missing alt
  document.querySelectorAll('img:not([alt])').forEach(img => {
    if (img.getBoundingClientRect().width > 0) issues.push({ type: 'img-no-alt', src: (img.getAttribute('src') || '').slice(0, 80) });
  });

  // 6. icon-only buttons/links with no accessible name
  document.querySelectorAll('a, button').forEach(el => {
    const r = el.getBoundingClientRect();
    if (r.width === 0) return;
    const name = (el.getAttribute('aria-label') || el.getAttribute('title') || (el.innerText || '').trim());
    const hasImgWithAlt = el.querySelector('img[alt]:not([alt=""])');
    const svgOnly = el.querySelector('svg, i') && !name && !hasImgWithAlt;
    if (svgOnly) issues.push({ type: 'icon-no-name', tag: el.tagName.toLowerCase(), cls: String(el.className || '').slice(0, 60), href: el.getAttribute('href') || '' });
  });

  // dedupe by type+detail-ish key
  const seenK = new Set();
  return issues.filter(i => {
    const k = i.type + '|' + (i.text || i.detail || i.src || i.cls || '') + '|' + (i.ratio || '');
    if (seenK.has(k)) return false;
    seenK.add(k);
    return true;
  }).slice(0, 25);
}

async function auditPage(browser, url, opts = {}) {
  const ctx = await browser.newContext(opts.viewport ? { viewport: opts.viewport } : {});
  const p = await ctx.newPage();
  try {
    if (opts.login) await opts.login(ctx, p);
    await p.goto(BASE + url, { waitUntil: 'domcontentloaded', timeout: 45000 });
    await p.waitForTimeout(opts.wait ?? 1800);
    const res = await p.evaluate(auditJS);
    return { url, ok: true, issues: res };
  } catch (e) {
    return { url, ok: false, error: String(e).slice(0, 120) };
  } finally {
    await ctx.close();
  }
}

(async () => {
  const browser = await chromium.launch();

  const targets = [
    // public desktop
    ...['/', '/properties', '/plots', '/projects', '/services', '/about', '/team', '/blog', '/contact', '/careers', '/faq', '/calc', '/compare', '/testimonials', '/tools-hub']
      .map(u => ({ u, vp: { width: 1440, height: 900 } })),
    // public mobile (key pages)
    ...['/', '/properties', '/tools-hub', '/contact'].map(u => ({ u, vp: { width: 390, height: 844 }, mobile: true })),
    // dashboards desktop
    { u: '/admin/dashboard', login: loginAdmin },
    { u: '/admin/erp', login: loginAdmin },
    { u: '/admin/users', login: loginAdmin },
    { u: '/admin/mlm', login: loginAdmin },
    { u: '/user/dashboard', login: loginCustomer },
    { u: '/user/properties', login: loginCustomer },
    { u: '/associate/dashboard', login: loginAssociate },
    { u: '/agent/dashboard', login: loginAgent },
    // dashboards mobile
    { u: '/', vp: null, login: loginCustomer, mobile: true },
    { u: '/user/dashboard', login: loginCustomer, mobile: true },
    { u: '/associate/dashboard', login: loginAssociate, mobile: true },
  ];

  for (const t of targets) {
    const key = (t.mobile ? '[M] ' : '') + t.u;
    const r = await auditPage(browser, t.u, {
      viewport: t.vp || (t.mobile ? { width: 390, height: 844 } : undefined),
      login: t.login,
      wait: t.login ? 2200 : undefined,
    });
    out.pages[key] = r;
    const cnt = r.ok ? r.issues.length : 'ERR';
    console.log(`${key}: ${cnt}${typeof cnt === 'number' && cnt ? ' issues' : ''}`);
    if (r.ok && r.issues.length) {
      r.issues.slice(0, 8).forEach(i => console.log(`   - ${i.type}: ${JSON.stringify(i).slice(0, 140)}`));
    }
  }

  await browser.close();

  // summary
  const totals = {};
  Object.values(out.pages).forEach(pg => pg.ok && pg.issues.forEach(i => { totals[i.type] = (totals[i.type] || 0) + 1; }));
  out.summary = totals;
  fs.writeFileSync('testing/nightly_vision/uiux_issues.json', JSON.stringify(out, null, 1));
  console.log('\n=== TOTALS ===\n' + JSON.stringify(totals, null, 1));
})();
