/**
 * E2E_NEW_FEATURES.mjs
 * Combined test runner — runs all 6 new-feature E2E tests sequentially.
 *
 * Tests:
 *   1. test_live_chat.mjs        — Live Chat widget (visitor + admin reply)
 *   2. test_2fa.mjs              — 2FA / TOTP enable + backup codes
 *   3. test_backup.mjs           — Admin backup page + create + health
 *   4. test_security_headers.mjs — CSP, HSTS, X-Frame-Options, lazy loading, gzip
 *   5. test_seo.mjs              — Title, meta, OG, Twitter, JSON-LD
 *   6. test_performance.mjs      — Response time, gzip, cache headers
 *
 * Exit code: 0 = all pass, 1 = any fail
 */

import { spawn } from 'child_process';
import { fileURLToPath } from 'url';
import path from 'path';

const __dirname = path.dirname(fileURLToPath(import.meta.url));

const tests = [
  { name: 'Live Chat Widget', file: 'test_live_chat.mjs' },
  { name: '2FA / TOTP',        file: 'test_2fa.mjs' },
  { name: 'Admin Backup',      file: 'test_backup.mjs' },
  { name: 'Security Headers',  file: 'test_security_headers.mjs' },
  { name: 'SEO Meta + JSON-LD', file: 'test_seo.mjs' },
  { name: 'Performance',       file: 'test_performance.mjs' },
];

const testDir = path.join(__dirname, '..', 'e2e');

function runOne(test) {
  return new Promise((resolve) => {
    const fullPath = path.join(testDir, test.file);
    const start = Date.now();
    console.log('\n' + '='.repeat(72));
    console.log(`▶ ${test.name} — node ${fullPath}`);
    console.log('='.repeat(72));
    const proc = spawn('node', [fullPath], { stdio: 'inherit' });
    proc.on('close', (code) => {
      const elapsed = ((Date.now() - start) / 1000).toFixed(1);
      const pass = code === 0;
      console.log(`\n  ⏱  ${test.name} completed in ${elapsed}s — exit code ${code}`);
      resolve({ name: test.name, file: test.file, code, pass, elapsed });
    });
    proc.on('error', (err) => {
      console.error(`  ✗ Failed to spawn ${test.file}: ${err.message}`);
      resolve({ name: test.name, file: test.file, code: -1, pass: false, elapsed: '0' });
    });
  });
}

async function main() {
  console.log('═══════════════════════════════════════════════════════════════════════');
  console.log('  APS Dream Home — E2E Test Suite (New Features)');
  console.log(`  ${tests.length} tests · running sequentially`);
  console.log('═══════════════════════════════════════════════════════════════════════');

  const results = [];
  for (const test of tests) {
    const result = await runOne(test);
    results.push(result);
  }

  console.log('\n' + '═'.repeat(72));
  console.log('  SUMMARY');
  console.log('═'.repeat(72));
  let pass = 0, fail = 0;
  for (const r of results) {
    const icon = r.pass ? '✅ PASS' : '❌ FAIL';
    console.log(`  ${icon}  ${r.name.padEnd(28)}  (${r.file})  ${r.elapsed}s`);
    if (r.pass) pass++; else fail++;
  }
  console.log('-'.repeat(72));
  console.log(`  Total: ${pass} pass, ${fail} fail (${results.length} tests)`);
  console.log('═'.repeat(72));

  process.exit(fail > 0 ? 1 : 0);
}

main();
