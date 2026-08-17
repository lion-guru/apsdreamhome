import fs from 'fs';
import path from 'path';

const SCREENSHOTS_DIR = 'C:\\xampp\\htdocs\\apsdreamhome\\visual_audit_output';
const OUTPUT_FILE = 'C:\\xampp\\htdocs\\apsdreamhome\\visual_audit_output\\vision_analysis.json';

const files = fs.readdirSync(SCREENSHOTS_DIR)
  .filter(f => f.endsWith('.png'))
  .sort();

const PROMPT = `Analyze this admin dashboard screenshot for UI/UX issues. Report ONLY problems as JSON array:

1. **Empty/broken data**: Tables with no rows, "No data" messages where data should exist, empty charts
2. **Layout issues**: Overlapping elements, cut-off text, misaligned columns, horizontal scrollbars
3. **Broken components**: Missing images (broken image icons), non-functional buttons, broken dropdowns
4. **Console errors visible**: Red error text, JS error overlays
5. **Missing content**: Placeholder text like "Coming soon", "Under construction", "Lorem ipsum"
6. **Visual bugs**: Overlapping modals, z-index issues, cutoff modals
7. **Responsive issues**: Content overflowing container on desktop

Output: JSON array ONLY. Each issue: { "severity": "high|medium|low", "category": "empty_data|layout|broken_component|console_error|placeholder|visual_bug|responsive", "description": "specific issue" }. If no issues, return [].`;

async function analyzeImage(imagePath, pageName) {
  const imageBuffer = fs.readFileSync(imagePath);
  const base64 = imageBuffer.toString('base64');
  
  try {
    const response = await fetch('http://localhost:11434/api/generate', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        model: 'moondream',
        prompt: PROMPT,
        images: [base64],
        stream: false,
        options: { temperature: 0.1, num_predict: 500 }
      })
    });
    
    const data = await response.json();
    const text = data.response || '';
    
    // Extract JSON array from response
    let issues = [];
    const jsonMatch = text.match(/\[[\s\S]*?\]/);
    if (jsonMatch) {
      try {
        issues = JSON.parse(jsonMatch[0]);
      } catch (e) {
        // Try cleaning the response
        const cleaned = jsonMatch[0].replace(/```json|```/g, '').trim();
        try { issues = JSON.parse(cleaned); } catch {}
      }
    }
    
    return { page: pageName, issues };
  } catch (e) {
    return { page: pageName, issues: [{ severity: 'high', category: 'analysis_error', description: e.message }] };
  }
}

async function main() {
  // Load existing results if any
  let results = [];
  if (fs.existsSync(OUTPUT_FILE)) {
    try {
      results = JSON.parse(fs.readFileSync(OUTPUT_FILE, 'utf8'));
    } catch {}
  }
  
  const analyzedPages = new Set(results.map(r => r.page));
  
  for (const file of files) {
    const pageName = file.replace('.png', '');
    if (analyzedPages.has(pageName)) {
      console.log(`Skipping (already done): ${pageName}`);
      continue;
    }
    
    console.log(`Analyzing: ${pageName}`);
    const result = await analyzeImage(path.join(SCREENSHOTS_DIR, file), pageName);
    results.push(result);
    
    if (result.issues.length > 0) {
      console.log(`  ⚠️ ${result.issues.length} issues found`);
      result.issues.forEach(i => console.log(`    [${i.severity}] ${i.category}: ${i.description}`));
    } else {
      console.log(`  ✓ Clean`);
    }
    
    // Save incrementally
    fs.writeFileSync(OUTPUT_FILE, JSON.stringify(results, null, 2));
  }
  
  console.log(`\n=== SUMMARY ===`);
  const totalIssues = results.reduce((sum, r) => sum + r.issues.length, 0);
  const highIssues = results.flatMap(r => r.issues).filter(i => i.severity === 'high').length;
  const mediumIssues = results.flatMap(r => r.issues).filter(i => i.severity === 'medium').length;
  
  console.log(`Pages analyzed: ${results.length}`);
  console.log(`Total issues: ${totalIssues} (High: ${highIssues}, Medium: ${mediumIssues})`);
  
  results.filter(r => r.issues.length > 0).forEach(r => {
    console.log(`\n${r.page}:`);
    r.issues.forEach(i => console.log(`  [${i.severity}] ${i.category}: ${i.description}`));
  });
}

main().catch(console.error);