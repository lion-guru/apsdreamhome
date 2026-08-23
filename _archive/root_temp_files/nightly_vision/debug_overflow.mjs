import { chromium } from 'playwright';

const BASE = 'http://localhost/apsdreamhome';
const PASSWORD = 'Aps@2026';

function captchaFromCookie(cookie) {
  const sid = cookie.value;
  const f = `C:/xampp/tmp/sess_${sid}`;
  if (!fs.existsSync(f)) return null;
  const m = fs.readFileSync(f, 'utf8').match(/captcha_code\|s:\d+:"([^"]+)"/);
  return m ? m[1] : null;
}
import fs from 'fs';

async function loginCustomer(ctx, page) {
  await page.goto(BASE + '/auth/login', { waitUntil: 'domcontentloaded' });
  const code = captchaFromCookie((await ctx.cookies()).find(c => c.name === 'PHPSESSID'));
  await page.fill('input[name="identity"]', 'testuser@example.com');
  await page.fill('input[name="password"]', PASSWORD);
  await page.fill('input[name="captcha_code"]', code);
  await Promise.all([page.waitForNavigation({ waitUntil: 'domcontentloaded' }).catch(() => {}), page.click('form button[type="submit"]')]);
}
async function loginAssociate(ctx, page) {
  await page.goto(BASE + '/associate/login', { waitUntil: 'domcontentloaded' });
  const code = captchaFromCookie((await ctx.cookies()).find(c => c.name === 'PHPSESSID'));
  await page.fill('input[name="email"]', 'testassociate@example.com');
  await page.fill('input[name="password"]', PASSWORD);
  await page.fill('input[name="captcha_code"]', code);
  await Promise.all([page.waitForNavigation({ waitUntil: 'domcontentloaded' }).catch(() => {}), page.click('#submitBtn')]);
}

const browser = await chromium.launch();

for (const [label, path, login] of [
  ['USER-DASH-DESK', '/user/dashboard', loginCustomer],
  ['ASSOC-DASH-DESK', '/associate/dashboard', loginAssociate],
  ['ASSOC-DASH-MOBILE', '/associate/dashboard', loginAssociate],
]) {
  const vp = label.includes('MOBILE') ? { width: 390, height: 844 } : { width: 1440, height: 900 };
  const ctx = await browser.newContext({ viewport: vp });
  const page = await ctx.newPage();
  await login(ctx, page);
  await page.goto(BASE + path, { waitUntil: 'domcontentloaded' });
  await page.waitForTimeout(2000);

  const res = await page.evaluate(() => {
    const vw = document.documentElement.clientWidth;
    // does body actually scroll?
    const sw = Math.max(document.documentElement.scrollWidth, document.body.scrollWidth);
    const out = { vw, sw, culprits: [] };
    if (sw <= vw + 2) return out;

    function clippedByAncestor(el) {
      let n = el.parentElement;
      while (n && n !== document.body) {
        const cs = getComputedStyle(n);
        if (/(hidden|clip|auto|scroll)/.test(cs.overflowX)) return true;
        n = n.parentElement;
      }
      return false;
    }

    document.querySelectorAll('body *').forEach(el => {
      const r = el.getBoundingClientRect();
      if (r.right <= vw + 2) return;
      if (clippedByAncestor(el)) return;
      out.culprits.push({
        tag: el.tagName.toLowerCase(),
        cls: String(el.className || '').slice(0, 70),
        id: el.id || '',
        right: Math.round(r.right),
        width: Math.round(r.width),
        text: (el.innerText || '').trim().slice(0, 40).replace(/\n/g, '|'),
      });
    });
    // keep shallowest few
    out.culprits = out.culprits.slice(0, 15);
    return out;
  });

  console.log(`\n=== ${label} ===`);
  console.log(`vw=${res.vw} scrollWidth=${res.sw}`);
  res.culprits.forEach(c => console.log(`  <${c.tag} class="${c.cls}" id="${c.id}"> right=${c.right} w=${c.w || c.width} "${c.text}"`));
  await ctx.close();
}

await browser.close();
