<?php $page_title = 'Interior Design Services'; ?>
<div class="container-fluid py-4">
    <h2 class="mb-4"><i class="fas fa-couch me-2"></i>Interior Design Services</h2>
    <div class="row mb-4">
        <div class="col-md-4"><div class="card border-0 shadow-sm"><div class="card-body text-center"><h3><?= $count ?></h3><small class="text-muted">Total Inquiries</small></div></div></div>
        <div class="col-md-4"><div class="card border-0 shadow-sm"><div class="card-body text-center"><h3 class="text-warning"><?= $pending ?></h3><small class="text-muted">Pending</small></div></div></div>
    </div>
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white"><h6 class="mb-0"><i class="fas fa-list me-2"></i>Interior Design Inquiries</h6></div>
        <div class="card-body p-0">
            <?php if (empty($services)): ?>
                <p class="text-muted text-center py-4">No interior design inquiries yet</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead><tr><th>#</th><th>Customer</th><th>Phone</th><th>Email</th><th>Status</th><th>Notes</th><th>Date</th></tr></thead>
                        <tbody>
                        <?php foreach ($services as $s): ?>
                            <tr>
                                <td><?= $s['id'] ?></td>
                                <td><?= htmlspecialchars($s['customer_name'] ?? $s['name'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($s['customer_phone'] ?? $s['phone'] ?? '') ?></td>
                                <td><?= htmlspecialchars($s['customer_email'] ?? $s['email'] ?? '') ?></td>
                                <td><span class="badge bg-<?= ($s['status'] ?? 'pending')==='resolved'?'success':(($s['status'] ?? 'pending')==='in_progress'?'info':'warning') ?>"><?= ucfirst($s['status'] ?? 'pending') ?></span></td>
                                <td><small><?= htmlspecialchars(substr($s['notes'] ?? '', 0, 40)) ?></small></td>
                                <td><?= date('d M Y', strtotime($s['created_at'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
