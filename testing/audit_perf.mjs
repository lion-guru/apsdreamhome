// Performance Audit Script
import { chromium } from 'playwright';

const BASE = 'http://localhost/apsdreamhome';

const RESULTS = [];

function check(name, pass, detail = '') {
  RESULTS.push({ name, pass, detail });
  console.log(`${pass ? '  ' : '  '} ${pass ? 'PASS' : 'FAIL'}: ${name}${detail ? ' - ' + detail : ''}`);
}

async function measurePage(page, url, label) {
  // Clear cache by using new context
  const start = Date.now();
  const perfEntries = [];
  
  page.on('response', resp => {
    const contentType = resp.headers()['content-type'] || '';
    const url = resp.url();
    if (contentType.includes('text/css')) perfEntries.push({ type: 'css', url, size: parseInt(resp.headers()['content-length'] || '0') });
    else if (contentType.includes('javascript')) perfEntries.push({ type: 'js', url, size: parseInt(resp.headers()['content-length'] || '0') });
    else if (contentType.includes('image')) perfEntries.push({ type: 'img', url, size: parseInt(resp.headers()['content-length'] || '0') });
    else if (contentType.includes('font')) perfEntries.push({ type: 'font', url, size: parseInt(resp.headers()['content-length'] || '0') });
  });
  
  await page.goto(url, { waitUntil: 'load', timeout: 20000 });
  
  const metrics = await page.evaluate(() => ({
    domContentLoaded: performance.timing.domContentLoadedEventEnd - performance.timing.navigationStart,
    load: performance.timing.loadEventEnd - performance.timing.navigationStart,
    requests: performance.getEntriesByType('resource').length,
    totalSize: performance.getEntriesByType('resource').reduce((sum, e) => sum + (e.transferSize || 0), 0)
  }));
  
  const cssCount = perfEntries.filter(e => e.type === 'css').length;
  const jsCount = perfEntries.filter(e => e.type === 'js').length;
  const imgCount = perfEntries.filter(e => e.type === 'img').length;
  const fontCount = perfEntries.filter(e => e.type === 'font').length;
  
  console.log(`\n--- ${label} ---`);
  console.log(`  DOMContentLoaded: ${metrics.domContentLoaded}ms`);
  console.log(`  Full Load: ${metrics.load}ms`);
  console.log(`  Requests: ${metrics.requests} (CSS:${cssCount} JS:${jsCount} IMG:${imgCount} Font:${fontCount})`);
  console.log(`  Total transfer: ${(metrics.totalSize / 1024).toFixed(1)}KB`);
  
  // Check for render-blocking CSS
  const cssFiles = perfEntries.filter(e => e.type === 'css');
  if (cssFiles.length > 3) {
    check(`${label} CSS files`, false, `${cssFiles.length} CSS files (should combine/minify)`);
  } else {
    check(`${label} CSS files`, true);
  }
  
  return metrics;
}

async function main() {
  const browser = await chromium.launch({ headless: true });
  const context = await browser.newContext({ viewport: { width: 1280, height: 800 } });
  const page = await context.newPage();

  await measurePage(page, BASE + '/', 'HOMEPAGE');
  
  // Clear context for fresh measurements
  await context.close();
  const context2 = await browser.newContext({ viewport: { width: 1280, height: 800 } });
  const page2 = await context2.newPage();
  await measurePage(page2, BASE + '/admin/login', 'ADMIN LOGIN');
  
  await context2.close();
  const context3 = await browser.newContext({ viewport: { width: 412, height: 915 } });
  const page3 = await context3.newPage();
  await measurePage(page3, BASE + '/login', 'LOGIN (mobile)');
  
  await context3.close();
  await browser.close();
  
  // Summary
  console.log('\n========================================');
  const passed = RESULTS.filter(r => r.pass).length;
  const failed = RESULTS.filter(r => !r.pass).length;
  console.log(`PERFORMANCE: ${passed} pass, ${failed} fail (${RESULTS.length} total)`);
}

main().catch(console.error);
