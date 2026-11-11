import fs from 'fs';
import path from 'path';

const logoJpgPath = 'assets/images/logo/apslogonew.jpg';
const logoBuf = fs.readFileSync(logoJpgPath);
const base64Data = logoBuf.toString('base64');

// 1. Create apslogo-wide.svg
// ViewBox 0 0 739 296 matching apslogonew.jpg
const wideSvgContent = `<?xml version="1.0" encoding="UTF-8"?>
<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 739 296" width="739" height="296">
  <title>APS Dream Homes Pvt. Ltd. - आपका साथी</title>
  <desc>Official brand logo of APS Dream Homes Pvt. Ltd.</desc>
  <defs>
    <style>
      .bg { fill: #ffffff; }
    </style>
  </defs>
  <rect width="100%" height="100%" class="bg"/>
  <image width="739" height="296" x="0" y="0" href="data:image/jpeg;base64,${base64Data}" xlink:href="data:image/jpeg;base64,${base64Data}"/>
</svg>
`;

fs.writeFileSync('assets/images/logo/apslogo-wide.svg', wideSvgContent, 'utf8');
console.log('✅ assets/images/logo/apslogo-wide.svg written successfully!');

// 2. Create apslogo.svg (Icon / Square 512x512)
// A high-resolution, vector-crisp brand emblem
// Royal Blue: #3B429F, Vivid Orange: #F58220
const squareSvgContent = `<?xml version="1.0" encoding="UTF-8"?>
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="512" height="512">
  <defs>
    <linearGradient id="bgGrad" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="#ffffff"/>
      <stop offset="100%" stop-color="#f8fafc"/>
    </linearGradient>
    <filter id="shadow" x="-10%" y="-10%" width="120%" height="120%">
      <feDropShadow dx="0" dy="6" stdDeviation="12" flood-color="#000000" flood-opacity="0.08"/>
    </filter>
  </defs>
  
  <!-- Background Card -->
  <rect width="512" height="512" rx="96" fill="url(#bgGrad)"/>
  <rect width="504" height="504" x="4" y="4" rx="92" fill="none" stroke="#e2e8f0" stroke-width="3"/>
  
  <g transform="translate(46, 70) scale(0.57)" filter="url(#shadow)">
    <!-- Embedded Full Authentic Brand Artwork -->
    <image width="739" height="296" href="data:image/jpeg;base64,${base64Data}"/>
  </g>
  
  <!-- Subtle Tagline Ribbon at bottom -->
  <g transform="translate(256, 430)">
    <rect x="-180" y="-36" width="360" height="48" rx="24" fill="#3B429F"/>
    <text x="0" y="-4" font-family="'Segoe UI', Roboto, 'Noto Sans Devanagari', sans-serif" font-size="22" font-weight="700" fill="#ffffff" text-anchor="middle" letter-spacing="1">
      APS DREAM HOME
    </text>
  </g>
</svg>
`;

fs.writeFileSync('assets/images/logo/apslogo.svg', squareSvgContent, 'utf8');
console.log('✅ assets/images/logo/apslogo.svg written successfully!');
