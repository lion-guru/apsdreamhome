<?php
$page_title = $page_title ?? 'Registry Requests';
$page_heading = $page_heading ?? 'Registry Requests';
$registries = $registries ?? [];
$filters = $filters ?? [];
ob_start();
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1"><i class="fas fa-landmark me-2"></i>Registry Requests</h2>
            <p class="text-muted mb-0">Sub-registrar office registration tracking</p>
        </div>
        <a href="<?= BASE_URL ?>/admin/noc-registry/registries/create" class="btn btn-success"><i class="fas fa-plus me-2"></i>Request Registry</a>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Statuses</option>
                        <?php foreach (['pending'=>'Pending','appointment_scheduled'=>'Appointment','documents_submitted'=>'Docs Submitted','in_progress'=>'In Progress','completed'=>'Completed','rejected'=>'Rejected','cancelled'=>'Cancelled'] as $v=>$l): ?>
                            <option value="<?= $v ?>" <?= ($filters['status'] ?? '') === $v ? 'selected' : '' ?>><?= $l ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2"><button type="submit" class="btn btn-primary w-100"><i class="fas fa-filter me-1"></i>Filter</button></div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr><th>#</th><th>Booking</th><th>Customer</th><th>Plot</th><th>Office</th><th>Cost</th><th>NOC</th><th>Status</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                        <?php if (empty($registries)): ?>
                            <tr><td colspan="9" class="text-center text-muted py-4"><i class="fas fa-inbox fa-2x d-block mb-2"></i>No registries found</td></tr>
                        <?php else: foreach ($registries as $r): ?>
                            <tr>
                                <td>#<?= $r['id'] ?></td>
                                <td><strong><?= htmlspecialchars($r['booking_number'] ?? '') ?></strong></td>
                                <td><?= htmlspecialchars($r['customer_name'] ?? '') ?></td>
                                <td><?= htmlspecialchars(($r['block'] ?? '') . '-' . ($r['plot_number'] ?? '')) ?></td>
                                <td><?= htmlspecialchars($r['sub_registrar_office'] ?? '') ?></td>
                                <td>₹<?= number_format($r['total_registry_cost'] ?? 0) ?></td>
                                <td><span class="badge bg-success">Approved</span></td>
                                <td>
                                    <?php
                                    $colors = ['pending'=>'warning','appointment_scheduled'=>'info','documents_submitted'=>'primary','in_progress'=>'primary','completed'=>'success','rejected'=>'danger','cancelled'=>'secondary'];
                                    ?>
                                    <span class="badge bg-<?= $colors[$r['status']] ?? 'secondary' ?> px-3 py-2"><?= ucfirst(str_replace('_',' ',$r['status'])) ?></span>
                                </td>
                                <td><a href="<?= BASE_URL ?>/admin/noc-registry/registries/<?= $r['id'] ?>" class="btn btn-sm btn-outline-success"><i class="fas fa-eye"></i></a></td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
include APP_PATH . '/views/admin/layouts/unified.php';
