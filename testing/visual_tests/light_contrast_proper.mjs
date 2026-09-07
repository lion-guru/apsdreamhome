import { chromium } from 'playwright';
const BASE='http://localhost/apsdreamhome';
const PAGES=['/','/properties','/projects','/colonies','/team','/about','/services','/tools-hub','/blog','/contact','/faq','/careers','/buy','/sell','/rent','/invest','/gallery','/news','/rera-lookup','/home-loan-eligibility','/emi-calculator','/stamp-duty-calculator'];
function lum(r,g,b){
  const s=[r,g,b].map(v=>{v/=255; return v<=0.03928?v/12.92:Math.pow((v+0.055)/1.055,2.4)});
  return 0.2126*s[0]+0.7152*s[1]+0.0722*s[2];
}
function ratio(c1,c2){
  const m1=c1.match(/(\d+),\s*(\d+),\s*(\d+)/), m2=c2.match(/(\d+),\s*(\d+),\s*(\d+)/);
  if(!m1||!m2) return 21;
  const l1=lum(+m1[1],+m1[2],+m1[3]), l2=lum(+m2[1],+m2[2],+m2[3]);
  const light=Math.max(l1,l2), dark=Math.min(l1,l2);
  return (light+0.05)/(dark+0.05);
}
async function audit(){
  const b=await chromium.launch({headless:true});
  for(const path of PAGES){
    const ctx=await b.newContext({viewport:{width:1280,height:800}});
    const page=await ctx.newPage();
    const url=BASE+path;
    try{
      const r=await page.goto(url,{waitUntil:'domcontentloaded',timeout:15000});
      if(r.status()!==200){ console.log(`\n=== ${path} => ${r.status()} skip`); await ctx.close(); continue; }
      await page.waitForTimeout(2000);
      const res=await page.evaluate(()=>{
        const els=[...document.querySelectorAll('h1,h2,h3,h4,h5,h6,p,span,a,button,label,li')];
        return els.slice(0,150).map(e=>{
          const cs=getComputedStyle(e);
          const rect=e.getBoundingClientRect();
          if(rect.width<5||rect.height<5||cs.display==='none'||cs.visibility==='hidden'||parseFloat(cs.opacity)<0.1) return null;
          let bg='rgba(0,0,0,0)'; let cur=e;
          for(let i=0;i<5&&cur;i++){ const s=getComputedStyle(cur); if(s.backgroundColor!=='rgba(0, 0, 0, 0)'&&s.backgroundColor!=='transparent'){ bg=s.backgroundColor; break; } cur=cur.parentElement; }
          if(bg==='rgba(0, 0, 0, 0)') bg='rgb(248, 250, 252)';
          let txt=(e.innerText||'').replace(/\s+/g,' ').trim().slice(0,50);
          if(txt.length<4) return null;
          return {tag:e.tagName, cls:[...e.classList].slice(0,2).join('.'), txt, color:cs.color, bg, fs:cs.fontSize, w:Math.round(rect.width)};
        }).filter(Boolean);
      });
      let low=0;
      console.log(`\n=== ${path} (${res.length} texts)`);
      for(const el of res){
        const cr=ratio(el.color, el.bg);
        const need = parseFloat(el.fs) >= 18 ? 3 : 4.5;
        if(cr < need){
          low++;
          if(low<=8) console.log(`  LOW ${cr.toFixed(2)}:1 <${need} ${el.tag}.${el.cls} "${el.txt.slice(0,35)}" color=${el.color} bg=${el.bg} ${el.fs}`);
        }
      }
      if(low===0) console.log('  OK all contrast >=4.5:1');
      else console.log(`  >> ${low} low-contrast texts`);
    }catch(e){ console.log(` ERR ${path}: ${e.message.slice(0,100)}`);}
    await ctx.close();
  }
  await b.close();
}
audit();
