const { chromium } = require('playwright');
const fs = require('fs');
const path = require('path');

const dir = 'C:/Users/abhay/AppData/Local/Temp/visual_final';
fs.mkdirSync(dir, { recursive: true });

const pages = [
  ['/', '01_home'],
  ['/properties', '02_properties'],
  ['/colonies', '03_colonies'],
  ['/projects', '04_projects'],
  ['/buy', '05_buy'],
  ['/sell', '06_sell'],
  ['/rent', '07_rent'],
  ['/invest', '08_invest'],
  ['/about', '09_about'],
  ['/team', '10_team'],
  ['/blog', '11_blog'],
  ['/contact', '12_contact'],
  ['/faq', '13_faq'],
  ['/careers', '14_careers'],
  ['/tools-hub', '15_tools_hub'],
  ['/calc', '16_emi_calc'],
  ['/services', '17_services'],
  ['/gallery', '18_gallery'],
  ['/news', '19_news'],
  ['/list-property', '20_list_property'],
  ['/colony/suryoday-colony', '21_colony_suryoday'],
  ['/colony/braj-radha-nagri', '22_colony_braj_radha'],
  ['/colony/raghunath-nagri-motiram', '23_colony_raghunath'],
  ['/colony/budh-bihar-colony', '24_colony_budh_bihar'],
];

(async () => {
  const b = await chromium.launch();
  const ctx = await b.newContext({ viewport: { width: 1280, height: 900 } });
  const issues = [];

  for (const [url, name] of pages) {
    const p = await ctx.newPage();
    try {
      await p.goto('http://localhost/apsdreamhome' + url, { waitUntil: 'domcontentloaded', timeout: 12000 });
      await p.waitForTimeout(2500);

      // Full page screenshot
      await p.screenshot({ path: path.join(dir, name + '.png'), fullPage: true });

      // Check for visible text-on-white contrast issues (hero sections)
      const contrastCheck = await p.evaluate(() => {
        const results = [];
        // Check hero sections
        document.querySelectorAll('.hero-section, .hero, .page-header, section').forEach(sec => {
          const style = getComputedStyle(sec);
          const bg = style.backgroundColor;
          const h = sec.querySelector('h1, h2, h3');
          if (h && sec.offsetHeight > 100) {
            const hStyle = getComputedStyle(h);
            // White text on white/light bg = bad contrast
            if (hStyle.color === 'rgb(255, 255, 255)' && bg === 'rgba(0, 0, 0, 0)') {
              // transparent bg + white text = potential issue
            }
          }
        });
        return results;
      });

      // Check for zero-height sections
      const sections = await p.evaluate(() => {
        const sects = document.querySelectorAll('section');
        return Array.from(sects).map(s => ({
          h: s.offsetHeight,
          cls: (s.className || '').slice(0, 40),
          text: s.textContent.trim().slice(0, 50)
        })).filter(s => s.h < 50 && s.text.length > 5);
      });

      if (sections.length > 0) {
        issues.push({ url: name, type: 'empty-sections', data: sections });
      }

      console.log('OK ' + name);
    } catch (e) {
      console.log('ERR ' + name + ': ' + e.message.slice(0, 80));
      issues.push({ url: name, type: 'error', data: e.message.slice(0, 80) });
    }
    await p.close();
  }

  await ctx.close();
  await b.close();

  if (issues.length) {
    console.log('\n' + issues.length + ' ISSUES FOUND:');
    issues.forEach(i => console.log('  ' + i.url + ' [' + i.type + ']: ' + JSON.stringify(i.data).slice(0, 120)));
  } else {
    console.log('\nALL CLEAN');
  }
})();
