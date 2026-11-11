import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    const info = await cdp.eval(`(() => {
        const fileInput = document.querySelector('input[type="file"]');
        if (!fileInput) return { error: 'No file input found' };

        let curr = fileInput;
        const parents = [];
        for (let i = 0; i < 6 && curr; i++) {
            parents.push({
                tag: curr.tagName,
                id: curr.id,
                className: curr.className,
                textSnippet: (curr.innerText || '').slice(0, 100)
            });
            curr = curr.parentElement;
        }

        const uploadEls = Array.from(document.querySelectorAll('*')).filter(el => {
            return (el.innerText || '').includes('Upload') && el.children.length === 0;
        }).map(el => ({
            tag: el.tagName,
            text: el.innerText.trim(),
            parentTag: el.parentElement?.tagName
        }));

        return { fileInputParent: parents, uploadEls };
    })()`);

    console.log('File input hierarchy:', JSON.stringify(info, null, 2));
    cdp.close();
}

main().catch(console.error);
