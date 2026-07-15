<?php
$stats = $stats ?? [];
$categories = $categories ?? [];
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="fas fa-file-contract me-2 text-primary"></i>Legal Documentation Management</h2>
        <div>
            <a href="<?= BASE_URL ?>/admin/legal/ai-composer" class="btn btn-outline-info btn-sm me-1"><i class="fas fa-robot me-1"></i>AI Composer</a>
            <a href="<?= BASE_URL ?>/admin/legal/documents/create" class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i>New Document</a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="aps-cp-card">
                <div class="aps-cp-card-body text-center py-3">
                    <div class="fs-2 text-primary fw-bold"><?= $stats['total_documents'] ?? 0 ?></div>
                    <div class="text-muted small">Total Documents</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="aps-cp-card">
                <div class="aps-cp-card-body text-center py-3">
                    <div class="fs-2 text-success fw-bold"><?= $stats['signed_documents'] ?? 0 ?></div>
                    <div class="text-muted small">Signed</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="aps-cp-card">
                <div class="aps-cp-card-body text-center py-3">
                    <div class="fs-2 text-warning fw-bold"><?= $stats['active_templates'] ?? 0 ?></div>
                    <div class="text-muted small">Active Templates</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="aps-cp-card">
                <div class="aps-cp-card-body text-center py-3">
                    <div class="fs-2 text-danger fw-bold"><?= $stats['pending_kyc'] ?? 0 ?></div>
                    <div class="text-muted small">Pending KYC</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-8">
            <div class="aps-cp-card">
                <div class="aps-cp-card-header"><i class="fas fa-clock me-2"></i>Recent Documents</div>
                <div class="aps-cp-card-body p-0">
                    <?php $recent = $stats['recent_documents'] ?? []; ?>
                    <?php if (empty($recent)): ?>
                        <div class="text-center text-muted py-4"><i class="fas fa-inbox me-2"></i>No documents yet</div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead><tr><th>#</th><th>Title</th><th>Customer</th><th>Status</th><th>Created</th></tr></thead>
                                <tbody>
                                <?php foreach ($recent as $d): ?>
                                    <tr>
                                        <td><a href="<?= BASE_URL ?>/admin/legal/documents/<?= $d['id'] ?>"><?= htmlspecialchars($d['document_number'] ?? '-') ?></a></td>
                                        <td><?= htmlspecialchars($d['title']) ?></td>
                                        <td><?= htmlspecialchars($d['customer_name'] ?? '-') ?></td>
                                        <td><span class="badge bg-<?= match($d['status']) { 'signed' => 'success', 'final' => 'info', 'draft' => 'secondary', 'expired' => 'warning', 'cancelled' => 'danger', default => 'secondary' } ?>"><?= $d['status'] ?></span></td>
                                        <td class="small"><?= date('d M Y', strtotime($d['created_at'])) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="aps-cp-card">
                <div class="aps-cp-card-header"><i class="fas fa-folder me-2"></i>Documents by Category</div>
                <div class="aps-cp-card-body">
                    <?php $byCat = $stats['documents_by_category'] ?? []; ?>
                    <?php if (empty($byCat)): ?>
                        <div class="text-muted small">No data yet</div>
                    <?php else: ?>
                        <?php foreach ($byCat as $c): ?>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span><i class="<?= htmlspecialchars($c['icon'] ?? 'fas fa-folder') ?> me-2 text-muted"></i><?= htmlspecialchars($c['name']) ?></span>
                                <span class="badge bg-primary rounded-pill"><?= (int)$c['count'] ?></span>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            <div class="aps-cp-card mt-3">
                <div class="aps-cp-card-header"><i class="fas fa-tasks me-2"></i>Quick Actions</div>
                <div class="aps-cp-card-body">
                    <div class="d-grid gap-2">
                        <a href="<?= BASE_URL ?>/admin/legal/templates" class="btn btn-outline-primary btn-sm"><i class="fas fa-file me-1"></i>Templates</a>
                        <a href="<?= BASE_URL ?>/admin/legal/clauses" class="btn btn-outline-success btn-sm"><i class="fas fa-list me-1"></i>Clause Library</a>
                        <a href="<?= BASE_URL ?>/admin/legal/categories" class="btn btn-outline-secondary btn-sm"><i class="fas fa-tags me-1"></i>Categories</a>
                        <a href="<?= BASE_URL ?>/admin/legal/ai-prompts" class="btn btn-outline-info btn-sm"><i class="fas fa-brain me-1"></i>AI Prompts</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
