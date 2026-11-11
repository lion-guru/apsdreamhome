import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    const root = await cdp.send('DOM.getDocument');
    const node = await cdp.send('DOM.querySelector', {
        nodeId: root.root.nodeId,
        selector: 'input[aria-label="Add email addresses"]'
    });
    console.log('Node:', node);

    const resolved = await cdp.send('DOM.resolveNode', { nodeId: node.nodeId });
    const res = await cdp.send('DOMDebugger.getEventListeners', { objectId: resolved.object.objectId });
    console.log('Input listeners:', res.listeners.map(l => ({ type: l.type, useCapture: l.useCapture })));

    // Also check parent listeners
    const parentNode = await cdp.send('DOM.querySelector', {
        nodeId: root.root.nodeId,
        selector: 'mat-form-field, .mdc-text-field'
    });
    if (parentNode && parentNode.nodeId) {
        const resolvedP = await cdp.send('DOM.resolveNode', { nodeId: parentNode.nodeId });
        const resP = await cdp.send('DOMDebugger.getEventListeners', { objectId: resolvedP.object.objectId });
        console.log('Parent listeners:', resP.listeners.map(l => ({ type: l.type, useCapture: l.useCapture })));
    }

    cdp.close();
}

try {
    await main();
} catch (e) {
    console.error(e);
}
