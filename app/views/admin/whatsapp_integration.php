<?php

/**
 * WhatsApp Integration Settings
 * Configure WhatsApp Business API for chatbot
 */
$page_title = $page_title ?? 'WhatsApp Integration';
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <h2 class="mb-0"><i class="fab fa-whatsapp me-2 text-success"></i>WhatsApp Integration</h2>
        <a href="<?= BASE_URL ?>/admin/ai" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back to AI</a>
    </div>

    <div class="row g-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom"><h6 class="mb-0">Connection Status</h6></div>
                <div class="card-body">
                    <?php
                    $whatsappConnected = false;
                    try {
                        $wa = new \App\Services\Communication\WhatsAppWebService();
                        $whatsappConnected = $wa->isConnected();
                    } catch (\Exception $e) {}
                    ?>
                    <div class="d-flex align-items-center gap-3 mb-4">
                        <div class="flex-shrink-0">
                            <span class="badge <?= $whatsappConnected ? 'bg-success' : 'bg-danger' ?> rounded-pill p-3">
                                <i class="fas <?= $whatsappConnected ? 'fa-check-circle' : 'fa-times-circle' ?> fa-2x"></i>
                            </span>
                        </div>
                        <div>
                            <h5 class="mb-0"><?= $whatsappConnected ? 'Connected' : 'Disconnected' ?></h5>
                            <small class="text-muted"><?= $whatsappConnected ? 'WhatsApp service is running' : 'WhatsApp service is not running' ?></small>
                        </div>
                    </div>
                    <div class="row text-center g-2">
                        <div class="col-4">
                            <div class="p-2 bg-light rounded">
                                <i class="fas fa-server text-primary mb-1"></i><br>
                                <small class="text-muted">Service</small><br>
                                <small class="fw-bold">Node.js:3001</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-2 bg-light rounded">
                                <i class="fas fa-plug text-info mb-1"></i><br>
                                <small class="text-muted">WebSocket</small><br>
                                <small class="fw-bold">Port 3001</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-2 bg-light rounded">
                                <i class="fas fa-qrcode text-success mb-1"></i><br>
                                <small class="text-muted">QR Code</small><br>
                                <a href="<?= BASE_URL ?>/admin/whatsapp-web" class="fw-bold text-decoration-none">Scan</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom"><h6 class="mb-0">API Configuration</h6></div>
                <div class="card-body">
                    <form id="whatsappConfigForm">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Service URL</label>
                            <input type="text" class="form-control form-control-sm" value="<?= defined('WHATSAPP_SERVICE_URL') ? WHATSAPP_SERVICE_URL : 'http://localhost:3001' ?>" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Phone Number ID</label>
                            <input type="text" class="form-control form-control-sm" placeholder="e.g. 1234567890" value="">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Business Account ID</label>
                            <input type="text" class="form-control form-control-sm" placeholder="WABA ID" value="">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Access Token</label>
                            <input type="password" class="form-control form-control-sm" placeholder="API token">
                        </div>
                        <button type="submit" class="btn btn-success btn-sm"><i class="fas fa-save me-1"></i>Save Configuration</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mt-2">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom"><h6 class="mb-0">Quick Actions</h6></div>
                <div class="card-body">
                    <a href="<?= BASE_URL ?>/admin/whatsapp-web" class="btn btn-outline-success btn-block mb-2">
                        <i class="fas fa-qrcode me-1"></i>Scan QR Code
                    </a>
                    <button class="btn btn-outline-primary btn-block mb-2" onclick="testConnection()">
                        <i class="fas fa-plug me-1"></i>Test Connection
                    </button>
                    <button class="btn btn-outline-info btn-block" onclick="sendTestMessage()">
                        <i class="fas fa-paper-plane me-1"></i>Send Test Message
                    </button>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom"><h6 class="mb-0">Message Templates</h6></div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="d-flex justify-content-between align-items-center mb-2">
                            <span class="small">Welcome Message</span>
                            <span class="badge bg-success">Active</span>
                        </li>
                        <li class="d-flex justify-content-between align-items-center mb-2">
                            <span class="small">EMI Reminder</span>
                            <span class="badge bg-success">Active</span>
                        </li>
                        <li class="d-flex justify-content-between align-items-center mb-2">
                            <span class="small">Booking Confirmation</span>
                            <span class="badge bg-success">Active</span>
                        </li>
                        <li class="d-flex justify-content-between align-items-center">
                            <span class="small">Follow-up</span>
                            <span class="badge bg-secondary">Draft</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom"><h6 class="mb-0">Recent Messages</h6></div>
                <div class="card-body text-center py-4 text-muted">
                    <i class="fab fa-whatsapp fa-3x mb-2" style="opacity:0.15"></i>
                    <p class="mb-0 small">No recent messages.<br>Connect WhatsApp and send a test message.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
async function testConnection() {
    try {
        const res = await fetch('<?= BASE_URL ?>/admin/ai-calling/health-check');
        const data = await res.json();
        const status = data.services?.whatsapp?.status || 'unknown';
        alert('WhatsApp Status: ' + (data.services?.whatsapp?.message || status));
    } catch(e) {
        alert('Could not connect to health check endpoint.');
    }
}

function sendTestMessage() {
    const phone = prompt('Enter phone number (with country code):');
    if (!phone) return;
    alert('Test message feature requires WhatsApp QR connection first. Visit the QR scan page.');
}
</script>
