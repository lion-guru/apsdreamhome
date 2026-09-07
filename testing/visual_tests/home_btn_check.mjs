import { chromium } from 'playwright';
const BASE='http://localhost/apsdreamhome';
async function check(){
  const b=await chromium.launch({headless:true});
  const ctx=await b.newContext({viewport:{width:1280,height:800}});
  const page=await ctx.newPage();
  await page.goto(BASE+'/',{waitUntil:'domcontentloaded',timeout:15000});
  await page.waitForTimeout(2000);
  const res=await page.evaluate(()=>{
    const els=[...document.querySelectorAll('a,button')].filter(e=>e.innerText.includes('Explore Projects')||e.innerText.includes('Post Property FREE')||e.classList.contains('ps-filter-btn'));
    return els.map(e=>{
      const cs=getComputedStyle(e);
      const rect=e.getBoundingClientRect();
      let bg=cs.backgroundColor;
      const bgImg=cs.backgroundImage;
      return {txt:e.innerText.slice(0,30), cls:[...e.classList].join(' '), color:cs.color, bg, bgImg: bgImg.slice(0,80), rect:`${Math.round(rect.x)},${Math.round(rect.y)} ${Math.round(rect.width)}x${Math.round(rect.height)}`, vis: rect.width>5&&rect.height>5&&cs.display!=='none'&&cs.visibility!=='hidden'&&parseFloat(cs.opacity)>0.1};
    });
  });
  console.log(JSON.stringify(res,null,2));
  await b.close();
}
check();
