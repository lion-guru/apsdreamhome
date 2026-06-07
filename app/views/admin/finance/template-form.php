<?php $page_title = $page_title ?? 'Template'; $page_heading = $page_heading ?? 'Demand Letter Template'; $template = $template ?? null; $id = (int)($template['id'] ?? 0); ?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="fas fa-file-alt me-2 text-primary"></i><?= $id > 0 ? 'Edit Template' : 'New Template' ?></h2>
        <a href="<?= BASE_URL ?>/admin/finance/templates" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
    </div>
    <div class="aps-cp-card">
        <div class="aps-cp-card-body">
            <form method="post" action="<?= BASE_URL ?>/admin/finance/template-store">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                <input type="hidden" name="id" value="<?= $id ?>">
                <div class="row g-3">
                    <div class="col-md-6"><label class="form-label">Template Name <span class="text-danger">*</span></label><input type="text" name="template_name" required class="form-control" value="<?= htmlspecialchars($template['template_name'] ?? '') ?>"></div>
                    <div class="col-md-4"><label class="form-label">Type <span class="text-danger">*</span></label>
                        <select name="template_type" required class="form-select">
                            <option value="overdue_installment" <?= ($template['template_type'] ?? '') === 'overdue_installment' ? 'selected' : '' ?>>Overdue Installment</option>
                            <option value="final_notice" <?= ($template['template_type'] ?? '') === 'final_notice' ? 'selected' : '' ?>>Final Notice</option>
                            <option value="booking_confirmation" <?= ($template['template_type'] ?? '') === 'booking_confirmation' ? 'selected' : '' ?>>Booking Confirmation</option>
                            <option value="payment_receipt" <?= ($template['template_type'] ?? '') === 'payment_receipt' ? 'selected' : '' ?>>Payment Receipt</option>
                            <option value="cancellation" <?= ($template['template_type'] ?? '') === 'cancellation' ? 'selected' : '' ?>>Cancellation</option>
                            <option value="transfer" <?= ($template['template_type'] ?? '') === 'transfer' ? 'selected' : '' ?>>Transfer</option>
                        </select>
                    </div>
                    <div class="col-md-2"><label class="form-label">Active</label>
                        <div class="form-check mt-2"><input class="form-check-input" type="checkbox" name="active" value="1" id="active" <?= !empty($template['active']) || $id === 0 ? 'checked' : '' ?>><label class="form-check-label" for="active">Active</label></div>
                    </div>
                    <div class="col-12"><label class="form-label">Subject</label><input type="text" name="subject" class="form-control" value="<?= htmlspecialchars($template['subject'] ?? '') ?>"></div>
                    <div class="col-12">
                        <label class="form-label">Body (HTML) <span class="text-danger">*</span></label>
                        <textarea name="body_html" required class="form-control" rows="14" placeholder="Use {{customer_name}}, {{amount}}, {{due_date}}, {{booking_number}} placeholders"><?= htmlspecialchars($template['body_html'] ?? '') ?></textarea>
                        <small class="text-muted">Available placeholders: <code>{{customer_name}}</code> <code>{{amount}}</code> <code>{{due_date}}</code> <code>{{booking_number}}</code> <code>{{plot_number}}</code> <code>{{project_name}}</code></small>
                    </div>
                </div>
                <div class="mt-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Save</button>
                    <a href="<?= BASE_URL ?>/admin/finance/templates" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
