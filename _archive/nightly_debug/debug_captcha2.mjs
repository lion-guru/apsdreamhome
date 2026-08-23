import { chromium } from 'playwright';
import fs from 'fs';
import http from 'http';

const BASE = 'http://localhost/apsdreamhome';

function ollama(prompt, imgB64) {
  return new Promise((resolve, reject) => {
    const body = JSON.stringify({ model: 'moondream', prompt, images: [imgB64], stream: false });
    const req = http.request({
      host: 'localhost', port: 11434, path: '/api/generate', method: 'POST',
      headers: { 'Content-Type': 'application/json', 'Content-Length': Buffer.byteLength(body) },
      timeout: 180000,
    }, res => {
      let data = '';
      res.on('data', c => { data += c; });
      res.on('end', () => { try { resolve((JSON.parse(data).response || '').trim()); } catch (e) { reject(new Error(data.slice(0, 200))); } });
    });
    req.on('error', reject);
    req.write(body);
    req.end();
  });
}

const browser = await chromium.launch({ headless: true });

// context A loads login page (gets session + captcha)
const ctxA = await browser.newContext();
const pageA = await ctxA.newPage();
await pageA.goto(BASE + '/auth/login', { waitUntil: 'domcontentloaded' });
const buf = await pageA.locator('img[src*="captcha"]').first().screenshot();

// context B renders upscaled version
const ctxB = await browser.newContext({ viewport: { width: 1000, height: 300 } });
const pageB = await ctxB.newPage();
const dataUrl = `data:image/png;base64,${buf.toString('base64')}`;
await pageB.setContent(`<body style="margin:0;background:#fff"><img id="c" src="${dataUrl}" style="width:800px;image-rendering:pixelated"></body>`);
await pageB.waitForTimeout(500);
const bigBuf = await pageB.locator('#c').screenshot();
fs.writeFileSync('testing/nightly_vision/captcha_big.png', bigBuf);

const r = await ollama('This is a CAPTCHA image containing 6 uppercase alphanumeric characters. Transcribe ONLY the 6-character code.', bigBuf.toString('base64'));
console.log('OCR upscaled:', JSON.stringify(r));

// verify by logging in through pageA with this code
const code = r.replace(/[^A-Za-z0-9]/g, '').toUpperCase();
if (code.length >= 4) {
  await pageA.fill('input[name="identity"]', 'testuser@example.com');
  await pageA.fill('input[name="password"]', 'Aps@2026');
  await pageA.fill('input[name="captcha_code"]', code);
  await Promise.all([
    pageA.waitForNavigation({ waitUntil: 'domcontentloaded', timeout: 30000 }).catch(() => {}),
    pageA.click('form button[type="submit"]'),
  ]);
  await pageA.waitForTimeout(2000);
  console.log('final URL:', pageA.url());
}
await browser.close();
