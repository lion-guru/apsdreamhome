const { chromium } = require('playwright');
(async () => {
  const browser = await chromium.launch({ headless: true });
  const page = await browser.newPage();
  
  // Login as admin
  await page.goto('http://localhost/apsdreamhome/admin/login', { waitUntil: 'load', timeout: 15000 });
  await page.goto('http://localhost/apsdreamhome/admin/login?test_login=1', { waitUntil: 'load', timeout: 15000 });
  
  // Extract all sidebar links
  const sidebarLinks = await page.evaluate(() => {
    const links = document.querySelectorAll('.sidebar a, .nav-sidebar a, #sidebar a, nav a, .menu a, [class*="sidebar"] a, [class*="menu"] a, aside a');
    const result = [];
    const seen = new Set();
    links.forEach(a => {
      const href = a.getAttribute('href');
      const text = a.textContent.trim().replace(/\s+/g, ' ');
      if (href && href.startsWith('/') && !seen.has(href) && text.length > 0 && !href.includes('logout') && !href.includes('#')) {
        seen.add(href);
        // Get parent structure for context
        let parent = a.closest('li')?.parentElement?.closest('li');
        let parentText = parent ? parent.querySelector('a')?.textContent?.trim()?.replace(/\s+/g, ' ') : '';
        result.push({ href, text, parent: parentText });
      }
    });
    return result;
  });
  
  console.log('=== ALL SIDEBAR LINKS (' + sidebarLinks.length + ') ===');
  sidebarLinks.forEach(l => {
    console.log((l.parent ? '[' + l.parent + '] ' : '') + l.text + ' => ' + l.href);
  });
  
  // Now test each one
  console.log('\n=== TESTING EACH SIDEBAR LINK ===');
  const results = [];
  for (const link of sidebarLinks) {
    try {
      const resp = await page.goto('http://localhost/apsdreamhome' + link.href, { waitUntil: 'load', timeout: 15000 });
      const status = resp.status();
      const title = await page.title();
      const bodyLen = await page.evaluate(() => document.body.innerText.length);
      const has500 = bodyLen < 100 || (await page.evaluate(() => document.body.innerText.includes('500') || document.body.innerText.includes('Internal Server Error')));
      results.push({ href: link.href, text: link.text, status, title: title.substring(0, 60), bodyLen, has500 });
    } catch(e) {
      results.push({ href: link.href, text: link.text, status: 0, title: 'ERROR', bodyLen: 0, has500: true });
    }
  }
  
  // Summary
  let ok = 0, fail = 0, byStatus = {};
  results.forEach(r => {
    const key = r.status || 'ERROR';
    byStatus[key] = (byStatus[key] || 0) + 1;
    if (r.status === 200 || r.status === 302) ok++; else fail++;
  });
  
  console.log('\n=== RESULTS ===');
  console.log('Total: ' + results.length + ', OK (200/302): ' + ok + ', FAIL: ' + fail);
  Object.keys(byStatus).sort().forEach(k => console.log('  HTTP ' + k + ': ' + byStatus[k]));
  
  console.log('\n=== FAILURES ===');
  results.filter(r => r.status !== 200 && r.status !== 302).forEach(r => {
    console.log('  [' + r.status + '] ' + r.href + ' (' + r.text + ')');
  });
  
  console.log('\n=== SUSPICIOUS (small body, possible 500) ===');
  results.filter(r => r.bodyLen < 200 && r.status === 200).forEach(r => {
    console.log('  body=' + r.bodyLen + ' [' + r.status + '] ' + r.href + ' (' + r.text + ')');
  });
  
  await browser.close();
})();
