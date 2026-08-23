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
const ctxA = await browser.newContext();
const pageA = await ctxA.newPage();
await pageA.goto(BASE + '/auth/login', { waitUntil: 'domcontentloaded' });
const buf = await pageA.locator('img[src*="captcha"]').first().screenshot();

// Clean: threshold out light-gray noise, upscale 8x
const ctxB = await browser.newContext({ viewport: { width: 2200, height: 700 } });
const pageB = await ctxB.newPage();
await pageB.setContent(`<canvas id="cv"></canvas>`);
const out = await pageB.evaluate((dataUrl) => {
  return new Promise(resolve => {
    const img = new Image();
    img.onload = () => {
      const scale = 8;
      const cv = document.getElementById('cv');
      cv.width = img.width * scale; cv.height = img.height * scale;
      const g = cv.getContext('2d');
      // draw small first
      const tmp = document.createElement('canvas');
      tmp.width = img.width; tmp.height = img.height;
      const tg = tmp.getContext('2d');
      tg.drawImage(img, 0, 0);
      const d = tg.getImageData(0, 0, tmp.width, tmp.height);
      for (let i = 0; i < d.data.length; i += 4) {
        const v = d.data[i]; // grayscale-ish (all channels equal)
        const nv = v < 130 ? 0 : 255;
        d.data[i] = d.data[i+1] = d.data[i+2] = nv;
        d.data[i+3] = 255;
      }
      tg.putImageData(d, 0, 0);
      g.imageSmoothingEnabled = false;
      g.drawImage(tmp, 0, 0, cv.width, cv.height);
      resolve(cv.toDataURL('image/png'));
    };
    img.src = dataUrl;
  });
}, `data:image/png;base64,${buf.toString('base64')}`);

fs.writeFileSync('testing/nightly_vision/captcha_clean.png', Buffer.from(out.split(',')[1], 'base64'));
const r = await ollama('This CAPTCHA contains exactly 6 characters using only letters ABCDEFGHJKLMNPQRSTUVWXYZ and digits 23456789. What are the 6 characters?', out.split(',')[1]);
console.log('OCR cleaned:', JSON.stringify(r));

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
