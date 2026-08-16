<?php
$page_title = $page_title ?? __('assoc_bw_title', [], 'Bulk WhatsApp');
$current_page = 'leads';
$leads = $leads ?? [];
$whatsappLinks = $whatsappLinks ?? [];
$sent = $sent ?? 0;
$message = $message ?? '';
?>

<div class="container-fluid px-4 py-3">
    <a href="<?= BASE_URL ?>/associate/leads" class="text-decoration-none mb-3 d-inline-block">
        <i class="fas fa-arrow-left me-1"></i> <?= __('assoc_bw_back', [], 'Back to Leads') ?>
    </a>

    <div class="row">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="fab fa-whatsapp me-2" class="style-43764"></i><?= __('assoc_bw_title', [], 'Bulk WhatsApp Messages') ?></h5>
                </div>
                <div class="card-body">
                    <?php if (empty($leads)): ?>
                        <p class="text-muted"><?= __('assoc_bw_select_hint', [], 'Select leads from the') ?> <a href="<?= BASE_URL ?>/associate/leads"><?= __('assoc_bw_leads_list', [], 'leads list') ?></a> <?= __('assoc_bw_send_hint', [], 'to send bulk messages.') ?></p>
                        <div class="alert alert-info border-0" class="style-15087">
                            <h6><i class="fas fa-info-circle me-2"></i><?= __('assoc_bw_how_to', [], 'How to use') ?></h6>
                            <ol class="mb-0" class="style-47175">
                                <li><?= __('assoc_bw_step1', [], 'Go to My Leads') ?></li>
                                <li><?= __('assoc_bw_step2', [], 'Check the boxes next to leads you want to message') ?></li>
                                <li><?= __('assoc_bw_step3', [], 'Click "Bulk WhatsApp" button') ?></li>
                                <li><?= __('assoc_bw_step4', [], 'Compose your message and send') ?></li>
                            </ol>
                        </div>
                    <?php else: ?>
                        <div class="mb-3">
                            <strong><?= __('assoc_bw_selected', ['count' => count($leads)], '%count% lead(s) selected') ?></strong>
                            <div class="d-flex flex-wrap gap-1 mt-1">
                                <?php foreach ($leads as $l): ?>
                                    <span class="badge bg-light text-dark"><?= htmlspecialchars($l['name'] ?? '') ?></span>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <form method="POST" action="<?= BASE_URL ?>/associate/leads/bulk-whatsapp">
                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                            <?php foreach ($leads as $l): ?>
                                <input type="hidden" name="lead_ids[]" value="<?= $l['id'] ?>">
                            <?php endforeach; ?>
                            <div class="mb-3">
                                <label class="form-label fw-bold"><?= __('assoc_bw_message_template', [], 'Message Template') ?></label>
                                <textarea class="form-control" name="message" rows="5" required placeholder="Hi {name}, ..."><?= htmlspecialchars($message ?: __('assoc_bw_default_msg', [], "Hi {name}, thank you for your interest in APS Dream Home! We'd love to show you our properties. Would you like to schedule a site visit?\n\nBest regards,\nAPS Dream Home Team")) ?></textarea>
                                <small class="text-muted"><?= __('assoc_bw_personalize_hint', [], 'Use {name} to personalize with lead\'s name, {phone} for their number.') ?></small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold"><?= __('assoc_bw_quick_templates', [], 'Quick Templates') ?></label>
                                <div class="d-grid gap-2">
                                    <button type="button" class="btn btn-outline-success btn-sm text-start" onclick="setTemplate('<?= __('assoc_bw_template_welcome', [], "Hi {name}, thank you for your interest in APS Dream Home! We have premium plots available in prime locations. Would you like to schedule a site visit?\n\nBest regards,\nAPS Dream Home Team") ?>')">
                                        <i class="fas fa-hand-wave me-1"></i> <?= __('assoc_bw_welcome_msg', [], 'Welcome Message') ?>
                                    </button>
                                    <button type="button" class="btn btn-outline-success btn-sm text-start" onclick="setTemplate('<?= __('assoc_bw_template_launch', [], "Hi {name}, exciting news! We have new plots launching in Greater Noida starting from ₹30 Lakh. Limited slots available. Book your site visit today!\n\nCall us: +91 7007444842") ?>')">
                                        <i class="fas fa-rocket me-1"></i> <?= __('assoc_bw_new_launch', [], 'New Launch') ?>
                                    </button>
                                    <button type="button" class="btn btn-outline-success btn-sm text-start" onclick="setTemplate('<?= __('assoc_bw_template_followup', [], "Hi {name}, just following up on your property inquiry. Do you have any questions? We're happy to help!\n\nView properties: https://apsdreamhome.in/properties") ?>')">
                                        <i class="fas fa-followme me-1"></i> <?= __('assoc_bw_followup', [], 'Follow-up') ?>
                                    </button>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-success">
                                <i class="fab fa-whatsapp me-1"></i> <?= __('assoc_bw_generate_links', ['count' => count($leads)], 'Generate WhatsApp Links (%count%)') ?>
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
                    <h5 class="mb-0"><i class="fas fa-check-circle text-success me-2"></i><?= __('assoc_bw_msgs_ready', ['count' => $sent], '%count% Message(s) Ready') ?></h5>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3"><?= __('assoc_bw_click_to_send', [], 'Click each link to open WhatsApp and send the message.') ?></p>
                    <div class="d-grid gap-2">
                        <?php foreach ($whatsappLinks as $link): ?>
                        <a href="<?= $link['url'] ?>" target="_blank" class="btn btn-outline-success d-flex justify-content-between align-items-center">
                            <span><i class="fab fa-whatsapp me-2"></i><?= htmlspecialchars($link['name'] ?? '') ?></span>
                            <span class="badge bg-success"><i class="fas fa-external-link-alt"></i></span>
                        </a>
                        <?php endforeach; ?>
                    </div>
                    <div class="mt-3 text-center">
                        <button class="btn btn-success" onclick="openAllLinks()">
                            <i class="fas fa-external-link-alt me-1"></i> <?= __('assoc_bw_open_all', ['count' => $sent], 'Open All (%count%)') ?>
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
