import { chromium } from 'playwright';
const BASE='http://localhost/apsdreamhome';
async function run(){
  const browser=await chromium.launch({headless:true});
  const ctx=await browser.newContext();
  const page=await ctx.newPage();
  const errs=[], failed=[];
  page.on('console', m=>{ if(m.type()==='error') errs.push(m.text()); });
  page.on('requestfailed', r=>failed.push(r.url()+' '+r.failure().errorText));
  page.on('response', r=>{ if(r.status()>=400 && !r.url().includes('favicon')) failed.push(r.url()+' '+r.status()); });
  await page.goto(BASE+'/',{waitUntil:'domcontentloaded',timeout:30000});
  await page.waitForTimeout(3000);
  // check for style-xxxxx
  const styleCount=await page.evaluate(()=>document.querySelectorAll('[class*="style-"]').length);
  const img404=await page.evaluate(()=>[...document.images].filter(i=>!i.complete || i.naturalWidth===0).map(i=>i.src));
  const hasHero=await page.evaluate(()=>!!document.querySelector('.hero-premium'));
  const hasJourney=await page.evaluate(()=>!!document.querySelector('.journey-section'));
  const hasStats=await page.evaluate(()=>!!document.querySelector('.stats-banner'));
  const hasParticles=await page.evaluate(()=>!!document.getElementById('particles-canvas'));
  console.log('style-xxxxx elements:',styleCount);
  console.log('broken images:',img404.length, img404.slice(0,5));
  console.log('hero:',hasHero,'journey:',hasJourney,'stats:',hasStats,'particles:',hasParticles);
  console.log('console errors:',errs.length); errs.slice(0,5).forEach(e=>console.log('  ERR',e));
  console.log('failed requests:',failed.length); failed.slice(0,10).forEach(f=>console.log('  FAIL',f));
  await browser.close();
}
run().catch(e=>{console.error(e);process.exit(1)});
