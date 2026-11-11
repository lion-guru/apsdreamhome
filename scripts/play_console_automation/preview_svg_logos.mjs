import { CDPClient } from './cdp_helper.mjs';
import fs from 'fs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    const wideSvg = fs.readFileSync('assets/images/logo/apslogo-wide.svg', 'utf8');
    const squareSvg = fs.readFileSync('assets/images/logo/apslogo.svg', 'utf8');

    const pageContent = `<!DOCTYPE html>
    <html>
    <head>
      <meta charset="utf-8">
      <title>APS Dream Home Logos</title>
      <style>
        body { background: #e2e8f0; font-family: sans-serif; padding: 40px; text-align: center; }
        .box { background: white; border-radius: 16px; padding: 30px; margin: 20px auto; max-width: 800px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
        h3 { color: #334155; margin-bottom: 20px; }
      </style>
    </head>
    <body>
      <div class="box">
        <h3>Wide SVG (apslogo-wide.svg)</h3>
        ${wideSvg}
      </div>
      <div class="box">
        <h3>Square SVG (apslogo.svg)</h3>
        <div style="max-width: 320px; margin: 0 auto;">
          ${squareSvg}
        </div>
      </div>
    </body>
    </html>`;

    await cdp.send('Page.navigate', { url: 'data:text/html;charset=utf-8,' + encodeURIComponent(pageContent) });
    await cdp.wait(2000);
    await cdp.screenshot('svg_preview_direct.png');
    console.log('Saved screenshot: svg_preview_direct.png');
    cdp.close();
}

main().catch(console.error);
