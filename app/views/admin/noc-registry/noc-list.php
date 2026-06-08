<?php
$page_title = $page_title ?? 'NOC Requests';
$page_heading = $page_heading ?? 'NOC Requests';
$nocs = $nocs ?? [];
$filters = $filters ?? [];
ob_start();
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1"><i class="fas fa-file-contract me-2"></i>NOC Requests</h2>
            <p class="text-muted mb-0">No Objection Certificate requests for plot bookings</p>
        </div>
        <a href="<?= BASE_URL ?>/admin/noc-registry/nocs/create" class="btn btn-primary"><i class="fas fa-plus me-2"></i>Request NOC</a>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Statuses</option>
                        <?php foreach (['pending'=>'Pending','processing'=>'Processing','approved'=>'Approved','blocked'=>'Blocked','rejected'=>'Rejected','cancelled'=>'Cancelled'] as $v=>$l): ?>
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
                        <tr><th>#</th><th>Booking</th><th>Customer</th><th>Plot</th><th>Purpose</th><th>Status</th><th>Created</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                        <?php if (empty($nocs)): ?>
                            <tr><td colspan="8" class="text-center text-muted py-4"><i class="fas fa-inbox fa-2x d-block mb-2"></i>No NOC requests found</td></tr>
                        <?php else: foreach ($nocs as $n): ?>
                            <tr>
                                <td>#<?= $n['id'] ?></td>
                                <td><strong><?= htmlspecialchars($n['booking_number'] ?? '') ?></strong></td>
                                <td><?= htmlspecialchars($n['customer_name'] ?? '') ?></td>
                                <td><?= htmlspecialchars(($n['block'] ?? '') . '-' . ($n['plot_number'] ?? '')) ?></td>
                                <td><?= htmlspecialchars($n['purpose'] ?? '') ?></td>
                                <td>
                                    <?php
                                    $colors = ['pending'=>'warning','processing'=>'info','approved'=>'success','blocked'=>'danger','rejected'=>'dark','cancelled'=>'secondary'];
                                    ?>
                                    <span class="badge bg-<?= $colors[$n['status']] ?? 'secondary' ?> px-3 py-2"><?= ucfirst($n['status']) ?></span>
                                </td>
                                <td><?= date('d M Y', strtotime($n['created_at'])) ?></td>
                                <td><a href="<?= BASE_URL ?>/admin/noc-registry/nocs/<?= $n['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i></a></td>
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
