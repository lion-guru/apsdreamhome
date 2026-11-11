async function main() {
    const res = await fetch('http://127.0.0.1:9222/json');
    const tabs = await res.json();
    console.log(tabs.map(t => ({ id: t.id, title: t.title, url: t.url, ws: t.webSocketDebuggerUrl })));
}
main().catch(console.error);
