
import { chromium } from 'playwright';
const BASE='http://localhost/apsdreamhome';
const PAGES=['/','/properties','/projects','/colonies','/about','/services','/tools-hub','/contact','/team','/colonies/suryoday-colony'];
async function audit(){
  const b=await chromium.launch({headless:true});
  for(const path of PAGES){
    const ctx=await b.newContext({viewport:{width:1280,height:800}});
    const page=await ctx.newPage();
    const url=BASE+path;
    try{
      await page.goto(url,{waitUntil:'domcontentloaded',timeout:15000});
      await page.waitForTimeout(2000);
      const results=await page.evaluate(()=>{
        const els=[...document.querySelectorAll('h1,h2,h3,h4,p,span,button,a,div.card,section')];
        const issues=[];
        for(const e of els.slice(0,120)){
          const cs=getComputedStyle(e);
          const rect=e.getBoundingClientRect();
          if(rect.width<5||rect.height<5||cs.display==='none'||cs.visibility==='hidden'||parseFloat(cs.opacity)<0.1) continue;
          let bg='rgba(0,0,0,0)';
          let cur=e;
          for(let i=0;i<5&&cur;i++){ const s=getComputedStyle(cur); if(s.backgroundColor!=='rgba(0, 0, 0, 0)' && s.backgroundColor!=='transparent'){ bg=s.backgroundColor; break; } cur=cur.parentElement; }
          const isDark=c=>{
            const m=c.match(/(\d+),\s*(\d+),\s*(\d+)/);
            if(!m) return null;
            const [r,g,b]=[+m[1],+m[2],+m[3]];
            if(r===0&&g===0&&b===0) return null;
            const lum=0.299*r+0.587*g+0.114*b;
            return lum<128;
          };
          const bgDark=isDark(bg), colDark=isDark(cs.color);
          let txt=(e.innerText||'').slice(0,60).replace(/\s+/g,' ').trim();
          if(txt.length<3) continue;
          const invisible=(bg!=='rgba(0, 0, 0, 0)' && bgDark!==null && colDark!==null && ((bgDark&&colDark)||(!bgDark&&!colDark)));
          if(invisible){
            issues.push({tag:e.tagName,cls:[...e.classList].slice(0,2).join('.'),txt:txt.slice(0,40),color:cs.color,bg, x:Math.round(rect.x), y:Math.round(rect.y), w:Math.round(rect.width), h:Math.round(rect.height)});
          }
        }
        return {issues, total:els.length};
      });
      console.log(`\n=== ${path} === ${results.issues.length} invisible of ${results.total} scanned`);
      for(const iss of results.issues.slice(0,10)){
        console.log(`  INV ${iss.tag}.${iss.cls} "${iss.txt}" color=${iss.color} bg=${iss.bg} @${iss.x},${iss.y} ${iss.w}x${iss.h}`);
      }
      if(results.issues.length===0) console.log('  OK no invisible');
    }catch(e){ console.log(`  ERR ${path}: ${e.message.slice(0,120)}`); }
    await ctx.close();
  }
  await b.close();
}
audit();
