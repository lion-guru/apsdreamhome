import { chromium } from 'playwright';
const BASE='http://localhost/apsdreamhome';
const PAGES=['/','/properties','/tools-hub','/about','/contact','/team','/colonies','/projects'];
function lum(r,g,b){ const s=[r,g,b].map(v=>{v/=255; return v<=0.03928?v/12.92:Math.pow((v+0.055)/1.055,2.4)}); return 0.2126*s[0]+0.7152*s[1]+0.0722*s[2]; }
function ratio(c1,c2){
  const m1=c1.match(/(\d+),\s*(\d+),\s*(\d+)/), m2=c2.match(/(\d+),\s*(\d+),\s*(\d+)/);
  if(!m1||!m2) return 21;
  const l1=lum(+m1[1],+m1[2],+m1[3]), l2=lum(+m2[1],+m2[2],+m2[3]);
  return (Math.max(l1,l2)+0.05)/(Math.min(l1,l2)+0.05);
}
async function audit(){
  const b=await chromium.launch({headless:true});
  for(const path of PAGES){
    const ctx=await b.newContext({viewport:{width:1280,height:800}});
    const page=await ctx.newPage();
    await page.goto(BASE+path,{waitUntil:'domcontentloaded',timeout:15000});
    await page.waitForTimeout(1500);
    const res=await page.evaluate(()=>{
      const els=[...document.querySelectorAll('button, a.btn, .card, .erp-module-card, .ps-card, .colony-card')];
      return els.slice(0,80).map(e=>{
        const cs=getComputedStyle(e);
        const rect=e.getBoundingClientRect();
        if(rect.width<10||rect.height<10||cs.display==='none'||cs.visibility==='hidden'||parseFloat(cs.opacity)<0.1) return null;
        let bg='rgba(0,0,0,0)', cur=e;
        for(let i=0;i<6&&cur;i++){ const s=getComputedStyle(cur); if(s.backgroundColor!=='rgba(0, 0, 0, 0)'&&s.backgroundColor!=='transparent'){ bg=s.backgroundColor; break; } cur=cur.parentElement; }
        if(bg==='rgba(0, 0, 0, 0)') bg='rgb(255,255,255)';
        let txt=(e.innerText||'').replace(/\s+/g,' ').trim().slice(0,40);
        if(!txt) txt=(e.textContent||'').replace(/\s+/g,' ').trim().slice(0,40);
        if(!txt) return null;
        return {tag:e.tagName, cls:[...e.classList].slice(0,3).join('.'), txt, color:cs.color, bg, fs:cs.fontSize, bw:Math.round(rect.width), bh:Math.round(rect.height)};
      }).filter(Boolean);
    });
    console.log(`\n=== ${path} (${res.length} btn/cards)`);
    let bad=0;
    for(const el of res){
      const cr=ratio(el.color, el.bg);
      const need=parseFloat(el.fs)>=18?3:4.5;
      if(cr < 2.5){
        bad++;
        if(bad<=10) console.log(`  BAD ${cr.toFixed(2)}:1 ${el.tag}.${el.cls} "${el.txt}" color=${el.color} bg=${el.bg} ${el.fs} ${el.bw}x${el.bh}`);
      }
    }
    if(bad===0) console.log('  OK no invisible btn/card');
    else console.log(`  >> ${bad} invisible/bad btn/cards`);
    await ctx.close();
  }
  await b.close();
}
audit();
