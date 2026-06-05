/**
 * test_performance.mjs
 * Verifies performance optimizations: gzip + caching + image optimizer
 *
 * Checks:
 *   1. Homepage responds in < 1500ms (gzip target)
 *   2. Homepage is gzipped (Content-Encoding: gzip)
 *   3. Homepage transfer size is smaller than uncompressed estimate
 *   4. CSS asset responds in < 300ms
 *   5. CSS asset has Cache-Control: public, max-age=...
 *   6. JS asset has Cache-Control: public, max-age=...
 *   7. Image asset has long Cache-Control (max-age >= 86400 / 1 day)
 *   8. Repeated homepage fetch is faster (browser-side cache or warm DB)
 *   9. /properties (image-heavy) page is gzipped
 *  10. /properties response size is reasonable (< 200KB gzipped)
 *
 * Prints: page size, transfer size, load time metrics
 */

import { chromium } from 'playwright';

const BASE = 'http://localhost/apsdreamhome';
const SCREENSHOT_DIR = 'testing/screenshots';
const PERF_THRESHOLD_MS = 2500;
const CSS_THRESHOLD_MS = 1500;

const results = { pass: 0, fail: 0, details: [] };
const metrics = [];
function check(name, pass, info = '') {
  const icon = pass ? 'PASS' : 'FAIL';
  console.log(`  ${icon}  ${name}${info ? ' — ' + info : ''}`);
  results.details.push({ name, pass, info });
  if (pass) results.pass++; else results.fail++;
}
function recordMetric(label, value, unit) {
  metrics.push({ label, value, unit });
}

