<?php
$page_title = $page_title ?? __('admin_eligibility_check');
ob_start();
$eligible_bookings = $eligible_bookings ?? [];
$result = $result ?? null;
$booking_id = $booking_id ?? 0;
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1"><i class="fas fa-check-double me-2"></i><?= htmlspecialchars($page_title) ?></h4>
        <span class="text-muted"><?= __('admin_eligibility_subtitle') ?></span>
    </div>
    <a href="<?= BASE_URL ?>/admin/noc-registry/dashboard" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i><?= __('admin_back_to_dashboard') ?></a>
</div>

<?php if (!empty($_SESSION['flash_success'])): ?>
    <div class="alert alert-success alert-dismissible fade show"><i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($_SESSION['flash_success'] ?? ''); unset($_SESSION['flash_success']); ?></div>
<?php endif; ?>

<!-- Booking Selector -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-bottom">
        <h6 class="mb-0"><i class="fas fa-search me-2"></i><?= __('admin_select_booking_to_check') ?></h6>
    </div>
    <div class="card-body aps-cp-card-body">
        <form method="GET" action="<?= BASE_URL ?>/admin/noc-registry/eligibility" class="row g-3">
            <div class="col-md-8">
                <select name="booking_id" class="form-select" required>
                    <option value="">— Select a Booking —</option>
                    <?php foreach ($eligible_bookings as $b): ?>
                        <option value="<?= $b['id'] ?>" <?= $booking_id == $b['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($b['booking_number']) ?> — <?= htmlspecialchars($b['customer_name']) ?>
                            (<?= htmlspecialchars($b['plot_no']) ?>, <?= htmlspecialchars($b['colony_name']) ?>)
                            — ₹<?= number_format($b['total_price'] ?? 0, 0) ?>
                            [<?= htmlspecialchars($b['status']) ?>]
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-primary w-100"><i class="fas fa-check-double me-1"></i><?= __('admin_check_eligibility') ?></button>
            </div>
        </form>
    </div>
</div>

<?php if ($result): ?>
    <?php if (!$result['booking']): ?>
        <div class="alert alert-danger"><?= __('admin_booking_not_found') ?></div>
    <?php else: ?>
        <div class="row g-4">
            <!-- NOC Eligibility -->
            <div class="col-md-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="mb-0"><i class="fas fa-file-alt me-2"></i><?= __('admin_noc_eligibility') ?></h6>
                            <?php if ($result['noc']['eligible']): ?>
                                <span class="badge bg-success fs-6"><i class="fas fa-check me-1"></i><?= __('admin_eligible') ?></span>
                            <?php else: ?>
                                <span class="badge bg-danger fs-6"><i class="fas fa-times me-1"></i><?= __('admin_not_eligible') ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="card-body aps-cp-card-body">
                        <!-- Booking Info -->
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <div class="text-muted small"><?= __('admin_booking_label') ?></div>
                                <div class="fw-semibold small"><?= htmlspecialchars($result['booking']['booking_number']) ?></div>
                            </div>
                            <div class="col-6">
                                <div class="text-muted small"><?= __('admin_customer_label') ?></div>
                                <div class="fw-semibold small"><?= htmlspecialchars($result['booking']['customer_name']) ?></div>
                            </div>
                            <div class="col-6">
                                <div class="text-muted small"><?= __('admin_plot_label') ?></div>
                                <div class="fw-semibold small"><?= htmlspecialchars($result['booking']['plot_no']) ?></div>
                            </div>
                            <div class="col-6">
                                <div class="text-muted small"><?= __('admin_status_label') ?></div>
                                <span class="badge bg-<?= $result['booking']['status'] === 'fully_paid' || $result['booking']['status'] === 'registration_done' ? 'success' : 'warning' ?>">
                                    <?= ucfirst(str_replace('_', ' ', $result['booking']['status'])) ?>
                                </span>
                            </div>
                        </div>

                        <hr>

                        <!-- Checks -->
                        <?php if (!empty($result['noc']['checks'])): ?>
                            <?php foreach ($result['noc']['checks'] as $check): ?>
                                <div class="d-flex align-items-start mb-3">
                                    <div class="flex-shrink-0 me-3 mt-1">
                                        <i class="fas fa-<?= $check['passed'] ? 'check-circle text-success fa-lg' : 'times-circle text-danger fa-lg' ?>"></i>
                                    </div>
                                    <div>
                                        <div class="fw-semibold small"><?= htmlspecialchars($check['label']) ?></div>
                                        <div class="text-muted" style="font-size:.8rem;"><?= htmlspecialchars($check['message']) ?></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>

                        <hr>
                        <div class="text-center">
                            <?php if ($result['noc']['eligible']): ?>
                                <a href="<?= BASE_URL ?>/admin/noc-registry/nocs/create?booking_id=<?= $result['booking']['id'] ?>" class="btn btn-success"><i class="fas fa-plus me-1"></i><?= __('admin_request_noc') ?></a>
                            <?php else: ?>
                                <div class="text-danger small fw-bold"><i class="fas fa-ban me-1"></i><?= $result['noc']['fail_count'] ?> <?= __('admin_checks_failed_resolve') ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Registry Eligibility -->
            <div class="col-md-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="mb-0"><i class="fas fa-landmark me-2"></i><?= __('admin_registry_eligibility') ?></h6>
                            <?php if ($result['registry']['eligible']): ?>
                                <span class="badge bg-success fs-6"><i class="fas fa-check me-1"></i><?= __('admin_eligible') ?></span>
                            <?php else: ?>
                                <span class="badge bg-danger fs-6"><i class="fas fa-times me-1"></i><?= __('admin_not_eligible') ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="card-body aps-cp-card-body">
                        <div class="text-muted small mb-3"><?= __('admin_registry_requires_noc') ?></div>

                        <!-- Checks -->
                        <?php if (!empty($result['registry']['checks'])): ?>
                            <?php foreach ($result['registry']['checks'] as $check): ?>
                                <div class="d-flex align-items-start mb-3">
                                    <div class="flex-shrink-0 me-3 mt-1">
                                        <i class="fas fa-<?= $check['passed'] ? 'check-circle text-success fa-lg' : 'times-circle text-danger fa-lg' ?>"></i>
                                    </div>
                                    <div>
                                        <div class="fw-semibold small"><?= htmlspecialchars($check['label']) ?></div>
                                        <div class="text-muted" style="font-size:.8rem;"><?= htmlspecialchars($check['message']) ?></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>

                        <hr>
                        <div class="text-center">
                            <?php if ($result['registry']['eligible']): ?>
                                <a href="<?= BASE_URL ?>/admin/noc-registry/registries/create?booking_id=<?= $result['booking']['id'] ?>" class="btn btn-primary"><i class="fas fa-plus me-1"></i><?= __('admin_create_registry') ?></a>
                            <?php else: ?>
                                <div class="text-danger small fw-bold"><i class="fas fa-ban me-1"></i><?= $result['registry']['fail_count'] ?> <?= __('admin_checks_failed') ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
<?php endif; ?>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/unified.php';
