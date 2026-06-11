<?php $page_title = $page_title ?? 'OCR Documents'; ?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><i class="fas fa-eye me-2"></i>OCR Documents</h1>
        <a href="<?= BASE_URL ?>/admin/documents" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
    </div>

    <div class="card shadow-sm">
        <div class="card-header aps-cp-card-header"><i class="fas fa-list me-2"></i>All OCR Processed Documents</div>
        <div class="card-body aps-cp-card-body">
            <?php if (!empty($records)): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr><th>ID</th><th>Original Document</th><th>Confidence</th><th>Processed By</th><th>Processed At</th><th>Date</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($records as $r): ?>
                                <tr>
                                    <td><?= (int)$r['id'] ?></td>
                                    <td><a href="<?= BASE_URL ?>/admin/documents/show/<?= (int)($r['original_document_id'] ?? 0) ?>"><?= htmlspecialchars($r['original_document_title'] ?? 'ID: ' . ($r['original_document_id'] ?? 0)) ?></a></td>
                                    <td>
                                        <?php if (isset($r['confidence_score'])): ?>
                                            <span class="badge bg-<?= $r['confidence_score'] >= 80 ? 'success' : ($r['confidence_score'] >= 50 ? 'warning' : 'danger') ?>">
                                                <?= number_format($r['confidence_score'], 1) ?>%
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($r['processed_by'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($r['processed_at'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($r['created_at'] ?? '-') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-eye fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No OCR processed documents found.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
