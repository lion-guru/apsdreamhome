<?php
/** @var array $templates */
/** @var string $page_title */
/** @var string $page_heading */
/** @var string|null $flash_success */
/** @var string|null $flash_error */
/** @var string $admin_email */
/** @var string $base_url */

$page_title  = $page_title  ?? 'Email Templates';
$page_heading = $page_heading ?? 'HTML Email Templates';
$base_url    = $base_url    ?? (defined('BASE_URL') ? BASE_URL : '');
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1"><i class="fas fa-envelope-open-text me-2"></i><?= htmlspecialchars($page_heading) ?></h1>
            <p class="text-muted mb-0">Brand-styled, responsive HTML emails sent from APS Dream Home.</p>
        </div>
        <a href="<?= htmlspecialchars($base_url) ?>/admin/dashboard" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i><?= __('admin_btn_back', null, 'Back') ?>
        </a>
    </div>

    <?php if (!empty($flash_success)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($flash_success) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (!empty($flash_error)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i><?= htmlspecialchars($flash_error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (empty($admin_email)): ?>
        <div class="alert alert-warning d-flex align-items-center" role="alert">
            <i class="fas fa-info-circle me-2"></i>
            <div>
                No admin email configured. Set <code>MAIL_FROM_ADDRESS</code> in your <code>.env</code> or
                configure SMTP in <a href="<?= htmlspecialchars($base_url) ?>/admin/settings/email" class="alert-link">Email Settings</a> before sending test emails.
            </div>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-list me-2"></i><?= __('admin_tpl_available', null, 'Available Templates') ?> (<?= count($templates) ?>)</h5>
            <span class="badge bg-primary"><?= __('admin_tpl_brand', null, 'Brand: #0d9488 â†’ #0f766e') ?></span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="style-45100"><?= __('admin_tpl_code', null, 'Code') ?></th>
                            <th class="style-85429"><?= __('admin_tpl_subject', null, 'Subject') ?></th>
                            <th class="style-26295"><?= __('admin_tpl_file', null, 'File') ?></th>
                            <th class="style-33374"><?= __('admin_tpl_vars', null, 'Variables') ?></th>
                            <th class="style-33374" class="text-end"><?= __('admin_tpl_actions', null, 'Actions') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($templates)): ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">
                                <?= __('admin_tpl_empty', null, 'No email templates found in the catalog.') ?>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($templates as $tpl): ?>
                            <tr>
                                <td>
                                    <code class="text-primary fw-bold"><?= htmlspecialchars($tpl['code']) ?></code>
                                </td>
                                <td>
                                    <div class="text-truncate" class="style-48743" title="<?= htmlspecialchars($tpl['subject']) ?>">
                                        <?= htmlspecialchars($tpl['subject']) ?>
                                    </div>
                                </td>
                                <td>
                                    <?php if (!empty($tpl['exists'])): ?>
                                        <span class="text-success" title="File exists">
                                            <i class="fas fa-check-circle me-1"></i><?= htmlspecialchars($tpl['file']) ?>
                                            <small class="text-muted">(<?= (int)$tpl['size'] ?> B)</small>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-danger" title="File missing">
                                            <i class="fas fa-times-circle me-1"></i><?= htmlspecialchars($tpl['file']) ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php foreach ($tpl['vars'] as $v): ?>
                                        <span class="badge bg-light text-dark border me-1 mb-1" title="Placeholder">{{<?= htmlspecialchars($v) ?>}}</span>
                                    <?php endforeach; ?>
                                </td>
                                <td class="text-end">
                                    <a href="<?= htmlspecialchars($base_url) ?>/admin/email-templates/preview/<?= urlencode($tpl['code']) ?>"
                                       target="_blank"
                                       class="btn btn-sm btn-outline-info me-1"
                                       title="Preview rendered HTML in a new tab">
                                        <i class="fas fa-eye me-1"></i><?= __('admin_btn_preview', null, 'Preview') ?>
                                    </a>
                                    <a href="<?= htmlspecialchars($base_url) ?>/admin/email-templates/test/<?= urlencode($tpl['code']) ?>"
                                       class="btn btn-sm btn-primary"
                                       title="Send a test email using default placeholder values"
                                       onclick="return confirm('Send a test email to <?= htmlspecialchars($admin_email ?: 'admin') ?>?');">
                                        <i class="fas fa-paper-plane me-1"></i><?= __('admin_btn_test_send', null, 'Test Send') ?>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white text-muted small">
            <i class="fas fa-info-circle me-1"></i>
            Templates are loaded from <code>app/views/emails/</code> and rendered by
            <code>App\Services\Communication\TemplateService::renderHtmlTemplate()</code>.
            Placeholders use <code>{{var}}</code> syntax (whitespace allowed).
        </div>
    </div>
</div>
