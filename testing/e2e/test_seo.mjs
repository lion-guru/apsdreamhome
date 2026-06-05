/**
 * test_seo.mjs
 * Verifies SEO meta tags + structured data
 *
 * Checks (homepage /):
 *   1. <title> is present and not empty
 *   2. <meta name="description"> is present
 *   3. Open Graph tags: og:title, og:description, og:image, og:type
 *   4. Twitter Card tags: twitter:card, twitter:title
 *   5. canonical link (optional but recommended)
 *   6. lang attribute on <html>
 *   7. JSON-LD structured data present (Organization or WebSite schema)
 *
 * Checks (/properties):
 *   8. <title> contains "Properties"
 *   9. JSON-LD ItemList schema (itemListElement) is present
 */

import { chromium } from 'playwright';

const BASE = 'http://localhost/apsdreamhome';
const SCREENSHOT_DIR = 'testing/screenshots';

const results = { pass: 0, fail: 0, details: [] };
function check(name, pass, info = '') {
  const icon = pass ? 'PASS' : 'FAIL';
  console.log(`  ${icon}  ${name}${info ? ' — ' + info : ''}`);
  results.details.push({ name, pass, info });
  if (pass) results.pass++; else results.fail++;
}

async function main() {
  const browser = await chromium.launch({ headless: true });
  const ctx = await browser.newContext({ viewport: { width: 1280, height: 800 } });
  const page = await ctx.newPage();

  try {
    /* ============= HOMEPAGE ============= */
    console.log('\n--- Fetch homepage ---');
    await page.goto(`${BASE}/`, { waitUntil: 'load', timeout: 15000 });
    await page.waitForTimeout(500);
    await page.screenshot({ path: `${SCREENSHOT_DIR}/seo-01-homepage.png` });

    // Title
    const title = await page.title();
    check('<title> present and non-empty', !!title && title.length > 5, `"${title}"`);

    // Meta description
    const metaDesc = await page.getAttribute('meta[name="description"]', 'content').catch(() => null);
    check('<meta name="description"> present', !!metaDesc && metaDesc.length > 20, metaDesc ? `(${metaDesc.length} chars: "${metaDesc.slice(0, 60)}...")` : 'missing');

    // Open Graph tags
    const ogTitle = await page.getAttribute('meta[property="og:title"]', 'content').catch(() => null);
    const ogDesc = await page.getAttribute('meta[property="og:description"]', 'content').catch(() => null);
    const ogImage = await page.getAttribute('meta[property="og:image"]', 'content').catch(() => null);
    const ogType = await page.getAttribute('meta[property="og:type"]', 'content').catch(() => null);
    check('og:title present', !!ogTitle, ogTitle ? `"${ogTitle}"` : 'missing');
    check('og:description present', !!ogDesc, ogDesc ? `(${ogDesc.length} chars)` : 'missing');
    check('og:image present', !!ogImage, ogImage ? `"${ogImage}"` : 'missing');
    check('og:type present', !!ogType, ogType ? `"${ogType}"` : 'missing');

    // Twitter Card tags
    const twCard = await page.getAttribute('meta[name="twitter:card"]', 'content').catch(() => null);
    const twTitle = await page.getAttribute('meta[name="twitter:title"]', 'content').catch(() => null);
    check('twitter:card present', !!twCard, twCard ? `"${twCard}"` : 'missing');
    check('twitter:title present', !!twTitle, twTitle ? `"${twTitle}"` : 'missing');

    // canonical
    const canonical = await page.getAttribute('link[rel="canonical"]', 'href').catch(() => null);
    check('canonical link present (recommended)', !!canonical, canonical ? `"${canonical}"` : 'missing');

    // lang
    const lang = await page.getAttribute('html', 'lang').catch(() => null);
    check('<html lang="..."> attribute set', !!lang, lang ? `"${lang}"` : 'missing');

    // JSON-LD presence
    const jsonLdScripts = await page.$$eval('script[type="application/ld+json"]', els =>
      els.map(e => { try { return JSON.parse(e.textContent); } catch { return null; } }).filter(Boolean)
    );
    check('JSON-LD structured data present', jsonLdScripts.length > 0, `${jsonLdScripts.length} script(s)`);
    if (jsonLdScripts.length > 0) {
      const types = jsonLdScripts.map(j => j['@type']).filter(Boolean);
      check('JSON-LD includes Organization / WebSite / BreadcrumbList type', types.some(t => /Organization|WebSite|BreadcrumbList|RealEstate/i.test(t)), `types: ${types.join(', ')}`);
    }

    /* ============= /properties ============= */
    console.log('\n--- Fetch /properties ---');
    await page.goto(`${BASE}/properties`, { waitUntil: 'load', timeout: 15000 });
    await page.waitForTimeout(500);
    await page.screenshot({ path: `${SCREENSHOT_DIR}/seo-02-properties.png` });

    const propTitle = await page.title();
    check('<title> on /properties contains "Properties"', /Properties/i.test(propTitle), `"${propTitle}"`);

    const propMetaDesc = await page.getAttribute('meta[name="description"]', 'content').catch(() => null);
    check('<meta name="description"> on /properties', !!propMetaDesc && propMetaDesc.length > 20, propMetaDesc ? `(${propMetaDesc.length} chars)` : 'missing');

    // JSON-LD ItemList
    const propJsonLd = await page.$$eval('script[type="application/ld+json"]', els =>
      els.map(e => { try { return JSON.parse(e.textContent); } catch { return null; } }).filter(Boolean)
    );
    check('JSON-LD scripts on /properties', propJsonLd.length > 0, `${propJsonLd.length} script(s)`);
    const itemListSchema = propJsonLd.find(j => j['@type'] === 'ItemList');
    check('JSON-LD ItemList schema on /properties', !!itemListSchema, itemListSchema ? `numberOfItems=${itemListSchema.numberOfItems ?? '?'}` : 'no ItemList');
    if (itemListSchema) {
      check('ItemList has itemListElement array', Array.isArray(itemListSchema.itemListElement), `itemListElement.length=${(itemListSchema.itemListElement || []).length}`);
    }
  } catch (err) {
    console.error('\nFATAL:', err.message);
    check('Run completed without exception', false, err.message);
  } finally {
    await browser.close();
  }

  console.log('\n' + '='.repeat(60));
  console.log(`SEO E2E: ${results.pass} pass, ${results.fail} fail`);
  console.log('='.repeat(60));
  process.exit(results.fail > 0 ? 1 : 0);
}

main();
