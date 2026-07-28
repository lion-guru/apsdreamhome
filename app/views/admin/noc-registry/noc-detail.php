<?php
$page_title = $page_title ?? __('admin_noc_detail');
ob_start();
$noc = $noc ?? [];
$eligibility = $eligibility ?? [];
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1"><i class="fas fa-file-contract me-2"></i><?= htmlspecialchars($page_title) ?></h4>
        <span class="text-muted">NOC #<?= $noc['id'] ?? 0 ?> — <?= htmlspecialchars($noc['noc_number'] ?? 'Pending') ?></span>
    </div>
    <a href="<?= BASE_URL ?>/admin/noc-registry/nocs" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i><?= __('admin_back_to_nocs') ?></a>
</div>

<?php if (!empty($_SESSION['flash_success'])): ?>
    <div class="alert alert-success alert-dismissible fade show"><i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($_SESSION['flash_success'] ?? ''); unset($_SESSION['flash_success']); ?></div>
<?php endif; ?>
<?php if (!empty($_SESSION['flash_error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show"><i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($_SESSION['flash_error'] ?? ''); unset($_SESSION['flash_error']); ?></div>
<?php endif; ?>

<?php if (empty($noc)): ?>
    <div class="alert alert-danger"><?= __('admin_noc_not_found') ?></div>
<?php else: ?>
    <div class="row g-4">
        <!-- NOC Info -->
        <div class="col-md-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0"><?= __('admin_noc_information') ?></h6>
                        <?php
                        $colors = ['pending'=>'warning','processing'=>'info','approved'=>'success','rejected'=>'danger','blocked'=>'dark'];
                        $color = $colors[$noc['status']] ?? 'secondary';
                        ?>
                        <span class="badge bg-<?= $color ?> fs-6"><?= ucfirst($noc['status']) ?></span>
                    </div>
                </div>
                <div class="card-body aps-cp-card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="text-muted small"><?= __('admin_noc_number') ?></div>
                            <div class="fw-semibold"><?= htmlspecialchars($noc['noc_number'] ?? '—') ?></div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted small"><?= __('admin_booking_label') ?></div>
                            <div class="fw-semibold"><?= htmlspecialchars($noc['booking_number']) ?></div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted small"><?= __('admin_customer_label') ?></div>
                            <div class="fw-semibold"><?= htmlspecialchars($noc['customer_name']) ?></div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted small"><?= __('admin_customer_phone') ?></div>
                            <div class="fw-semibold"><?= htmlspecialchars($noc['customer_phone'] ?? '—') ?></div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted small"><?= __('admin_plot_label') ?></div>
                            <div class="fw-semibold"><?= htmlspecialchars($noc['plot_no']) ?>, <?= htmlspecialchars($noc['colony_name']) ?></div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted small"><?= __('admin_purpose_label') ?></div>
                            <div class="fw-semibold"><?= htmlspecialchars($noc['purpose']) ?></div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted small"><?= __('admin_created_at') ?></div>
                            <div class="fw-semibold"><?= date('d M Y h:i A', strtotime($noc['created_at'])) ?></div>
                        </div>
                        <?php if ($noc['approved_at']): ?>
                        <div class="col-md-6">
                            <div class="text-muted small"><?= __('admin_approved_at') ?></div>
                            <div class="fw-semibold"><?= date('d M Y h:i A', strtotime($noc['approved_at'])) ?></div>
                        </div>
                        <?php endif; ?>
                        <?php if ($noc['rejection_reason']): ?>
                        <div class="col-12">
                            <div class="text-muted small"><?= __('admin_rejection_reason') ?></div>
                            <div class="text-danger fw-semibold"><?= htmlspecialchars($noc['rejection_reason']) ?></div>
                        </div>
                        <?php endif; ?>
                        <?php if ($noc['notes']): ?>
                        <div class="col-12">
                            <div class="text-muted small"><?= __('admin_notes_label') ?></div>
                            <div><?= nl2br(htmlspecialchars($noc['notes'])) ?></div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Actions Sidebar -->
        <div class="col-md-4">
            <!-- Eligibility Checks -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom">
                    <h6 class="mb-0"><i class="fas fa-check-double me-2"></i><?= __('admin_eligibility_checks') ?></h6>
                </div>
                <div class="card-body aps-cp-card-body">
                    <?php if (!empty($eligibility['checks'])): ?>
                        <?php foreach ($eligibility['checks'] as $check): ?>
                            <div class="d-flex align-items-start mb-2">
                                <i class="fas fa-<?= $check['passed'] ? 'check-circle text-success' : 'times-circle text-danger' ?> me-2 mt-1"></i>
                                <div>
                                    <div class="small fw-semibold"><?= htmlspecialchars($check['label']) ?></div>
                                    <?php if (!$check['passed']): ?>
                                        <div class="text-danger" style="font-size:.75rem;"><?= htmlspecialchars($check['message']) ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <hr>
                        <div class="small">
                            <?php if ($eligibility['eligible']): ?>
                                <span class="text-success fw-bold"><i class="fas fa-check me-1"></i><?= __('admin_eligible_for_noc') ?></span>
                            <?php else: ?>
                                <span class="text-danger fw-bold"><i class="fas fa-times me-1"></i><?= __('admin_not_eligible_prefix') ?> <?= $eligibility['fail_count'] ?> <?= __('admin_checks_failed') ?></span>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-muted small"><?= __('admin_no_checks_available') ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Actions -->
            <?php if (in_array($noc['status'], ['pending', 'processing'])): ?>
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h6 class="mb-0"><i class="fas fa-cog me-2"></i><?= __('admin_actions_label') ?></h6>
                </div>
                <div class="card-body aps-cp-card-body">
                    <form method="POST" action="<?= BASE_URL ?>/admin/noc-registry/nocs/approve" class="mb-2">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                        <input type="hidden" name="noc_id" value="<?= $noc['id'] ?>">
                        <button type="submit" class="btn btn-success w-100"><i class="fas fa-check me-1"></i><?= __('admin_approve_noc') ?></button>
                    </form>

                    <form method="POST" action="<?= BASE_URL ?>/admin/noc-registry/nocs/reject">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                        <input type="hidden" name="noc_id" value="<?= $noc['id'] ?>">
                        <div class="mb-2">
                            <input type="text" name="reason" class="form-control form-control-sm" placeholder="<?= __('admin_rejection_reason_placeholder') ?>" required>
                        </div>
                        <button type="submit" class="btn btn-danger w-100"><i class="fas fa-times me-1"></i><?= __('admin_reject_noc') ?></button>
                    </form>

                    <?php if ($noc['status'] === 'rejected'): ?>
                    <form method="POST" action="<?= BASE_URL ?>/admin/noc-registry/nocs/reprocess" class="mt-2">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                        <input type="hidden" name="noc_id" value="<?= $noc['id'] ?>">
                        <button type="submit" class="btn btn-info w-100"><i class="fas fa-redo me-1"></i><?= __('admin_reprocess') ?></button>
                    </form>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<?php
$content = ob_get_clean();
require_once APP_PATH . '/views/layouts/unified.php';
