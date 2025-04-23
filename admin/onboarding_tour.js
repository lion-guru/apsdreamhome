// Simple onboarding tour for dashboard (uses Bootstrap popovers)
document.addEventListener('DOMContentLoaded',function(){
  const steps=[
    {el:'#main-navbar',title:'Navigation',content:'यहाँ से आप सभी modules एक्सेस कर सकते हैं (Dashboard, App Store, Analytics, आदि)।'},
    {el:'#quick-stats',title:'Quick Stats',content:'यह section आपको platform के मुख्य metrics दिखाता है।'},
    {el:'#feature-announcements',title:'New Features',content:'नए features और updates यहाँ highlight होते हैं।'},
    {el:'#user-profile',title:'Profile',content:'यहाँ से आप अपना profile और settings manage कर सकते हैं।'}
  ];
  let i=0;
  function showStep(idx){
    if(idx>=steps.length) return;
    const s=steps[idx];
    const el=document.querySelector(s.el);
    if(!el) return showStep(idx+1);
    bootstrap.Popover.getOrCreateInstance(el,{title:s.title,content:s.content,placement:'bottom',trigger:'manual'}).show();
    el.classList.add('tour-highlight');
    setTimeout(()=>{
      bootstrap.Popover.getOrCreateInstance(el).hide();
      el.classList.remove('tour-highlight');
      showStep(idx+1);
    },3000);
  }
  if(window.location.search.includes('tour=1')) showStep(0);
});
