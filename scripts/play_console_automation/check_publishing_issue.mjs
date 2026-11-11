import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    // Click "View 1 issue"
    console.log('Clicking "View 1 issue"...');
    const clickIssue = await cdp.eval(`(() => {
        const btns = Array.from(document.querySelectorAll('button, a')).filter(b => (b.innerText || '').includes('View 1 issue') || (b.innerText || '').includes('issue'));
        if (btns.length > 0) {
            btns[0].scrollIntoView({ block: 'center' });
            btns[0].click();
            return { clicked: true, text: btns[0].innerText.trim() };
        }
        return { notFound: true };
    })()`);
    console.log('Click issue result:', clickIssue);
    await cdp.wait(4000);
    await cdp.screenshot('publishing_issue_detail.png');

    // Inspect issue details
    const issueText = await cdp.eval(`(() => {
        const dialog = document.querySelector('material-dialog, [role="dialog"], .modal, [role="alert"]');
        return {
            hasDialog: !!dialog,
            text: dialog ? dialog.innerText : document.body.innerText.substring(0, 1500)
        };
    })()`);
    console.log('Issue Details:\n', issueText.text);

    cdp.close();
}

main().catch(console.error);
