<?php
/**
 * WhatsApp Integration View (Admin)
 * Data: $page_title
 */
$page_title = $page_title ?? 'WhatsApp Integration';
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="fab fa-whatsapp me-2 text-success"></i><?= htmlspecialchars($page_title ?? '') ?></h2>
    </div>

    <div class="alert alert-info">
        <i class="fas fa-info-circle me-2"></i>
        <strong>WhatsApp Business API Integration</strong> - Configure and manage WhatsApp Business API for customer communication.
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="card aps-cp-card h-100">
                <div class="card-header aps-cp-card-header"><i class="fas fa-qrcode me-2"></i>WhatsApp Web QR</div>
                <div class="card-body text-center py-4">
                    <div class="mb-3">
                        <i class="fab fa-whatsapp fa-4x text-success mb-3"></i>
                        <h5>Scan QR Code</h5>
                        <p class="text-muted">Link your WhatsApp Business account by scanning the QR code.</p>
                    </div>
                    <div class="border rounded p-4 bg-light" id="qrContainer">
                        <div class="text-muted">QR Code will appear here</div>
                    </div>
                    <button class="btn btn-primary mt-3" id="refreshQR"><i class="fas fa-sync me-1"></i> Refresh QR</button>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card aps-cp-card h-100">
                <div class="card-header aps-cp-card-header"><i class="fas fa-cog me-2"></i>Configuration</div>
                <div class="card-body">
                    <form method="POST" action="<?= BASE_URL ?>/admin/whatsapp/save-config">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                        
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Phone Number ID</label>
                            <input type="text" class="form-control" name="phone_number_id" placeholder="Your WhatsApp Business Phone Number ID">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Access Token</label>
                            <input type="password" class="form-control" name="access_token" placeholder="WhatsApp Business API Access Token">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Webhook Verify Token</label>
                            <input type="text" class="form-control" name="webhook_verify_token" placeholder="Webhook verification token">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Webhook URL</label>
                            <input type="text" class="form-control" value="<?= BASE_URL ?>/webhook/whatsapp" readonly>
                            <div class="form-text">Configure this URL in your Meta Developer Console</div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Save Configuration</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-md-6">
            <div class="card aps-cp-card h-100">
                <div class="card-header aps-cp-card-header"><i class="fas fa-comments me-2"></i>Message Templates</div>
                <div class="card-body">
                    <p class="text-muted">Manage WhatsApp message templates for notifications and marketing.</p>
                    <button class="btn btn-outline-primary" disabled><i class="fas fa-plus me-1"></i> Add Template</button>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card aps-cp-card h-100">
                <div class="card-header aps-cp-card-header"><i class="fas fa-chart-line me-2"></i>Analytics</div>
                <div class="card-body">
                    <p class="text-muted">Track message delivery, read rates, and engagement.</p>
                    <button class="btn btn-outline-info" disabled><i class="fas fa-chart-bar me-1"></i> View Analytics</button>
                </div>
            </div>
        </div>
    </div>
</div>