<div class="container py-4">
    <div class="text-center mb-5">
        <h4 class="fw-bold"><i class="fas fa-mobile-alt me-2"></i><?= ($page_title ?? 'PWA Features') ?></h4>
        <p class="text-muted">Install APS Dream Home as a Progressive Web App</p>
    </div>

    <div class="row g-4 justify-content-center">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm text-center h-100">
                <div class="card-body py-5">
                    <i class="fas fa-download fa-3x text-primary mb-3"></i>
                    <h5>Install App</h5>
                    <p class="small text-muted">Install on your device for a native-like experience</p>
                    <button id="installPwaBtn" class="btn btn-primary" style="display:none"><i class="fas fa-download me-1"></i>Install</button>
                    <span class="badge bg-secondary">Already installed</span>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm text-center h-100">
                <div class="card-body py-5">
                    <i class="fas fa-wifi-slash fa-3x text-warning mb-3"></i>
                    <h5>Offline Support</h5>
                    <p class="small text-muted">Browse cached properties even without internet</p>
                    <i class="fas fa-check-circle text-success"></i> <small>Enabled</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm text-center h-100">
                <div class="card-body py-5">
                    <i class="fas fa-bell fa-3x text-info mb-3"></i>
                    <h5>Push Notifications</h5>
                    <p class="small text-muted">Get instant alerts for new properties and offers</p>
                    <button class="btn btn-outline-info btn-sm" id="enablePushBtn"><i class="fas fa-bell me-1"></i>Enable</button>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mt-4">
        <div class="card-header bg-white py-3"><h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>App Info</h6></div>
        <div class="card-body aps-cp-card-body">
            <div class="table-responsive"><table class="table table-sm mb-0 table-responsive">
                <tr><td class="text-muted" style="width:200px">App Name</td><td><?= (defined('APP_NAME') ? APP_NAME : 'APS Dream Home') ?></td></tr>
                <tr><td class="text-muted">Version</td><td>1.0.0</td></tr>
                <tr><td class="text-muted">Cache</td><td><span class="badge bg-success">Active</span></td></tr>
                <tr><td class="text-muted">Service Worker</td><td><span class="badge bg-success">Registered</span></td></tr>
            </table></div>
        </div>
    </div>
</div>

<script>
if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('<?= ($base ?? BASE_URL) ?>pwa/service-worker');
}
let deferredPrompt;
window.addEventListener('beforeinstallprompt', e => {
    e.preventDefault();
    deferredPrompt = e;
    document.getElementById('installPwaBtn').style.display = 'inline-block';
});
document.getElementById('installPwaBtn')?.addEventListener('click', async () => {
    if (deferredPrompt) { deferredPrompt.prompt(); deferredPrompt = null; }
});
document.getElementById('enablePushBtn')?.addEventListener('click', async () => {
    try {
        const sub = await navigator.serviceWorker.ready.then(r => r.pushManager.subscribe({userVisibleOnly:true,applicationServerKey:null}));
        await fetch('<?= ($base ?? BASE_URL) ?>pwa/subscribe', {method:'POST',body:JSON.stringify(sub),headers:{'Content-Type':'application/json'}});
        alert('Push notifications enabled!');
    } catch(e) { alert('Push not supported or permission denied.'); }
});
</script>
