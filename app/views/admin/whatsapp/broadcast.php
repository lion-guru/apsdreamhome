<div class="container-fluid py-4">
    <h1 class="h3 mb-4"><i class="fab fa-whatsapp me-2 text-success"></i>WhatsApp Broadcast</h1>

    <?php if (!empty($message)): ?>
        <div class="alert alert-<?= $message_type ?? 'info' ?>"><?= htmlspecialchars($message ?? '') ?></div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white"><h5 class="mb-0">Send Broadcast</h5></div>
                <div class="card-body aps-cp-card-body">
                    <form method="POST" action="<?= BASE_URL ?>/admin/whatsapp-broadcast">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        <div class="mb-3">
                            <label class="form-label">Audience <span class="text-danger">*</span></label>
                            <select name="audience" class="form-select" required>
                                <option value="all_customers">All users</option>
                                <option value="all_leads">All Leads</option>
                                <option value="all_associates">All users</option>
                                <option value="recent_inquiries">Recent Inquiries (30 days)</option>
                                <option value="custom">Custom Phone Numbers</option>
                            </select>
                        </div>
                        <div class="mb-3" id="customPhones" class="style-2248">
                            <label class="form-label">Phone Numbers (one per line) <span class="text-danger">*</span></label>
                            <textarea name="custom_phones" class="form-control" rows="4" placeholder="+919XXXXXXXXX&#10;+919XXXXXXXXX"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Message Template</label>
                            <select name="template_name" class="form-select">
                                <option value="">-- Plain Message --</option>
                                <?php if (!empty($templates)): foreach ($templates as $t): ?>
                                    <option value="<?= htmlspecialchars($t['template_name'] ?? '') ?>"><?= htmlspecialchars($t['template_name'] ?? '') ?> (<?= htmlspecialchars($t['language'] ?? 'en') ?>)</option>
                                <?php endforeach; endif; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Message <span class="text-danger">*</span></label>
                            <textarea name="message" class="form-control" rows="5" required placeholder="Type your message here..."></textarea>
                        </div>
                        <button type="submit" class="btn btn-success"><i class="fab fa-whatsapp me-1"></i>Send Broadcast</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm bg-light">
                <div class="card-body aps-cp-card-body">
                    <h6><i class="fas fa-info-circle me-1"></i>Broadcast Info</h6>
                    <ul class="small text-muted ps-3">
                        <li class="mb-1">Messages are sent via WhatsApp Business API</li>
                        <li class="mb-1">Template messages have higher deliverability</li>
                        <li class="mb-1">Plain messages require opt-in from recipients</li>
                        <li class="mb-1">Avoid sending more than 250 messages/day</li>
                    </ul>
                    <hr>
                    <?php if (isset($stats)): ?>
                        <p class="small mb-1">Total users: <strong><?= $stats['users'] ?? 0 ?></strong></p>
                        <p class="small mb-1">Total Leads: <strong><?= $stats['leads'] ?? 0 ?></strong></p>
                        <p class="small mb-0">Total users: <strong><?= $stats['users'] ?? 0 ?></strong></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.querySelector('[name="audience"]')?.addEventListener('change', function() {
    document.getElementById('customPhones').style.display = this.value === 'custom' ? 'block' : 'none';
});
</script>
