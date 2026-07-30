// Performance Audit - fresh browser per page
import { chromium } from 'playwright';
import { writeFileSync } from 'fs';

const BASE = 'http://localhost/apsdreamhome';

async function measurePage(label) {
  const browser = await chromium.launch({ headless: true });
  const page = await browser.newPage({ viewport: { width: 1280, height: 800 } });
  
  const resources = [];
  page.on('response', resp => {
    const ct = resp.headers()['content-type'] || '';
    const size = parseInt(resp.headers()['content-length'] || '0');
    resources.push({
      url: resp.url().replace(BASE, '').split('?')[0],
      type: ct.includes('css') ? 'CSS' : ct.includes('javascript') ? 'JS' : ct.includes('image') ? 'IMG' : ct.includes('font') ? 'FONT' : 'OTHER',
      size,
      status: resp.status()
    });
  });
  
  const t1 = Date.now();
  await page.goto(BASE + '/', { waitUntil: 'load', timeout: 20000 });
  const loadTime = Date.now() - t1;
  
  const byType = {};
  let totalSize = 0;
  resources.forEach(r => {
    byType[r.type] = (byType[r.type] || 0) + 1;
    totalSize += r.size;
  });
  
  console.log(`${label}: ${loadTime}ms, ${resources.length} req (${Object.entries(byType).map(([k,v]) => `${k}:${v}`).join(', ')}), ${(totalSize/1024).toFixed(1)}KB`);
  
  // List 404s
  resources.filter(r => r.status === 404).forEach(r => console.log(`  404: ${r.url}`));
  
  await browser.close();
}

async function main() {
  console.log('=== PAGE LOAD PERFORMANCE ===\n');
  await measurePage('HOMEPAGE');
  await measurePage('PROPERTIES');
  await measurePage('PLOTS');
  await measurePage('LOGIN');
  await measurePage('ADMIN LOGIN');
  console.log('\nDone.');
}

main().catch(console.error);
