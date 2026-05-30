const { chromium } = require('playwright');

(async () => {
    const viewports = [
        { name: 'desktop_1280', width: 1280, height: 800 },
        { name: 'tablet_1024', width: 1024, height: 768 },
        { name: 'tablet_768', width: 768, height: 1024 },
        { name: 'mobile_390', width: 390, height: 844 },
    ];

    for (const vp of viewports) {
        const browser = await chromium.launch({ headless: true });
        console.log(`\n===== ${vp.name} (${vp.width}x${vp.height}) =====`);
        const ctx = await browser.newContext({ viewport: vp, deviceScaleFactor: 1 });
        const page = await ctx.newPage();
        await page.goto('http://localhost/apsdreamhome/', { waitUntil: 'load', timeout: 30000 });
        await page.waitForTimeout(2000);

        // Screenshots
        await page.screenshot({ path: `C:\\xampp\\htdocs\\apsdreamhome\\testing\\visual_tests\\${vp.name}.png`, fullPage: false });

        // Check header
        const info = await page.evaluate((vpWidth) => {
            const h = document.querySelector('header');
            if (!h) return { error: 'no header' };
            const hs = getComputedStyle(h);

            // Check all nav links and dropdowns for overflow
            const items = h.querySelectorAll('.nav-item, .dropdown-menu');
            let overflowItems = [];
            items.forEach(item => {
                const rect = item.getBoundingClientRect();
                if (rect.right > vpWidth || rect.left < 0) {
                    overflowItems.push({
                        tag: item.tagName,
                        text: (item.textContent || '').substring(0, 30),
                        right: Math.round(rect.right),
                        left: Math.round(rect.left),
                        width: Math.round(rect.width)
                    });
                }
            });

            // Check all dropdown menus position
            const dropdowns = h.querySelectorAll('.dropdown-menu');
            let dropdownPositions = [];
            dropdowns.forEach(d => {
                const rect = d.getBoundingClientRect();
                dropdownPositions.push({
                    text: (d.textContent || '').substring(0, 30),
                    right: Math.round(rect.right),
                    left: Math.round(rect.left),
                    width: Math.round(rect.width)
                });
            });

            return {
                headerHeight: hs.height,
                headerWidth: h.offsetWidth,
                viewportW: vpWidth,
                navItemsCount: h.querySelectorAll('.nav-item').length,
                overflowItems,
                dropdownPositions,
                hasToggler: !!h.querySelector('.navbar-toggler'),
                togglerVisible: h.querySelector('.navbar-toggler') ? getComputedStyle(h.querySelector('.navbar-toggler')).display !== 'none' : false
            };
        }, vp.width);

        console.log('  Header info:', JSON.stringify(info, null, 2));

        await browser.close();
    }
})();
