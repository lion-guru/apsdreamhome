<?php $page_title = $page_title ?? 'Document Classification'; ?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><i class="fas fa-tag me-2"></i>Document Classification</h1>
        <a href="<?= BASE_URL ?>/admin/documents" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
    </div>

    <div class="card shadow-sm">
        <div class="card-header aps-cp-card-header"><i class="fas fa-list me-2"></i>Classification Records</div>
        <div class="card-body aps-cp-card-body">
            <?php if (!empty($records)): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr><th>ID</th><th>Document</th><th>Classification Type</th><th>Classification Value</th><th>Classified By</th><th>Date</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($records as $r): ?>
                                <tr>
                                    <td><?= (int)$r['id'] ?></td>
                                    <td><a href="<?= BASE_URL ?>/admin/documents/show/<?= (int)$r['document_id'] ?>"><?= htmlspecialchars($r['document_title'] ?? '-') ?></a></td>
                                    <td><span class="badge bg-info"><?= htmlspecialchars($r['classification_type'] ?? '-') ?></span></td>
                                    <td><?= htmlspecialchars($r['classification_value'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($r['classified_by_name'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($r['created_at'] ?? '-') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-tags fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No classification records found.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
