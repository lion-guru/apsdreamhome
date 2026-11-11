import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    const info = await cdp.eval(`(() => {
        const input = document.querySelector('input[aria-label="Add email addresses"]');
        if (!input) return { error: 'No input' };

        const ancestors = [];
        let cur = input.parentElement;
        while (cur && cur !== document.body) {
            ancestors.push({
                tag: cur.tagName,
                classes: cur.className,
                isForm: cur.tagName === 'FORM'
            });
            cur = cur.parentElement;
        }

        return {
            form: input.form ? { tag: input.form.tagName, id: input.form.id, action: input.form.action } : null,
            ancestors
        };
    })()`);

    console.log('Ancestor info:', JSON.stringify(info, null, 2));
    cdp.close();
}

try {
    await main();
} catch (e) {
    console.error(e);
}
