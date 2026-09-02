import { chromium } from 'playwright';
const BASE='http://localhost/apsdreamhome';
async function run(){
  const b=await chromium.launch({headless:true});
  const ctx=await b.newContext({viewport:{width:1280,height:800}});
  const p=await ctx.newPage();
  await p.goto(BASE+'/',{waitUntil:'domcontentloaded',timeout:30000});
  await p.waitForTimeout(2000);
  // scroll to 3D section
  await p.evaluate(()=>document.querySelector('.bg-dark')?.scrollIntoView());
  await p.waitForTimeout(1500);
  const text=await p.evaluate(()=>document.querySelector('[id="3d-tour-title"]')?.innerText||'no h2');
  const visible=await p.evaluate(()=>{
    const h=document.querySelector('[id="3d-tour-title"]');
    if(!h) return 'no h2';
    const s=getComputedStyle(h);
    const r=h.getBoundingClientRect();
    return `visible=${r.width>0} opacity=${s.opacity} color=${s.color} text="${h.innerText.slice(0,40)}"`;
  });
  console.log('3D h2:',text,'=>',visible);
  // check stats after scroll
  await p.evaluate(()=>window.scrollTo(0,800));
  await p.waitForTimeout(1500);
  const stats=await p.evaluate(()=>[...document.querySelectorAll('.stat-number')].map(e=>e.innerText).join(', '));
  console.log('stats after scroll:',stats);
  await p.screenshot({path:'testing/visual_tests/screenshots/home-scroll-3d.png', fullPage:false});
  console.log('screenshot: home-scroll-3d.png');
  await b.close();
}
run().catch(e=>{console.error(e);process.exit(1)});
