<?php
$page_title = $page_title ?? 'Registries';
ob_start();
$registries = $registries ?? [];
$status_filter = $status_filter ?? null;
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1"><i class="fas fa-landmark me-2"></i><?= htmlspecialchars($page_title) ?></h4>
        <span class="text-muted"><?= __('admin_registry_subtitle') ?></span>
    </div>
    <a href="<?= BASE_URL ?>/admin/noc-registry/registries/create" class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i><?= __('admin_new_registry') ?></a>
</div>

<?php if (!empty($_SESSION['flash_success'])): ?>
    <div class="alert alert-success alert-dismissible fade show"><i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($_SESSION['flash_success'] ?? ''); unset($_SESSION['flash_success']); ?></div>
<?php endif; ?>
<?php if (!empty($_SESSION['flash_error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show"><i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($_SESSION['flash_error'] ?? ''); unset($_SESSION['flash_error']); ?></div>
<?php endif; ?>

<!-- Filter Buttons -->
<div class="mb-3">
    <a href="<?= BASE_URL ?>/admin/noc-registry/registries" class="btn btn-sm <?= !$status_filter ? 'btn-primary' : 'btn-outline-primary' ?>">All</a>
    <?php foreach (['pending','appointment_scheduled','documents_submitted','in_progress','completed','rejected','cancelled'] as $s): ?>
        <a href="<?= BASE_URL ?>/admin/noc-registry/registries?status=<?= $s ?>" class="btn btn-sm <?= $status_filter === $s ? 'btn-primary' : 'btn-outline-primary' ?>"><?= ucfirst(str_replace('_',' ',$s)) ?></a>
    <?php endforeach; ?>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <?php if (empty($registries)): ?>
            <div class="p-5 text-center text-muted">
                <i class="fas fa-landmark fa-3x mb-3 opacity-25"></i>
                <p><?= __('admin_no_registries_found') ?><?= $status_filter ? " with status: $status_filter" : '' ?></p>
                <a href="<?= BASE_URL ?>/admin/noc-registry/registries/create" class="btn btn-primary btn-sm"><?= __('admin_create_registry') ?></a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th><?= __('admin_booking_label') ?></th>
                            <th><?= __('admin_customer_label') ?></th>
                            <th><?= __('admin_plot_label') ?></th>
                            <th><?= __('admin_stamp_duty') ?></th>
                            <th><?= __('admin_reg_fee') ?></th>
                            <th><?= __('admin_total_cost') ?></th>
                            <th><?= __('admin_status_label') ?></th>
                            <th><?= __('admin_reg_no') ?></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($registries as $r): ?>
                        <tr>
                            <td><?= $r['id'] ?></td>
                            <td class="small fw-semibold"><?= htmlspecialchars($r['booking_number']) ?></td>
                            <td class="small"><?= htmlspecialchars($r['customer_name'] ?? '—') ?></td>
                            <td class="small"><?= htmlspecialchars($r['plot_no']) ?>, <?= htmlspecialchars($r['colony_name']) ?></td>
                            <td class="small">₹<?= number_format($r['stamp_duty_amount'], 0) ?></td>
                            <td class="small">₹<?= number_format($r['registration_fee'], 0) ?></td>
                            <td class="small fw-bold">₹<?= number_format($r['total_registry_cost'], 0) ?></td>
                            <td>
                                <?php
                                $colors = ['pending'=>'secondary','appointment_scheduled'=>'info','documents_submitted'=>'warning','in_progress'=>'primary','completed'=>'success','rejected'=>'danger','cancelled'=>'dark'];
                                $color = $colors[$r['status']] ?? 'secondary';
                                ?>
                                <span class="badge bg-<?= $color ?>"><?= ucfirst(str_replace('_',' ',$r['status'])) ?></span>
                            </td>
                            <td class="small"><?= htmlspecialchars($r['registration_no'] ?? '—') ?></td>
                            <td><a href="<?= BASE_URL ?>/admin/noc-registry/registries/<?= $r['id'] ?>" class="btn btn-sm btn-outline-primary">View</a></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

