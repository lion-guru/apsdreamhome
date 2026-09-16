import { test, expect } from '@playwright/test';
import pixelmatch from 'pixelmatch';
import { PNG } from 'pngjs';
import fs from 'fs';
import path from 'path';

const BASE_URL = process.env.BASE_URL || 'http://localhost/apsdreamhome';
const BASELINE_DIR = path.join(__dirname, 'baselines');
const SCREENSHOT_DIR = path.join(__dirname, 'screenshots', 'current');
const DIFF_DIR = path.join(__dirname, 'screenshots', 'diff');

const VIEWPORTS = [
  { width: 1280, height: 800, name: 'desktop' },
  { width: 390, height: 844, name: 'mobile' },
];

const PAGES = [
  { path: '/', name: 'home', roles: ['public'] },
  { path: '/properties', name: 'properties', roles: ['public'] },
  { path: '/colonies', name: 'colonies', roles: ['public'] },
  { path: '/auth/login', name: 'login', roles: ['public'] },
  { path: '/admin/login?test_login=1', name: 'admin-dashboard', roles: ['admin'] },
  { path: '/user/dashboard', name: 'customer-dashboard', roles: ['customer'] },
  { path: '/associate/dashboard', name: 'associate-dashboard', roles: ['associate'] },
  { path: '/agent/dashboard', name: 'agent-dashboard', roles: ['agent'] },
  { path: '/property-detail/1', name: 'property-detail', roles: ['public'] },
  { path: '/user/emi-schedule', name: 'emi-schedule', roles: ['customer'] },
  { path: '/user/payments', name: 'payment', roles: ['customer'] },
];

async function loginAs(page, role) {
  if (role === 'admin') {
    await page.goto(`${BASE_URL}/admin/login?test_login=1`, { waitUntil: 'domcontentloaded' });
    await page.waitForTimeout(1000);
    return;
  }
  if (role === 'customer') {
    await page.goto(`${BASE_URL}/auth/login`, { waitUntil: 'domcontentloaded' });
    await page.fill('input[name="email"]', 'testuser@example.com');
    await page.fill('input[name="password"]', 'Aps@2026');
    await page.click('button[type="submit"]');
    await page.waitForURL('**/user/dashboard**', { timeout: 10000 });
    return;
  }
  if (role === 'associate') {
    await page.goto(`${BASE_URL}/associate/login`, { waitUntil: 'domcontentloaded' });
    await page.fill('input[name="email"]', 'testassociate@example.com');
    await page.fill('input[name="password"]', 'Aps@2026');
    await page.click('button[type="submit"]');
    await page.waitForURL('**/associate/dashboard**', { timeout: 10000 });
    return;
  }
  if (role === 'agent') {
    await page.goto(`${BASE_URL}/agent/login`, { waitUntil: 'domcontentloaded' });
    await page.fill('input[name="email"]', 'agent@example.com');
    await page.fill('input[name="password"]', 'Aps@2026');
    await page.click('button[type="submit"]');
    await page.waitForURL('**/agent/dashboard**', { timeout: 10000 });
    return;
  }
}

async function captureScreenshot(page, pageName, viewportName) {
  const dir = path.join(SCREENSHOT_DIR, viewportName);
  if (!fs.existsSync(dir)) {
    fs.mkdirSync(dir, { recursive: true });
  }
  const filePath = path.join(dir, `${pageName}.png`);
  await page.screenshot({ path: filePath, fullPage: true });
  return filePath;
}

async function compareImages(baselinePath, currentPath, diffPath, threshold = 0.001) {
  if (!fs.existsSync(baselinePath)) {
    return { match: false, reason: 'Baseline not found', diffPixels: 0, totalPixels: 0 };
  }

  const baselineImg = PNG.sync.read(fs.readFileSync(baselinePath));
  const currentImg = PNG.sync.read(fs.readFileSync(currentPath));

  if (baselineImg.width !== currentImg.width || baselineImg.height !== currentImg.height) {
    return { match: false, reason: 'Dimension mismatch', diffPixels: -1, totalPixels: baselineImg.width * baselineImg.height };
  }

  const diffImg = new PNG({ width: baselineImg.width, height: baselineImg.height });
  const diffPixels = pixelmatch(baselineImg.data, currentImg.data, diffImg.data, baselineImg.width, baselineImg.height, {
    threshold: 0.1,
    includeAA: true,
    alpha: 0.1,
  });

  const totalPixels = baselineImg.width * baselineImg.height;
  const diffRatio = diffPixels / totalPixels;

  if (!fs.existsSync(path.dirname(diffPath))) {
    fs.mkdirSync(path.dirname(diffPath), { recursive: true });
  }
  fs.writeFileSync(diffPath, PNG.sync.write(diffImg));

  return {
    match: diffRatio <= threshold,
    diffPixels,
    totalPixels,
    diffRatio: (diffRatio * 100).toFixed(4) + '%',
  };
}

for (const viewport of VIEWPORTS) {
  test.describe(`Visual Regression - ${viewport.name}`, () => {
    test.use({ viewport: { width: viewport.width, height: viewport.height } });

    for (const pageConfig of PAGES) {
      test(`${pageConfig.name} @ ${viewport.name}`, async ({ page }) => {
        const requiresAuth = pageConfig.roles.some(r => r !== 'public');

        if (requiresAuth) {
          const role = pageConfig.roles.find(r => r !== 'public');
          await loginAs(page, role);
        } else {
          await page.goto(`${BASE_URL}${pageConfig.path}`, { waitUntil: 'domcontentloaded', timeout: 15000 });
        }

        await page.waitForLoadState('networkidle');
        await page.waitForTimeout(1000);

        const currentPath = await captureScreenshot(page, pageConfig.name, viewport.name);
        const baselinePath = path.join(BASELINE_DIR, viewport.name, `${pageConfig.name}.png`);
        const diffPath = path.join(DIFF_DIR, viewport.name, `${pageConfig.name}-diff.png`);

        const result = await compareImages(baselinePath, currentPath, diffPath);

        if (!result.match) {
          const errorMsg = `Visual regression detected for ${pageConfig.name} (${viewport.name}): ${result.reason || `Diff: ${result.diffRatio} (${result.diffPixels}/${result.totalPixels} pixels)`}\nDiff saved to: ${diffPath}`;
          console.error(errorMsg);
          throw new Error(errorMsg);
        }

        console.log(`✓ ${pageConfig.name} (${viewport.name}) - Match: ${result.diffRatio} diff`);
      });
    }
  });
}