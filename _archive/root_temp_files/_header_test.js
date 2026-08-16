const puppeteer = require('puppeteer-core');

(async () => {
  const browser = await puppeteer.launch({
    headless: 'new',
    executablePath: 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe',
    args: ['--no-sandbox']
  });

  const sizes = [
    { w: 1920, h: 1080, name: '1920_fullhd' },
    { w: 1440, h: 900, name: '1440_laptop' },
    { w: 1280, h: 800, name: '1280_small_laptop' },
    { w: 1024, h: 768, name: '1024_tablet_landscape' },
    { w: 768, h: 1024, name: '768_tablet_portrait' },
    { w: 375, h: 812, name: '375_mobile' },
  ];

  for (const s of sizes) {
    const page = await browser.newPage();
    await page.setViewport({ width: s.w, height: s.h });
    console.log(`${s.name} (${s.w}x${s.h})...`);
    await page.goto('http://localhost/apsdreamhome/', { waitUntil: 'networkidle2', timeout: 20000 });
    await new Promise(r => setTimeout(r, 500));
    await page.screenshot({ path: `_header_${s.name}.png` });
    await page.close();
  }

  await browser.close();
  console.log('Done!');
})().catch(e => { console.error('ERROR:', e.message); process.exit(1); });
