<?php
$page_title = $page_title ?? 'Bulk WhatsApp';
$current_page = 'leads';
$leads = $leads ?? [];
$whatsappLinks = $whatsappLinks ?? [];
$sent = $sent ?? 0;
$message = $message ?? '';
?>

<div class="container-fluid px-4 py-3">
    <a href="<?= BASE_URL ?>/associate/leads" class="text-decoration-none mb-3 d-inline-block">
        <i class="fas fa-arrow-left me-1"></i> Back to Leads
    </a>

    <div class="row">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="fab fa-whatsapp me-2" style="color:#25d366;"></i>Bulk WhatsApp Messages</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($leads)): ?>
                        <p class="text-muted">Select leads from the <a href="<?= BASE_URL ?>/associate/leads">leads list</a> to send bulk messages.</p>
                        <div class="alert alert-info border-0" style="background:#f0fdf4;">
                            <h6><i class="fas fa-info-circle me-2"></i>How to use</h6>
                            <ol class="mb-0" style="font-size:0.85rem;">
                                <li>Go to <a href="<?= BASE_URL ?>/associate/leads">My Leads</a></li>
                                <li>Check the boxes next to leads you want to message</li>
                                <li>Click "Bulk WhatsApp" button</li>
                                <li>Compose your message and send</li>
                            </ol>
                        </div>
                    <?php else: ?>
                        <div class="mb-3">
                            <strong><?= count($leads) ?> lead(s) selected</strong>
                            <div class="d-flex flex-wrap gap-1 mt-1">
                                <?php foreach ($leads as $l): ?>
                                    <span class="badge bg-light text-dark"><?= htmlspecialchars($l['name']) ?></span>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <form method="POST" action="<?= BASE_URL ?>/associate/leads/bulk-whatsapp">
                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                            <?php foreach ($leads as $l): ?>
                                <input type="hidden" name="lead_ids[]" value="<?= $l['id'] ?>">
                            <?php endforeach; ?>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Message Template</label>
                                <textarea class="form-control" name="message" rows="5" required placeholder="Hi {name}, ..."><?= htmlspecialchars($message ?: "Hi {name}, thank you for your interest in APS Dream Home! We'd love to show you our properties. Would you like to schedule a site visit?\n\nBest regards,\nAPS Dream Home Team") ?></textarea>
                                <small class="text-muted">Use <code>{name}</code> to personalize with lead's name, <code>{phone}</code> for their number.</small>
                            </div>

                            <!-- Quick Templates -->
                            <div class="mb-3">
                                <label class="form-label fw-bold">Quick Templates</label>
                                <div class="d-grid gap-2">
                                    <button type="button" class="btn btn-outline-success btn-sm text-start" onclick="setTemplate('Hi {name}, thank you for your interest in APS Dream Home! We have premium plots available in prime locations. Would you like to schedule a site visit?\n\nBest regards,\nAPS Dream Home Team')">
                                        <i class="fas fa-hand-wave me-1"></i> Welcome Message
                                    </button>
                                    <button type="button" class="btn btn-outline-success btn-sm text-start" onclick="setTemplate('Hi {name}, exciting news! We have new plots launching in Greater Noida starting from ₹30 Lakh. Limited slots available. Book your site visit today!\n\nCall us: +91 9876543210')">
                                        <i class="fas fa-rocket me-1"></i> New Launch
                                    </button>
                                    <button type="button" class="btn btn-outline-success btn-sm text-start" onclick="setTemplate('Hi {name}, just following up on your property inquiry. Do you have any questions? We\'re happy to help!\n\nView properties: https://apsdreamhome.in/properties')">
                                        <i class="fas fa-followme me-1"></i> Follow-up
                                    </button>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-success">
                                <i class="fab fa-whatsapp me-1"></i> Generate WhatsApp Links (<?= count($leads) ?>)
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <?php if (!empty($whatsappLinks)): ?>
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="fas fa-check-circle text-success me-2"></i><?= $sent ?> Message(s) Ready</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">Click each link to open WhatsApp and send the message.</p>
                    <div class="d-grid gap-2">
                        <?php foreach ($whatsappLinks as $link): ?>
                        <a href="<?= $link['url'] ?>" target="_blank" class="btn btn-outline-success d-flex justify-content-between align-items-center">
                            <span><i class="fab fa-whatsapp me-2"></i><?= htmlspecialchars($link['name']) ?></span>
                            <span class="badge bg-success"><i class="fas fa-external-link-alt"></i></span>
                        </a>
                        <?php endforeach; ?>
                    </div>
                    <div class="mt-3 text-center">
                        <button class="btn btn-success" onclick="openAllLinks()">
                            <i class="fas fa-external-link-alt me-1"></i> Open All (<?= $sent ?>)
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
function setTemplate(text) {
    document.querySelector('textarea[name="message"]').value = text;
}
function openAllLinks() {
    var links = document.querySelectorAll('.btn-outline-success[href*="wa.me"]');
    links.forEach(function(l, i) {
        setTimeout(function() { window.open(l.href, '_blank'); }, i * 500);
    });
}
</script>
