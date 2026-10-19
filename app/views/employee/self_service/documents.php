<?php
/** Employee Self-Service - Documents */

$page_title = $page_title ?? 'My Documents';
$documents = $documents ?? [];
$categories = $categories ?? [];
?>

<style nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
.doc-row { transition: background 0.15s; }
.doc-row:hover { background: #fefcf6; }
.status-badge { font-size: 0.7rem; padding: 0.2rem 0.5rem; border-radius: 999px; }
</style>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold"><i class="fas fa-folder-open me-2 text-primary"></i>My Documents</h4>
            <p class="text-muted mb-0 small">View and manage your personal documents</p>
        </div>
        <a href="<?= BASE_URL ?>/employee/self-service/documents/upload" class="btn btn-primary">
            <i class="fas fa-upload me-1"></i> Upload Document
        </a>
    </div>

    <?php if (empty($documents)): ?>
    <div class="card shadow-sm border-0">
        <div class="card-body text-center py-5">
            <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
            <h5 class="text-muted">No Documents</h5>
            <p class="text-muted small mb-3">Upload your first document</p>
            <a href="<?= BASE_URL ?>/employee/self-service/documents/upload" class="btn btn-primary"><i class="fas fa-upload me-1"></i> Upload Document</a>
        </div>
    </div>
    <?php else: ?>
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 120px">Doc #</th>
                            <th>Category</th>
                            <th>Name</th>
                            <th style="width: 120px">Issue Date</th>
                            <th style="width: 120px">Expiry</th>
                            <th style="width: 120px">Status</th>
                            <th style="width: 150px">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($documents as $doc): 
                            $statusClass = [
                                'not_verified' => 'secondary',
                                'pending' => 'warning',
                                'verified' => 'success',
                                'rejected' => 'danger',
                                'expired' => 'danger',
                            ][$doc['verification_status']] ?? 'secondary';
                            
                            $isExpiring = $doc['expiry_date'] && $doc['expiry_date'] < date('Y-m-d');
                            $isExpiringSoon = $doc['expiry_date'] && $doc['expiry_date'] <= date('Y-m-d', strtotime('+30 days')) && !$isExpiring;
                        ?>
                        <tr class="doc-row">
                            <td><code class="small"><?= htmlspecialchars($doc['document_number']) ?></code></td>
                            <td>
                                <span class="badge bg-light text-dark" style="background: #f1f5f9; color: #475569;">
                                    <?= htmlspecialchars($doc['category_name'] ?? '—') ?>
                                </span>
                            </td>
                            <td>
                                <div class="fw-semibold"><?= htmlspecialchars($doc['document_name']) ?></div>
                                <div class="small text-muted"><?= htmlspecialchars($doc['file_name'] ?? '') ?></div>
                            </td>
                            <td><?= $doc['issue_date'] ? date('d M Y', strtotime($doc['issue_date'])) : '—' ?></td>
                            <td>
                                <?= $doc['expiry_date'] ? date('d M Y', strtotime($doc['expiry_date'])) : '—' ?>
                                <?php if ($isExpiring): ?><span class="badge bg-danger ms-1">Expired</span><?php endif; ?>
                                <?php if ($isExpiringSoon): ?><span class="badge bg-warning ms-1">Expiring Soon</span><?php endif; ?>
                            </td>
                            <td>
                                <span class="badge bg-<?= $statusClass ?> status-badge">
                                    <?= ucfirst(str_replace('_', ' ', $doc['verification_status'])) ?>
                                </span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="<?= BASE_URL ?>/employee/self-service/documents/<?= $doc['id'] ?>" class="btn btn-outline-primary" title="View"><i class="fas fa-eye"></i></a>
                                    <?php if ($doc['verification_status'] !== 'verified'): ?>
                                        <a href="<?= BASE_URL ?>/employee/self-service/documents/<?= $doc['id'] ?>/verify" class="btn btn-outline-success" title="Request Verification"><i class="fas fa-check"></i></a>
                                    <?php endif; ?>
                                    <a href="<?= BASE_URL ?>/employee/self-service/documents/<?= $doc['id'] ?>/download" class="btn btn-outline-info" title="Download"><i class="fas fa-download"></i></a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>