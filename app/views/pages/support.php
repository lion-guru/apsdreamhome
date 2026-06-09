<?php if (!isset($sc)) { $sc = function($k, $d='') { return $GLOBALS['_site_settings_cache'][$k] ?? $d; }; }$phoneRaw = preg_replace('/[^0-9]/', '', $sc('contact_whatsapp', '919277121112')); $phoneDisplay = $sc('contact_phone', '<?= $phoneDisplay ?>'); ?>
<?php
$priority = $_POST['priority'] ?? 'medium';
$subjectVal = $_POST['subject'] ?? '';
$messageVal = $_POST['message'] ?? '';
?>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-lg border-0 rounded-lg">
                <div class="card-header bg-primary text-white">
                    <h3 class="text-center font-weight-light my-4"><?php echo __('customer_support'); ?></h3>
                </div>
                <div class="card-body">
                    <?php if (!empty($success)): ?>
                        <div class="alert alert-success" role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            <?php echo htmlspecialchars($success); ?>
                        </div>
                    <?php elseif (!empty($error)): ?>
                        <div class="alert alert-danger" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>

                    <form action="<?= BASE_URL ?>support" method="post">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
                        <div class="mb-3">
                            <label for="subject" class="form-label"><?php echo __('subject'); ?> <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-lg" id="subject" name="subject" required
                                value="<?php echo isset($subjectVal) ? htmlspecialchars($subjectVal) : ''; ?>">
                        </div>

                        <div class="mb-3">
                            <label for="priority" class="form-label"><?php echo __('priority'); ?></label>
                            <select class="form-select form-select-lg" id="priority" name="priority">
                                <option value="low" <?php echo ($priority === 'low') ? 'selected' : ''; ?>><?php echo __('priority_low'); ?></option>
                                <option value="medium" <?php echo ($priority === 'medium') ? 'selected' : ''; ?>><?php echo __('priority_medium'); ?></option>
                                <option value="high" <?php echo ($priority === 'high') ? 'selected' : ''; ?>><?php echo __('priority_high'); ?></option>
                                <option value="urgent" <?php echo ($priority === 'urgent') ? 'selected' : ''; ?>><?php echo __('priority_urgent'); ?></option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label for="message" class="form-label"><?php echo __('message_label'); ?> <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="message" name="message" rows="6" required><?php echo isset($messageVal) ? htmlspecialchars($messageVal) : ''; ?></textarea>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-paper-plane me-2"></i><?php echo __('submit_request'); ?>
                            </button>
                        </div>
                    </form>
                </div>
                <div class="card-footer text-center py-3">
                    <div class="small">
                        <?php echo __('need_immediate_assistance'); ?> <a href="tel:<?= $phoneRaw ?>"><?= $phoneDisplay ?></a> <?php echo __('or_email_us'); ?>
                        <a href="mailto:info@apsdreamhome.com"><?php echo __('email_us_link'); ?></a>.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
