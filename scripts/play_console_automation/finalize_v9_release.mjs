import { CDPClient } from 'file:///C:/xampp/htdocs/apsdreamhome/scripts/play_console_automation/cdp_helper.mjs';
const cdp = new CDPClient();
await cdp.init();
console.log('URL:', await cdp.eval('window.location.href'));

const releaseName = '1.2.10 (9) - AI Dashboard & Photo Leads';
await cdp.eval(`(() => {
  const inputs = Array.from(document.querySelectorAll('input'));
  const nameInput = inputs.find(i => i.placeholder?.includes('Release name') || i.getAttribute('aria-label')?.includes('Release name') || i.name?.includes('releaseName'));
  if (nameInput) {
    nameInput.focus(); nameInput.select();
    document.execCommand('insertText', false, ${JSON.stringify(releaseName)});
    nameInput.dispatchEvent(new Event('input', { bubbles: true }));
    nameInput.dispatchEvent(new Event('change', { bubbles: true }));
  }
  return !!nameInput;
})()`);
console.log('Release name set:', releaseName);

const notes = '<en-US>APS Dream Home v1.2.10 (Build 9):\n- AI Dashboard: next best actions, churn alerts, priority leads, deal predictions.\n- Photo Lead: create a lead from a property photo with AI extraction; confirm site visits with AI photos.\n- Home recommendations with confidence scores; Smart Search; chatbot voice TTS.\n- Performance: cached dashboards; fixed payroll, training, voice pages.</en-US>';
await cdp.eval(`(() => {
  const textareas = Array.from(document.querySelectorAll('textarea'));
  const noteArea = textareas.find(t => t.placeholder?.includes('Release notes') || t.getAttribute('aria-label')?.includes('Release notes') || t.offsetParent !== null);
  if (noteArea) {
    noteArea.focus(); noteArea.select();
    document.execCommand('insertText', false, ${JSON.stringify(notes)});
    noteArea.dispatchEvent(new Event('input', { bubbles: true }));
    noteArea.dispatchEvent(new Event('change', { bubbles: true }));
  }
  return !!noteArea;
})()`);
console.log('Release notes set with en-US tags.');
await cdp.wait(4000);

const nextResult = await cdp.eval(`(() => {
  const btns = Array.from(document.querySelectorAll('button'));
  const nextBtn = btns.find(b => !b.disabled && b.innerText.trim() === 'Next' && b.offsetParent !== null);
  if (nextBtn) { nextBtn.scrollIntoView({ block: 'center' }); nextBtn.click(); return { clicked: true }; }
  const errs = Array.from(document.querySelectorAll('.error-message, mat-error, [role="alert"]')).map(e => e.innerText.trim());
  return { notFound: true, errors: errs };
})()`);
console.log('Next:', nextResult);
await cdp.wait(8000);

const rolloutCheck = await cdp.eval(`(() => {
  const btns = Array.from(document.querySelectorAll('button'));
  const saveOrRollout = btns.find(b => !b.disabled && b.offsetParent !== null && (
    b.innerText.includes('Save and publish') || b.innerText.includes('Start rollout to Internal testing') || b.innerText.includes('Start rollout')
  ));
  const errors = Array.from(document.querySelectorAll('.error-message, mat-error, [role="alert"]')).map(e => e.innerText.trim());
  return { hasSaveOrRollout: !!saveOrRollout, buttonText: saveOrRollout ? saveOrRollout.innerText.trim() : null, errors, url: window.location.href };
})()`);
console.log('Rollout check:', rolloutCheck);
await cdp.screenshot('v9_finalize_review.png');

if (rolloutCheck.hasSaveOrRollout) {
  await cdp.eval(`(() => {
    const btns = Array.from(document.querySelectorAll('button'));
    const b = btns.find(x => !x.disabled && x.offsetParent !== null && (
      x.innerText.includes('Save and publish') || x.innerText.includes('Start rollout to Internal testing') || x.innerText.includes('Start rollout')
    ));
    if (b) { b.scrollIntoView({ block: 'center' }); b.click(); }
  })()`);
  await cdp.wait(5000);
  await cdp.eval(`(() => {
    const modalBtns = Array.from(document.querySelectorAll('mat-dialog-container button, [role="dialog"] button'));
    const confirmBtn = modalBtns.find(b => !b.disabled && (
      b.innerText.includes('Save and publish') || b.innerText.includes('Rollout') || b.innerText.includes('Confirm') || b.innerText.includes('Save')
    ));
    if (confirmBtn) confirmBtn.click();
  })()`);
  await cdp.wait(6000);
  await cdp.screenshot('v9_published_status.png');
  const final = await cdp.eval(`(() => {
    const body = (document.body.innerText||'').replace(/\\s+/g,' ').slice(0,1000);
    return { url: window.location.href, body };
  })()`);
  console.log('FINAL URL:', final.url);
  console.log('FINAL BODY:', final.body);
}
cdp.close();
