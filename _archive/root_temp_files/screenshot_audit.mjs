import puppeteer from 'puppeteer';
import { mkdirSync, existsSync } from 'fs';

const BASE = 'http://localhost/apsdreamhome';
const WIDTH = 1440;
const HEIGHT = 900;
const OUT = 'C:/Users/abhay/AppData/Local/Temp/aps_screenshots';

if (!existsSync(OUT)) mkdirSync(OUT, { recursive: true });

const pages = [
  { name: '01-home', path: '/' },
  { name: '02-properties', path: '/properties' },
  { name: '03-plots-browse', path: '/plots/browse' },
  { name: '04-projects', path: '/projects' },
  { name: '05-about', path: '/about' },
  { name: '06-team', path: '/team' },
  { name: '07-contact', path: '/contact' },
  { name: '08-services', path: '/services' },
  { name: '09-tools-hub', path: '/tools-hub' },
  { name: '10-blog', path: '/blog' },
  { name: '11-careers', path: '/careers' },
  { name: '12-faqs', path: '/faqs' },
  { name: '13-gallery', path: '/gallery' },
  { name: '14-testimonials', path: '/testimonials' },
  { name: '15-news', path: '/news' },
  { name: '16-login', path: '/login' },
  { name: '17-register', path: '/register' },
  { name: '18-colony-suryoday', path: '/colony/suryoday-colony' },
  { name: '19-how-it-works', path: '/how-it-works' },
  { name: '20-privacy', path: '/privacy' },
];

async function run() {
  const browser = await puppeteer.launch({
    headless: 'new',
    executablePath: 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe',
    args: ['--no-sandbox', '--disable-setuid-sandbox'],
  });

  const page = await browser.newPage();
  await page.setViewport({ width: WIDTH, height: HEIGHT });

  for (const p of pages) {
    const url = BASE + p.path;
    try {
      await page.goto(url, { waitUntil: 'domcontentloaded', timeout: 15000 });
      await new Promise(r => setTimeout(r, 1500));
      await page.screenshot({ path: `${OUT}/${p.name}.png`, fullPage: false });
      console.log(`OK: ${p.name}`);
    } catch (e) {
      console.log('FAIL: ' + p.name + ' - ' + e.message);
    }
  }

  await browser.close();
  console.log(`\nDone — ${pages.length} pages screenshot at ${OUT}`);
}

run();
