<?php
$page_title = $page_title ?? 'NOC Requests';
ob_start();
$nocs = $nocs ?? [];
$status_filter = $status_filter ?? null;
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1"><i class="fas fa-file-alt me-2"></i><?= htmlspecialchars($page_title) ?></h4>
        <span class="text-muted"><?= __('admin_noc_subtitle') ?></span>
    </div>
    <a href="<?= BASE_URL ?>/admin/noc-registry/nocs/create" class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i><?= __('admin_new_noc_request') ?></a>
</div>

<?php if (!empty($_SESSION['flash_success'])): ?>
    <div class="alert alert-success alert-dismissible fade show"><i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($_SESSION['flash_success']); unset($_SESSION['flash_success']); ?></div>
<?php endif; ?>
<?php if (!empty($_SESSION['flash_error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show"><i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($_SESSION['flash_error']); unset($_SESSION['flash_error']); ?></div>
<?php endif; ?>

<!-- Filter Buttons -->
<div class="mb-3">
    <a href="<?= BASE_URL ?>/admin/noc-registry/nocs" class="btn btn-sm <?= !$status_filter ? 'btn-primary' : 'btn-outline-primary' ?>">All</a>
    <?php foreach (['pending','processing','approved','rejected','blocked'] as $s): ?>
        <a href="<?= BASE_URL ?>/admin/noc-registry/nocs?status=<?= $s ?>" class="btn btn-sm <?= $status_filter === $s ? 'btn-primary' : 'btn-outline-primary' ?>"><?= ucfirst($s) ?></a>
    <?php endforeach; ?>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <?php if (empty($nocs)): ?>
            <div class="p-5 text-center text-muted">
                <i class="fas fa-file-alt fa-3x mb-3 opacity-25"></i>
                <p><?= __('admin_no_noc_requests_found') ?><?= $status_filter ? " with status: $status_filter" : '' ?></p>
                <a href="<?= BASE_URL ?>/admin/noc-registry/nocs/create" class="btn btn-primary btn-sm"><?= __('admin_request_noc') ?></a>
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
                            <th><?= __('admin_purpose_label') ?></th>
                            <th><?= __('admin_status_label') ?></th>
                            <th><?= __('admin_created_at') ?></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($nocs as $n): ?>
                        <tr>
                            <td><?= $n['id'] ?></td>
                            <td>
                                <div class="fw-semibold small"><?= htmlspecialchars($n['booking_number']) ?></div>
                            </td>
                            <td>
                                <div class="small"><?= htmlspecialchars($n['customer_name'] ?? '—') ?></div>
                                <div class="text-muted" style="font-size:.75rem;"><?= htmlspecialchars($n['customer_phone'] ?? '') ?></div>
                            </td>
                            <td class="small"><?= htmlspecialchars($n['plot_no']) ?>, <?= htmlspecialchars($n['colony_name']) ?></td>
                            <td class="small text-truncate" style="max-width:180px;"><?= htmlspecialchars($n['purpose']) ?></td>
                            <td>
                                <?php
                                $colors = ['pending'=>'warning','processing'=>'info','approved'=>'success','rejected'=>'danger','blocked'=>'dark'];
                                $color = $colors[$n['status']] ?? 'secondary';
                                ?>
                                <span class="badge bg-<?= $color ?>"><?= ucfirst($n['status']) ?></span>
                                <?php if ($n['noc_number']): ?>
                                    <div class="text-muted" style="font-size:.7rem;"><?= htmlspecialchars($n['noc_number']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="small text-muted"><?= date('d M Y', strtotime($n['created_at'])) ?></td>
                            <td><a href="<?= BASE_URL ?>/admin/noc-registry/nocs/<?= $n['id'] ?>" class="btn btn-sm btn-outline-primary">View</a></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/unified.php';
