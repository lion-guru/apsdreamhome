import { chromium } from 'playwright';
const BASE='http://localhost/apsdreamhome';
async function audit(){
  const b=await chromium.launch({headless:true});
  for(const vp of [{w:1280,h:800,n:'desktop'},{w:390,h:844,n:'mobile'}]){
    console.log(`\n===== ${vp.n} ${vp.w}x${vp.h} =====`);
    const ctx=await b.newContext({viewport:{width:vp.w,height:vp.h}});
    const page=await ctx.newPage();
    const errs=[];
    page.on('console', m=>{ if(m.type()==='error') errs.push(m.text()); });
    await page.goto(BASE+'/',{waitUntil:'domcontentloaded',timeout:30000});
    await page.waitForTimeout(2500);
    // check all h1/h2/h3/p/button/a for visibility and contrast
    const results=await page.evaluate(()=>{
      const els=[...document.querySelectorAll('h1,h2,h3,h4,h5,h6,p,button,a.btn,span.badge')];
      return els.slice(0,80).map(e=>{
        const cs=getComputedStyle(e);
        const rect=e.getBoundingClientRect();
        const visible=rect.width>5 && rect.height>5 && cs.display!=='none' && cs.visibility!=='hidden' && parseFloat(cs.opacity)>0.1;
        let bg='transparent';
        let cur=e;
        for(let i=0;i<4&&cur;i++){ const s=getComputedStyle(cur); if(s.backgroundColor!=='rgba(0, 0, 0, 0)' && s.backgroundColor!=='transparent'){ bg=s.backgroundColor; break; } cur=cur.parentElement; }
        const isDark=c=>{
          const m=c.match(/(\d+),\s*(\d+),\s*(\d+)/);
          if(!m) return false;
          const [r,g,b]=[+m[1],+m[2],+m[3]];
          return (0.299*r+0.587*g+0.114*b)<128;
        };
        const bgDark=isDark(bg), colDark=isDark(cs.color);
        const invisible=(bg!=='transparent' && ((bgDark&&colDark)||(!bgDark&&!colDark)));
        const txt=e.innerText.slice(0,50).replace(/\s+/g,' ').trim();
        return {tag:e.tagName, cls:[...e.classList].slice(0,3).join('.'), txt, visible, invisible, color:cs.color, bg, rect:`${Math.round(rect.width)}x${Math.round(rect.height)}`};
      });
    });
    let invisibleCount=0;
    results.forEach(r=>{
      if(r.invisible) invisibleCount++;
      const flag=r.invisible?'INVISIBLE':r.visible?'OK':'HIDDEN';
      if(r.invisible || !r.visible) console.log(`  ${flag} ${r.tag}.${r.cls} "${r.txt.slice(0,40)}" color=${r.color} bg=${r.bg} ${r.rect}`);
    });
    console.log(`  Summary: ${results.length} checked, ${invisibleCount} invisible, ${results.filter(r=>!r.visible).length} hidden`);
    console.log(`  Console errors: ${errs.length}`); errs.slice(0,3).forEach(e=>console.log('    ERR',e.slice(0,120)));
    await page.screenshot({path:`testing/visual_tests/screenshots/home-deep-${vp.n}.png`, fullPage:true}).catch(()=>{});
    console.log(`  Screenshot: home-deep-${vp.n}.png`);
    await ctx.close();
  }
  await b.close();
}
audit().catch(e=>{console.error(e);process.exit(1)});