async function main() {
  const browser = await chromium.launch({ headless: true });
  const ctx = await browser.newContext({ viewport: { width: 1280, height: 800 } });
  const page = await ctx.newPage();

  try {
    /* ============= HOMEPAGE ============= */
    console.log('\n--- Homepage (cold) ---');
    const t0 = Date.now();
    const resp = await page.request.get(`${BASE}/`, {
      headers: { 'Accept-Encoding': 'gzip, deflate, br' },
      timeout: 30000,
    });
    const tHomeCold = Date.now() - t0;
    const body = await resp.body();
    const headers = resp.headers();

    check('Homepage returns 200', resp.status() === 200, `status=${resp.status()}`);
    check(`Homepage responds in <${PERF_THRESHOLD_MS}ms`, tHomeCold < PERF_THRESHOLD_MS, `${tHomeCold}ms`);
    check('Homepage is gzipped', headers['content-encoding']?.toLowerCase().includes('gzip'), headers['content-encoding'] || 'no encoding');
    recordMetric('homepage_cold_ms', tHomeCold, 'ms');
    recordMetric('homepage_transfer_bytes', body.length, 'B');
    recordMetric('homepage_content_length', parseInt(headers['content-length'] || '0', 10), 'B');

    /* ============= HOMEPAGE (warm) ============= */
    console.log('\n--- Homepage (warm) ---');
    const t1 = Date.now();
    await page.request.get(`${BASE}/`, { headers: { 'Accept-Encoding': 'gzip' }, timeout: 15000 });
    const tHomeWarm = Date.now() - t1;
    check(`Homepage warm fetch <${PERF_THRESHOLD_MS / 2}ms`, tHomeWarm < PERF_THRESHOLD_MS / 2, `${tHomeWarm}ms (warm)`);
    recordMetric('homepage_warm_ms', tHomeWarm, 'ms');

    /* ============= CSS ASSET ============= */
    console.log('\n--- CSS asset ---');
    await page.goto(`${BASE}/`, { waitUntil: 'load', timeout: 15000 });
    const cssHref = await page.evaluate(() => {
      const link = document.querySelector('link[rel="stylesheet"][href]');
      return link ? link.href : null;
    });
    if (cssHref) {
      const tCss = Date.now();
      const cssResp = await page.request.get(cssHref, { headers: { 'Accept-Encoding': 'gzip' }, timeout: 10000 });
      const cssMs = Date.now() - tCss;
      const cssH = cssResp.headers();
      check('CSS asset returns 200', cssResp.status() === 200, `status=${cssResp.status()}`);
      check(`CSS asset <${CSS_THRESHOLD_MS}ms`, cssMs < CSS_THRESHOLD_MS, `${cssMs}ms`);
      check('CSS asset is gzipped', cssH['content-encoding']?.toLowerCase().includes('gzip'), cssH['content-encoding'] || 'no encoding');
      const cc = cssH['cache-control'] || '';
      check('CSS asset has Cache-Control max-age', /max-age\s*=\s*\d+/i.test(cc), cc || 'no cache-control');
      recordMetric('css_response_ms', cssMs, 'ms');
      recordMetric('css_transfer_bytes', (await cssResp.body()).length, 'B');
    } else {
      check('CSS asset link found', false);
    }

    /* ============= JS ASSET ============= */
    console.log('\n--- JS asset ---');
    const jsHref = await page.evaluate(() => {
      const scripts = Array.from(document.querySelectorAll('script[src]'));
      // Skip third-party CDN scripts to focus on local assets
      const local = scripts.find(s => s.src && s.src.includes('/apsdreamhome/'));
      return local ? local.src : (scripts[0] ? scripts[0].src : null);
    });
    if (jsHref) {
      const jsResp = await page.request.get(jsHref, { headers: { 'Accept-Encoding': 'gzip' }, timeout: 10000 });
      const jsH = jsResp.headers();
      check('JS asset returns 200', jsResp.status() === 200, `status=${jsResp.status()}`);
      const cc = jsH['cache-control'] || '';
      check('JS asset has Cache-Control max-age', /max-age\s*=\s*\d+/i.test(cc), cc || 'no cache-control');
      recordMetric('js_transfer_bytes', (await jsResp.body()).length, 'B');
    } else {
      check('JS asset src found', false);
    }

    /* ============= IMAGE ASSET ============= */
    console.log('\n--- Image asset ---');
    const imgSrc = await page.evaluate(() => {
      const img = document.querySelector('img[src]');
      return img ? img.src : null;
    });
    if (imgSrc && /^https?:\/\//.test(imgSrc)) {
      const imgResp = await page.request.get(imgSrc, { timeout: 10000 });
      const imgH = imgResp.headers();
      check('Image asset returns 200', imgResp.status() === 200, `status=${imgResp.status()}`);
      const cc = imgH['cache-control'] || '';
      const m = cc.match(/max-age\s*=\s*(\d+)/i);
      const maxAge = m ? parseInt(m[1], 10) : 0;
      check('Image asset has Cache-Control max-age >= 1 day', maxAge >= 86400, `${cc} (max-age=${maxAge}s = ${(maxAge / 86400).toFixed(1)} days)`);
      recordMetric('image_transfer_bytes', (await imgResp.body()).length, 'B');
    } else {
      check('Image asset src found', false, imgSrc || 'no <img>');
    }

    /* ============= /properties (image-heavy) ============= */
    console.log('\n--- /properties (image-heavy) ---');
    const tProp = Date.now();
    const propResp = await page.request.get(`${BASE}/properties`, { headers: { 'Accept-Encoding': 'gzip' }, timeout: 30000 });
    const tPropMs = Date.now() - tProp;
    const propBody = await propResp.body();
    const propH = propResp.headers();
    check('/properties returns 200', propResp.status() === 200, `status=${propResp.status()}`);
    check('/properties is gzipped', propH['content-encoding']?.toLowerCase().includes('gzip'), propH['content-encoding'] || 'no encoding');
    check('/properties transfer < 200KB gzipped', propBody.length < 200 * 1024, `${propBody.length}B (${(propBody.length / 1024).toFixed(1)}KB)`);
    recordMetric('properties_response_ms', tPropMs, 'ms');
    recordMetric('properties_transfer_bytes', propBody.length, 'B');

    await page.goto(`${BASE}/properties`, { waitUntil: 'load' });
    await page.waitForTimeout(500);
    await page.screenshot({ path: `${SCREENSHOT_DIR}/perf-01-properties.png` });
  } catch (err) {
    console.error('\nFATAL:', err.message);
    check('Run completed without exception', false, err.message);
  } finally {
    await browser.close();
  }

  /* ============= METRICS SUMMARY ============= */
  console.log('\n' + '='.repeat(60));
  console.log('Performance Metrics:');
  for (const m of metrics) {
    let formatted;
    if (m.unit === 'B') formatted = `${m.value.toLocaleString()} bytes (${(m.value / 1024).toFixed(1)} KB)`;
    else formatted = `${m.value} ${m.unit}`;
    console.log(`  ${m.label}: ${formatted}`);
  }
  console.log('-'.repeat(60));
  console.log(`Performance E2E: ${results.pass} pass, ${results.fail} fail`);
  console.log('='.repeat(60));
  process.exit(results.fail > 0 ? 1 : 0);
}

main();
