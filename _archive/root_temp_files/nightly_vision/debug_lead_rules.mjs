import { chromium } from 'playwright';
const b = await chromium.launch();
const p = await (await b.newContext({ viewport: { width: 390, height: 844 } })).newPage();
await p.goto('http://localhost/apsdreamhome/contact', { waitUntil: 'domcontentloaded' });
await p.waitForTimeout(1000);
const client = await p.context().newCDPSession(p);
await client.send('DOM.enable');
await client.send('CSS.enable');
const { root } = await client.send('DOM.getDocument', { depth: -1 });
async function matched(selector) {
  const q = await client.send('DOM.querySelector', { nodeId: root.nodeId, selector }).catch(() => ({ nodeId: 0 }));
  if (!q.nodeId) return null;
  const r = await client.send('CSS.getMatchedStylesForNode', { nodeId: q.nodeId });
  return {
    computed: await p.evaluate(s => { const el = document.querySelector(s); return el ? getComputedStyle(el).color : null; }, selector),
    rows: (r.matchedCSSRules || []).filter(m => m.rule.style && ((m.rule.style.cssText || '').includes('color'))).map(m => ({
      sel: (m.rule.selectorList?.text || '?').slice(0, 120),
      color: (m.rule.style.cssText || '').match(/color:[^;]*/)?.[0],
    })).slice(-6),
  };
}
console.log(JSON.stringify({ h4: await matched('.card-header.bg-primary h4') }, null, 1));
process.exit(0);
