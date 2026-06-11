<?php $page_title = $page_title ?? 'Customer Documents'; ?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><i class="fas fa-user me-2"></i>Customer Documents</h1>
        <a href="<?= BASE_URL ?>/admin/documents" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
    </div>

    <div class="card shadow-sm">
        <div class="card-header aps-cp-card-header"><i class="fas fa-list me-2"></i>All Customer Documents</div>
        <div class="card-body aps-cp-card-body">
            <?php if (!empty($records)): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr><th>ID</th><th>Customer</th><th>Document Type</th><th>Document Name</th><th>Uploaded By</th><th>Verification</th><th>Date</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($records as $r): ?>
                                <tr>
                                    <td><?= (int)$r['id'] ?></td>
                                    <td><?= htmlspecialchars($r['customer_name'] ?? 'ID: ' . ($r['customer_id'] ?? 0)) ?></td>
                                    <td><span class="badge bg-info"><?= htmlspecialchars($r['document_type'] ?? '-') ?></span></td>
                                    <td><?= htmlspecialchars($r['document_name'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($r['uploaded_by_name'] ?? '-') ?></td>
                                    <td><span class="badge bg-<?= ($r['verification_status'] ?? 'unverified') === 'verified' ? 'success' : 'warning' ?>"><?= htmlspecialchars($r['verification_status'] ?? 'unverified') ?></span></td>
                                    <td><?= htmlspecialchars($r['created_at'] ?? '-') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-user fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No customer documents found.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
