// Performance Audit Script (with cache busting)
import { chromium } from 'playwright';

const BASE = 'http://localhost/apsdreamhome';

async function measurePage(browser, url, label) {
  const context = await browser.newContext({ viewport: { width: 1280, height: 800 } });
  const page = await context.newPage();
  
  // Block cache
  await context.route('**/*', route => {
    const headers = route.request().headers();
    headers['Cache-Control'] = 'no-cache';
    route.continue({ headers });
  });
  
  const resources = [];
  page.on('response', resp => {
    const ct = resp.headers()['content-type'] || '';
    const url = resp.url();
    const size = parseInt(resp.headers()['content-length'] || '0');
    const status = resp.status();
    resources.push({ url: url.substring(0, 80), type: ct.includes('css') ? 'CSS' : ct.includes('javascript') ? 'JS' : ct.includes('image') ? 'IMG' : ct.includes('font') ? 'FONT' : 'OTHER', size, status });
  });
  
  const t1 = Date.now();
  await page.goto(url + '?cb=' + Date.now(), { waitUntil: 'load', timeout: 20000 });
  const loadTime = Date.now() - t1;
  
  console.log(`\n--- ${label} (${loadTime}ms) ---`);
  
  const byType = {};
  resources.forEach(r => {
    byType[r.type] = (byType[r.type] || 0) + 1;
  });
  const totalSize = resources.reduce((s, r) => s + r.size, 0);
  console.log(`  Requests: ${resources.length} | Types: ${Object.entries(byType).map(([k,v]) => `${k}:${v}`).join(' ')} | ${(totalSize/1024).toFixed(1)}KB`);
  
  // Show 404s
  resources.filter(r => r.status === 404).forEach(r => console.log(`  404: ${r.url}`));
  
  await context.close();
  return { loadTime, requests: resources.length, totalSize };
}

async function main() {
  const browser = await chromium.launch({ headless: true });
  
  console.log('=== PERFORMANCE AUDIT (cache-busted) ===\n');
  const results = [];
  results.push(await measurePage(browser, BASE + '/', 'HOMEPAGE'));
  results.push(await measurePage(browser, BASE + '/properties', 'PROPERTIES'));
  results.push(await measurePage(browser, BASE + '/plots', 'PLOTS'));
  results.push(await measurePage(browser, BASE + '/plots/1', 'PLOT DETAIL'));
  results.push(await measurePage(browser, BASE + '/login', 'LOGIN'));
  results.push(await measurePage(browser, BASE + '/admin/login', 'ADMIN LOGIN'));
  
  console.log('\n=== SUMMARY ===');
  results.forEach((r, i) => {
    const labels = ['HOMEPAGE','PROPERTIES','PLOTS','PLOT DETAIL','LOGIN','ADMIN LOGIN'];
    console.log(`  ${labels[i]}: ${r.loadTime}ms, ${r.requests} req, ${(r.totalSize/1024).toFixed(1)}KB`);
  });
  
  await browser.close();
}

main().catch(console.error);
