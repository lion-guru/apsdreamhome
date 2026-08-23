import fs from 'fs';
import path from 'path';
import http from 'http';

const DIR = path.resolve('testing/nightly_vision');
const manifest = JSON.parse(fs.readFileSync(path.join(DIR, 'manifest.json'), 'utf8'));

function ollamaGenerate(prompt, imgB64) {
  return new Promise((resolve, reject) => {
    const body = JSON.stringify({ model: 'moondream', prompt, images: [imgB64], stream: false });
    const req = http.request({
      host: 'localhost', port: 11434, path: '/api/generate', method: 'POST',
      headers: { 'Content-Type': 'application/json', 'Content-Length': Buffer.byteLength(body) },
      timeout: 300000,
    }, res => {
      let data = '';
      res.on('data', c => { data += c; });
      res.on('end', () => {
        try {
          // stream:false returns a single JSON object
          resolve(JSON.parse(data).response || '');
        } catch (e) { reject(new Error('bad json: ' + data.slice(0, 100))); }
      });
    });
    req.on('error', reject);
    req.on('timeout', () => { req.destroy(new Error('timeout')); });
    req.write(body);
    req.end();
  });
}

const PROMPT = `You are a strict UI QA inspector reviewing a webpage screenshot. Report ONLY real visual problems you can see: broken/missing images (placeholder icons), overlapping or cut-off text, unreadable low-contrast text, obviously empty sections where content should be, visible PHP error text, misaligned layout. Ignore stylistic taste. Format: one issue per line starting with "- ". If the screenshot looks fine, reply exactly: NO_ISSUES`;

async function run() {
  const findings = [];
  for (const m of manifest) {
    const p = path.join(DIR, 'shots', m.file);
    if (!fs.existsSync(p)) continue;
    const b64 = fs.readFileSync(p).toString('base64');
    process.stdout.write(`analyzing ${m.tag} ... `);
    try {
      const resp = await ollamaGenerate(PROMPT, b64);
      const clean = (resp || '').trim();
      const hasIssues = clean && !clean.toUpperCase().includes('NO_ISSUES');
      console.log(hasIssues ? 'ISSUES' : 'ok');
      findings.push({ tag: m.tag, url: m.url, issues: hasIssues ? clean : null, raw: clean.slice(0, 800) });
    } catch (e) {
      console.log(`ERR ${e.message.slice(0, 80)}`);
      findings.push({ tag: m.tag, url: m.url, error: e.message.slice(0, 200) });
    }
  }
  fs.writeFileSync(path.join(DIR, 'moondream_findings.json'), JSON.stringify(findings, null, 2));
  const flagged = findings.filter(f => f.issues);
  console.log(`\n=== MOONDREAM SUMMARY ===`);
  console.log(`${flagged.length}/${findings.length} screenshots flagged`);
  flagged.forEach(f => {
    console.log(`\n--- ${f.tag} (${f.url})`);
    console.log(f.issues.split('\n').slice(0, 8).join('\n'));
  });
}

run().catch(e => { console.error(e); process.exit(1); });
