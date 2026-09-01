import { chromium } from 'playwright';
const BASE='http://localhost/apsdreamhome';
async function check(page, selector, name){
  const el=await page.$(selector);
  if(!el){ console.log(`  MISS ${name} (${selector})`); return false; }
  const box=await el.boundingBox();
  const visible=await el.isVisible();
  const style=await el.evaluate(e=>{ const s=getComputedStyle(e); return {display:s.display, visibility:s.visibility, opacity:s.opacity, color:s.color, bg:s.backgroundColor}; });
  console.log(`  ${visible?'OK':'HIDDEN'} ${name}: box=${box?`${Math.round(box.width)}x${Math.round(box.height)} @${Math.round(box.y)}`:'null'} display=${style.display} opacity=${style.opacity}`);
  return visible && box && box.width>10;
}
async function run(){
  const browser=await chromium.launch({headless:true});
  for(const vp of [{w:1280,h:800,n:'desktop'},{w:390,h:844,n:'mobile'}]){
    console.log(`\n=== ${vp.n} ${vp.w}x${vp.h} ===`);
    const ctx=await browser.newContext({viewport:{width:vp.w,height:vp.h}});
    const page=await ctx.newPage();
    await page.goto(BASE+'/',{waitUntil:'domcontentloaded',timeout:30000});
    await page.waitForTimeout(2500);
    await check(page, '.hero-premium','hero');
    await check(page, '.hero-premium h1','hero h1');
    await check(page, '.hero-premium .btn-premium','hero CTA');
    await check(page, '.stats-banner','stats banner');
    await check(page, '.journey-section','journey');
    await check(page, '.construction-grid','construction grid');
    await check(page, '.colony-card, .service-card','colony cards');
    await check(page, '.projects-section','projects');
    await check(page, '.emi-section','emi calculator');
    await check(page, '.premium-header','header');
    await check(page, '.mobile-bottom-nav, .mobile-bottom-sticky-nav','mobile nav');
    // check for overlapping: hero vs stats
    const overlap=await page.evaluate(()=>{
      const hero=document.querySelector('.hero-premium'), stats=document.querySelector('.stats-banner');
      if(!hero||!stats) return 'no hero/stats';
      const hr=hero.getBoundingClientRect(), sr=stats.getBoundingClientRect();
      return `hero bottom ${Math.round(hr.bottom)} stats top ${Math.round(sr.top)} gap ${Math.round(sr.top-hr.bottom)}`;
    });
    console.log('  layout:',overlap);
    // check horizontal scroll
    const scrollW=await page.evaluate(()=>document.documentElement.scrollWidth - window.innerWidth);
    console.log('  h-scroll overflow:',scrollW, scrollW>5?'OVERFLOW':'ok');
    await ctx.close();
  }
  await browser.close();
}
run().catch(e=>{console.error(e);process.exit(1)});
