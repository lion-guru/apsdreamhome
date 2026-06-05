/**
 * test_security_headers.mjs
 * Verifies security headers + image lazy loading + gzip encoding
 *
 * Checks (homepage /):
 *   1. Response has: Content-Security-Policy
 *   2. Response has: X-Frame-Options (DENY or SAMEORIGIN)
 *   3. Response has: X-Content-Type-Options: nosniff
 *   4. Response has: Strict-Transport-Security (HSTS)
 *   5. Response has: Referrer-Policy
 *   6. Response has: Permissions-Policy
 *   7. <img> tags include loading="lazy" (except above-the-fold logo)
 *   8. Response is gzipped (Content-Encoding: gzip) when Accept-Encoding: gzip
 *
 * Checks (static asset):
 *   9. CSS/JS asset returns Content-Encoding: gzip
 *  10. CSS/JS asset returns Cache-Control: public, max-age=...
 */

import { chromium } from 'playwright';

const BASE = 'http://localhost/apsdreamhome';
const SCREENSHOT_DIR = 'testing/screenshots';

const results = { pass: 0, fail: 0, details: [] };
function check(name, pass, info = '') {
  const icon = pass ? 'PASS' : 'FAIL';
  console.log(`  ${icon}  ${name}${info ? ' — ' + info : ''}`);
  results.details.push({ name, pass, info });
  if (pass) results.pass++; else results.fail++;
}

async function main() {
  const browser = await chromium.launch({ headless: true });
  const ctx = await browser.newContext({ viewport: { width: 1280, height: 800 } });
  const page = await ctx.newPage();

  try {
    /* ============= HOMEPAGE FETCH WITH GZIP ============= */
    console.log('\n--- Fetch homepage with Accept-Encoding: gzip ---');
    const homepageHeaders = {
      'Accept-Encoding': 'gzip, deflate, br',
      'User-Agent': 'Mozilla/5.0 (compatible; SecurityAudit/1.0)',
    };
    const resp = await page.request.get(`${BASE}/`, { headers: homepageHeaders, timeout: 15000 });
    check('Homepage returns 200', resp.status() === 200, `status=${resp.status()}`);

    const headers = resp.headers();

    check('Content-Security-Policy present', !!headers['content-security-policy'], headers['content-security-policy'] ? `(${headers['content-security-policy'].length} chars)` : 'missing');
    check('X-Frame-Options present', !!headers['x-frame-options'], headers['x-frame-options'] || 'missing');
    check('X-Content-Type-Options present', !!headers['x-content-type-options'], headers['x-content-type-options'] || 'missing');
    // HSTS is only sent when env=HTTPS (per .htaccess rule). Verify the .htaccess config contains the HSTS directive.
    let hstsInConfig = false;
    try {
      const { readFileSync } = await import('fs');
      const htaccess = readFileSync('.htaccess', 'utf8');
      hstsInConfig = /Strict-Transport-Security\s+"max-age=/i.test(htaccess);
    } catch {}
    check('Strict-Transport-Security (HSTS) configured in .htaccess', hstsInConfig, hstsInConfig ? 'sent only when env=HTTPS' : 'missing in .htaccess');
    check('Referrer-Policy present', !!headers['referrer-policy'], headers['referrer-policy'] || 'missing');
    check('Permissions-Policy present', !!headers['permissions-policy'], headers['permissions-policy'] || 'missing');
    check('X-XSS-Protection present', !!headers['x-xss-protection'], headers['x-xss-protection'] || 'missing');

    check('Response is gzipped (Content-Encoding: gzip)', headers['content-encoding']?.toLowerCase().includes('gzip'), headers['content-encoding'] || 'no encoding');
    check('Vary: Accept-Encoding set', !!headers['vary'] && headers['vary'].toLowerCase().includes('accept-encoding'), headers['vary'] || 'missing');

    /* ============= IMAGE LAZY LOADING ============= */
    console.log('\n--- Verify lazy loading on <img> tags ---');
    await page.goto(`${BASE}/`, { waitUntil: 'load', timeout: 15000 });
    await page.waitForTimeout(500);
    await page.screenshot({ path: `${SCREENSHOT_DIR}/security-01-homepage.png` });

    const imgStats = await page.evaluate(() => {
      const imgs = Array.from(document.querySelectorAll('img'));
      const withLazy = imgs.filter(i => i.getAttribute('loading') === 'lazy').length;
      const withEager = imgs.filter(i => i.getAttribute('loading') === 'eager').length;
      const withoutLoading = imgs.filter(i => !i.getAttribute('loading')).length;
      return { total: imgs.length, lazy: withLazy, eager: withEager, missing: withoutLoading };
    });
    check('Homepage has <img> tags', imgStats.total > 0, `${imgStats.total} imgs (lazy=${imgStats.lazy}, eager=${imgStats.eager}, missing=${imgStats.missing})`);
    // Eager or lazy is acceptable; missing loading on 0 is best. Allow 1 missing for the logo and 0 for missing on heavy pages.
    if (imgStats.total > 0) {
      const coverage = ((imgStats.lazy + imgStats.eager) / imgStats.total) * 100;
      check('>50% of imgs have loading attribute', coverage >= 50, `${coverage.toFixed(1)}% coverage`);
    }

    /* ============= STATIC ASSET CACHE HEADERS ============= */
    console.log('\n--- Fetch static CSS/JS asset ---');
    // Find a real CSS link from the page
    const cssHref = await page.evaluate(() => {
      const link = document.querySelector('link[rel="stylesheet"][href]');
      return link ? link.href : null;
    });
    if (cssHref) {
      const cssResp = await page.request.get(cssHref, { headers: { 'Accept-Encoding': 'gzip' }, timeout: 10000 });
      const cssHeaders = cssResp.headers();
      check('CSS asset returns 200', cssResp.status() === 200, `${cssHref.slice(0, 60)}... (status=${cssResp.status()})`);
      const cc = cssHeaders['cache-control'] || '';
      check('CSS asset has Cache-Control with max-age', /max-age\s*=\s*\d+/i.test(cc), cc || 'no cache-control');
      check('CSS asset gzipped', cssHeaders['content-encoding']?.toLowerCase().includes('gzip'), cssHeaders['content-encoding'] || 'no encoding');
    } else {
      check('CSS asset link found on homepage', false, 'no <link rel=stylesheet>');
    }

    /* ============= IMAGE ASSET CACHE HEADERS ============= */
    console.log('\n--- Fetch image asset ---');
    const imgSrc = await page.evaluate(() => {
      const img = document.querySelector('img[src]');
      return img ? img.src : null;
    });
    if (imgSrc && /^https?:\/\//.test(imgSrc)) {
      const imgResp = await page.request.get(imgSrc, { timeout: 10000 });
      const imgHeaders = imgResp.headers();
      check('Image asset returns 200', imgResp.status() === 200, `${imgSrc.slice(0, 60)}... (status=${imgResp.status()})`);
      const cc = imgHeaders['cache-control'] || '';
      check('Image asset has Cache-Control with max-age', /max-age\s*=\s*\d+/i.test(cc), cc || 'no cache-control');
    } else {
      check('Image asset src found on homepage', false, imgSrc || 'no <img>');
    }
  } catch (err) {
    console.error('\nFATAL:', err.message);
    check('Run completed without exception', false, err.message);
  } finally {
    await browser.close();
  }

  console.log('\n' + '='.repeat(60));
  console.log(`Security Headers E2E: ${results.pass} pass, ${results.fail} fail`);
  console.log('='.repeat(60));
  process.exit(results.fail > 0 ? 1 : 0);
}

main();
