import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    const root = await cdp.send('DOM.getDocument');

    // 1. material-input
    const matInput = await cdp.send('DOM.querySelector', {
        nodeId: root.root.nodeId,
        selector: 'material-input[inputmode="email"], material-input:has(input[aria-label="Add email addresses"])'
    });
    if (matInput && matInput.nodeId) {
        const resolved = await cdp.send('DOM.resolveNode', { nodeId: matInput.nodeId });
        const res = await cdp.send('DOMDebugger.getEventListeners', { objectId: resolved.object.objectId });
        console.log('material-input listeners:', res.listeners.map(l => ({ type: l.type })));
    }

    // 2. email-list
    const emailList = await cdp.send('DOM.querySelector', {
        nodeId: root.root.nodeId,
        selector: 'email-list'
    });
    if (emailList && emailList.nodeId) {
        const resolved = await cdp.send('DOM.resolveNode', { nodeId: emailList.nodeId });
        const res = await cdp.send('DOMDebugger.getEventListeners', { objectId: resolved.object.objectId });
        console.log('email-list listeners:', res.listeners.map(l => ({ type: l.type })));
    }

    cdp.close();
}

try {
    await main();
} catch (e) {
    console.error(e);
}
