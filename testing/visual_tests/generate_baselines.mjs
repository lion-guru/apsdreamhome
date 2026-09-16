import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

const SOURCE_DIRS = [
  path.join(__dirname, 'screenshots'),
  __dirname,
];
const BASELINE_DESKTOP = path.join(__dirname, 'baselines', 'desktop');
const BASELINE_MOBILE = path.join(__dirname, 'baselines', 'mobile');

const PAGE_MAP = {
  'home': ['session90_home.png', 'scan_home.png', 'debug_homepage.png'],
  'properties': ['scan_properties.png', 'session90_properties.png'],
  'colonies': ['scan_colonies.png', 'session90_colonies.png'],
  'login': ['customer_login_page.png', 'final_customer_login.png', 'admin_login_page.png'],
  'admin-dashboard': ['admin_01_dashboard.png', 'admin_dashboard.png', 'final_admin_dashboard.png'],
  'customer-dashboard': ['customer_dashboard.png', 'final_dashboard.png', 'user_dashboard.png'],
  'associate-dashboard': ['associate_dashboard.png'],
  'agent-dashboard': ['agent_dashboard.png'],
  'property-detail': ['property_detail.png', 'scan_property_detail.png'],
  'emi-schedule': ['emi_schedule.png'],
  'payment': ['payment.png', 'payments.png'],
};

function findBestMatch(pageName) {
  const candidates = PAGE_MAP[pageName] || [];
  for (const sourceDir of SOURCE_DIRS) {
    for (const candidate of candidates) {
      const fullPath = path.join(sourceDir, candidate);
      if (fs.existsSync(fullPath)) {
        return fullPath;
      }
    }
  }
  return null;
}

function copyBaselines() {
  console.log('Generating baseline screenshots from existing captures...\n');

  let desktopCount = 0;
  let mobileCount = 0;

  for (const pageName of Object.keys(PAGE_MAP)) {
    const sourceFile = findBestMatch(pageName);
    if (sourceFile) {
      const targetDesktop = path.join(BASELINE_DESKTOP, `${pageName}.png`);
      const targetMobile = path.join(BASELINE_MOBILE, `${pageName}.png`);

      fs.copyFileSync(sourceFile, targetDesktop);
      console.log(`✓ ${pageName} (desktop) <- ${path.basename(sourceFile)}`);
      desktopCount++;

      fs.copyFileSync(sourceFile, targetMobile);
      console.log(`✓ ${pageName} (mobile) <- ${path.basename(sourceFile)}`);
      mobileCount++;
    } else {
      console.log(`⚠ ${pageName} - No source screenshot found`);
    }
  }

  console.log(`\nDone! ${desktopCount} desktop + ${mobileCount} mobile baselines created.`);
  console.log(`Desktop: ${BASELINE_DESKTOP}`);
  console.log(`Mobile: ${BASELINE_MOBILE}`);
}

copyBaselines();