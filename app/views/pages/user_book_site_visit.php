<?php if (!isset($sc)) { $sc = function($k, $d='') { return $GLOBALS['_site_settings_cache'][$k] ?? $d; }; } $phoneRaw = preg_replace('/[^0-9]/', '', $sc('contact_whatsapp', '919277121112')); $phoneDisplay = $sc('contact_phone', '<?= htmlspecialchars($phoneDisplay ?? '') ?>'); $emailDisplay = $sc('contact_email', '<?= htmlspecialchars($emailDisplay ?? '') ?>'); ?>
<div class="container py-4">
    <h3 class="mb-4"><i class="fas fa-calendar-check me-2"></i><?php echo __('book_site_visit_heading', [], 'Book a Site Visit'); ?></h3>
    <?php if (!empty($message)): ?>
        <div class="alert alert-<?= $message_type ?? 'info' ?>"><?= htmlspecialchars($message ?? '') ?></div>
    <?php endif; ?>
    <div class="row">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-body aps-cp-card-body">
                    <form method="POST" action="<?= BASE_URL ?>/user/book-site-visit">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        <div class="mb-3">
                            <label class="form-label"><?php echo __('book_site_visit_property', [], 'Property/Project'); ?> <span class="text-danger">*</span></label>
                            <select name="property_id" class="form-select" required>
                                <option value=""><?php echo __('book_site_visit_select_property', [], '-- Select Property --'); ?></option>
                                <?php if (!empty($properties)): foreach ($properties as $p): ?>
                                    <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['name'] ?? $p['title'] ?? 'Property #'.$p['id']) ?></option>
                                <?php endforeach; endif; ?>
                                <option value="0"><?php echo __('book_site_visit_general_inquiry', [], 'General inquiry (no specific property)'); ?></option>
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label"><?php echo __('book_site_visit_date', [], 'Preferred Date'); ?> <span class="text-danger">*</span></label>
                                <input type="date" name="visit_date" class="form-control" required min="<?= date('Y-m-d') ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label"><?php echo __('book_site_visit_time', [], 'Preferred Time'); ?></label>
                                <select name="visit_time" class="form-select">
                                    <option value="09:00-11:00">9:00 AM - 11:00 AM</option>
                                    <option value="11:00-13:00">11:00 AM - 1:00 PM</option>
                                    <option value="13:00-15:00">1:00 PM - 3:00 PM</option>
                                    <option value="15:00-17:00">3:00 PM - 5:00 PM</option>
                                    <option value="17:00-19:00">5:00 PM - 7:00 PM</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label"><?php echo __('book_site_visit_phone', [], 'Your Phone'); ?> <span class="text-danger">*</span></label>
                            <input type="tel" name="phone" class="form-control" value="<?= htmlspecialchars($_SESSION['user_phone'] ?? '') ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label"><?php echo __('book_site_visit_notes', [], 'Notes'); ?></label>
                            <textarea name="notes" class="form-control" rows="3" placeholder="<?php echo __('book_site_visit_notes_placeholder', [], 'Any specific requirements...'); ?>"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane me-1"></i><?php echo __('book_site_visit_submit', [], 'Submit Request'); ?></button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm bg-light">
                <div class="card-body aps-cp-card-body">
                    <h6><i class="fas fa-info-circle me-1"></i><?php echo __('book_site_visit_info_title', [], 'Visit Information'); ?></h6>
                    <ul class="small text-muted ps-3">
                        <li class="mb-1"><?php echo __('book_site_visit_info_1', [], 'Our team will confirm your visit within 24 hours'); ?></li>
                        <li class="mb-1"><?php echo __('book_site_visit_info_2', [], 'Free pick-up & drop from office'); ?></li>
                        <li class="mb-1"><?php echo __('book_site_visit_info_3', [], 'Complimentary refreshments'); ?></li>
                        <li class="mb-1"><?php echo __('book_site_visit_info_4', [], 'Project brochures & price list provided'); ?></li>
                    </ul>
                    <hr>
                    <p class="small mb-0"><i class="fas fa-phone me-1"></i>Call: <?= htmlspecialchars($phoneDisplay ?? '') ?></p>
                </div>
            </div>
        </div>
    </div>
</div>
