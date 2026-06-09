<?php $page_title = $page_title ?? __('finance_edit_template'); $page_heading = $page_heading ?? __('finance_edit_template'); $template = $template ?? null; $id = (int)($template['id'] ?? 0); ?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="fas fa-file-alt me-2 text-primary"></i><?= $id > 0 ? __('finance_edit_template') : __('finance_new_template') ?></h2>
        <a href="<?= BASE_URL ?>/admin/finance/templates" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i><?php echo __('finance_back'); ?></a>
    </div>
    <div class="aps-cp-card">
        <div class="aps-cp-card-body">
            <form method="post" action="<?= BASE_URL ?>/admin/finance/template-store">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                <input type="hidden" name="id" value="<?= $id ?>">
                <div class="row g-3">
                    <div class="col-md-6"><label class="form-label"><?php echo __('finance_template_name'); ?> <span class="text-danger">*</span></label><input type="text" name="template_name" required class="form-control" value="<?= htmlspecialchars($template['template_name'] ?? '') ?>"></div>
                    <div class="col-md-4"><label class="form-label"><?php echo __('finance_type'); ?> <span class="text-danger">*</span></label>
                        <select name="template_type" required class="form-select">
                            <option value="overdue_installment" <?= ($template['template_type'] ?? '') === 'overdue_installment' ? 'selected' : '' ?>><?php echo __('finance_overdue_installment'); ?></option>
                            <option value="final_notice" <?= ($template['template_type'] ?? '') === 'final_notice' ? 'selected' : '' ?>><?php echo __('finance_final_notice'); ?></option>
                            <option value="booking_confirmation" <?= ($template['template_type'] ?? '') === 'booking_confirmation' ? 'selected' : '' ?>><?php echo __('finance_booking_confirmation'); ?></option>
                            <option value="payment_receipt" <?= ($template['template_type'] ?? '') === 'payment_receipt' ? 'selected' : '' ?>><?php echo __('finance_payment_receipt'); ?></option>
                            <option value="cancellation" <?= ($template['template_type'] ?? '') === 'cancellation' ? 'selected' : '' ?>><?php echo __('finance_cancellation'); ?></option>
                            <option value="transfer" <?= ($template['template_type'] ?? '') === 'transfer' ? 'selected' : '' ?>><?php echo __('finance_transfer'); ?></option>
                        </select>
                    </div>
                    <div class="col-md-2"><label class="form-label"><?php echo __('finance_active'); ?></label>
                        <div class="form-check mt-2"><input class="form-check-input" type="checkbox" name="active" value="1" id="active" <?= !empty($template['active']) || $id === 0 ? 'checked' : '' ?>><label class="form-check-label" for="active"><?php echo __('finance_active'); ?></label></div>
                    </div>
                    <div class="col-12"><label class="form-label"><?php echo __('finance_subject'); ?></label><input type="text" name="subject" class="form-control" value="<?= htmlspecialchars($template['subject'] ?? '') ?>"></div>
                    <div class="col-12">
                        <label class="form-label"><?php echo __('finance_body_html'); ?> <span class="text-danger">*</span></label>
                        <textarea name="body_html" required class="form-control" rows="14" placeholder="<?php echo __('finance_available_placeholders'); ?> {{customer_name}}, {{amount}}, {{due_date}}, {{booking_number}}"><?= htmlspecialchars($template['body_html'] ?? '') ?></textarea>
                        <small class="text-muted"><?php echo __('finance_available_placeholders'); ?> <code>{{customer_name}}</code> <code>{{amount}}</code> <code>{{due_date}}</code> <code>{{booking_number}}</code> <code>{{plot_number}}</code> <code>{{project_name}}</code></small>
                    </div>
                </div>
                <div class="mt-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i><?php echo __('finance_save'); ?></button>
                    <a href="<?= BASE_URL ?>/admin/finance/templates" class="btn btn-outline-secondary"><?php echo __('finance_cancel'); ?></a>
                </div>
            </form>
        </div>
    </div>
</div>
