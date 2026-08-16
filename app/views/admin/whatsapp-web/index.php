<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0"><i class="fab fa-whatsapp text-success me-2"></i> WhatsApp Web</h3>
        <div>
            <a href="<?= WHATSAPP_SERVICE_URL ?>" target="_blank" class="btn btn-success">
                <i class="fas fa-qrcode me-1"></i> Open QR Scanner
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card aps-cp-card">
                <div class="card-body text-center py-5">
                    <iframe src="<?= WHATSAPP_SERVICE_URL ?>" width="100%" height="600" class="style-3619" title="WhatsApp Web QR"></iframe>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card mb-3">
                <div class="card-header bg-white"><h6 class="mb-0 fw-bold">Status</h6></div>
                <div class="card-body aps-cp-card-body" id="wa-status">
                    <p class="text-muted mb-0">Loading...</p>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header bg-white"><h6 class="mb-0 fw-bold">Quick Actions</h6></div>
                <div class="card-body aps-cp-card-body">
                    <button class="btn btn-outline-success w-100 mb-2" onclick="checkStatus()">
                        <i class="fas fa-sync me-1"></i> Check Status
                    </button>
                    <button class="btn btn-outline-warning w-100 mb-2" onclick="reconnectWA()">
                        <i class="fas fa-redo me-1"></i> New QR
                    </button>
                    <button class="btn btn-outline-danger w-100" onclick="logoutWA()">
                        <i class="fas fa-sign-out-alt me-1"></i> Logout
                    </button>
                </div>
            </div>

            <div class="card aps-cp-card">
                <div class="card-header bg-white"><h6 class="mb-0 fw-bold">AI Agent Config</h6></div>
                <div class="card-body aps-cp-card-body">
                    <p class="small text-muted">WhatsApp Web se connected hone ke baad AI Agent automatically:</p>
                    <ul class="small">
                        <li>Property inquiries ka reply karega</li>
                        <li>Booking confirmations bhejega</li>
                        <li>Payment reminders bhejega</li>
                        <li>Customer support handle karega</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const WA_SERVICE_URL = '<?= WHATSAPP_SERVICE_URL ?>';

async function checkStatus() {
    const el = document.getElementById('wa-status');
    el.innerHTML = '<p class="text-muted mb-0">Checking...</p>';
    try {
        const r = await fetch(WA_SERVICE_URL + '/api/status');
        const d = await r.json();
        if (d.connected) {
            el.innerHTML = `<div class="alert alert-success mb-0">âœ… Connected to ${d.number}<br><small>${d.pushname || ''}</small></div>`;
        } else if (d.qr_generated) {
            el.innerHTML = `<div class="alert alert-warning mb-0">📱 QR ready - Scan with WhatsApp</div>`;
        } else {
            el.innerHTML = `<div class="alert alert-secondary mb-0">â�³ Initializing... Refresh in 10s</div>`;
        }
    } catch(e) {
        el.innerHTML = `<div class="alert alert-danger mb-0">â�Œ Service not running<br><small>Start: node whatsapp-service/server.js</small></div>`;
    }
}

async function reconnectWA() {
    await fetch(WA_SERVICE_URL + '/api/reconnect', { method: 'POST' });
    document.getElementById('wa-status').innerHTML = '<p class="text-muted mb-0">ðŸ”„ Reconnecting... QR will appear soon</p>';
    setTimeout(checkStatus, 5000);
}

async function logoutWA() {
    if (confirm('Logout from WhatsApp Web?')) {
        await fetch(WA_SERVICE_URL + '/api/logout', { method: 'POST' });
        document.getElementById('wa-status').innerHTML = '<p class="text-muted mb-0">ðŸšª Logged out</p>';
    }
}

checkStatus();
setInterval(checkStatus, 15000);
</script>
