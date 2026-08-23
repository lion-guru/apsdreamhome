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
    req.on('timeout', () => req.destroy(new Error('timeout')));
    req.write(body);
    req.end();
  });
}

const browser = await chromium.launch({ headless: true });
const ctx = await browser.newContext();
const page = await ctx.newPage();
await page.goto(BASE + '/auth/login', { waitUntil: 'domcontentloaded' });
const img = page.locator('img[src*="captcha"]').first();
console.log('captcha img count:', await img.count());
if (await img.count()) {
  const buf = await img.screenshot();
  fs.writeFileSync('testing/nightly_vision/captcha_sample.png', buf);
  console.log('captcha size:', buf.length, 'bytes');
  const b64 = buf.toString('base64');
  // upscale via sharp? Not installed. Try direct prompts:
  for (const prompt of [
    'What characters are written in this image? Reply with only the characters.',
    'This is a CAPTCHA image. Transcribe the alphanumeric code exactly.',
    'Read all visible text.',
  ]) {
    const r = await ollama(prompt, b64);
    console.log(`prompt="${prompt.slice(0, 40)}..." -> "${r}"`);
  }
}
await browser.close();
