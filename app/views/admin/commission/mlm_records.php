<?php $base = defined('BASE_URL') ? BASE_URL : '/' . trim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/'); ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4><i class="fas fa-database"></i> MLM Commission Records</h4>
        <a href="<?= BASE_URL ?>/admin/commission" class="btn btn-sm btn-secondary"><i class="fas fa-arrow-left"></i> Back</a>
    </div>

    <div class="card aps-cp-card">
        <div class="card-body p-0">
            <div class="table-responsive"><table class="table table-striped mb-0">
                <thead><tr><th>#</th><th>Associate</th><th>Customer</th><th>Booking Amt</th><th>Total Comm.</th><th>Status</th><th>Created</th><th>Action</th></tr></thead>
                <tbody>
                    <?php if (empty($records ?? [])): ?>
                    <tr>
                        <td colspan="8" class="text-center py-5">
                            <i class="fas fa-database fa-3x text-muted mb-3" class="style-82835"></i>
                            <h5 class="text-muted">No commission records found</h5>
                            <p class="text-muted mb-3">Commission records are generated automatically when bookings are confirmed and payments are processed.</p>
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($records as $r): ?>
                    <tr>
                        <td><?= $r['id'] ?></td>
                        <td><?= htmlspecialchars($r['associate_name'] ?? 'N/A') ?></td>
                        <td>#<?= (int)$r['customer_id'] ?></td>
                        <td>&#8377;<?= number_format((float)$r['booking_amount']) ?></td>
                        <td><strong>&#8377;<?= number_format((float)$r['total_commission'],2) ?></strong></td>
                        <td>
                            <?php $c = $r['status']=='paid'?'success':($r['status']=='approved'?'primary':($r['status']=='cancelled'?'danger':'warning')); ?>
                            <span class="badge bg-<?= $c ?>"><?= $r['status'] ?></span>
                        </td>
                        <td><?= date('d-m-Y', strtotime($r['created_at'])) ?></td>
                        <td>
                            <?php if ($r['status'] == 'calculated'): ?>
                            <form method="POST" action="<?= BASE_URL ?>/admin/commission/mlm/records/status/<?= $r['id'] ?>" class="d-inline" onsubmit="return confirm('Approve record #<?= $r['id'] ?>?')">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                <input type="hidden" name="status" value="approved">
                                <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-check"></i></button>
                            </form>
                            <?php elseif ($r['status'] == 'approved'): ?>
                            <form method="POST" action="<?= BASE_URL ?>/admin/commission/mlm/records/status/<?= $r['id'] ?>" class="d-inline" onsubmit="return confirm('Mark #<?= $r['id'] ?> as paid?')">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                <input type="hidden" name="status" value="paid">
                                <button type="submit" class="btn btn-sm btn-success"><i class="fas fa-money-bill"></i></button>
                            </form>
                            <?php endif; ?>
                            <?php if ($r['commission_details']): ?>
                            <button class="btn btn-sm btn-info view-details-btn" data-details="<?= htmlspecialchars($r['commission_details'], ENT_QUOTES, 'UTF-8') ?>"><i class="fas fa-eye"></i></button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table></div>
        </div>
    </div>
</div>

<!-- Commission Details Modal -->
<div class="modal fade" id="commissionDetailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Commission Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="commissionDetailsBody">
                <pre class="bg-light p-3 rounded" class="style-34341"></pre>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modal = new bootstrap.Modal(document.getElementById('commissionDetailsModal'));
    const modalBody = document.querySelector('#commissionDetailsBody pre');
    document.querySelectorAll('.view-details-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            modalBody.textContent = this.getAttribute('data-details');
            modal.show();
        });
    });
});
</script>

