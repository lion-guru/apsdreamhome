<?php
$documents = $documents ?? [];
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">My Documents</h1>
        <a href="/documents/upload" class="btn btn-primary"><i class="fas fa-upload"></i> Upload Document</a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Document Name</th>
                            <th>Type</th>
                            <th>Upload Date</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($documents)): ?>
                            <tr><td colspan="5" class="text-center text-muted py-4">No documents uploaded yet.</td></tr>
                        <?php else: ?>
                            <?php foreach ($documents as $d): ?>
                                <tr>
                                    <td>
                                        <i class="fas fa-file me-1 text-muted"></i>
                                        <?= htmlspecialchars($d['name'] ?? '') ?>
                                    </td>
                                    <td><span class="badge bg-secondary"><?= htmlspecialchars($d['type'] ?? '') ?></span></td>
                                    <td><?= htmlspecialchars($d['upload_date'] ?? '') ?></td>
                                    <td>
                                        <?php $st = $d['status'] ?? ''; ?>
                                        <?php if ($st === 'Verified'): ?>
                                            <span class="badge bg-success">Verified</span>
                                        <?php elseif ($st === 'Pending'): ?>
                                            <span class="badge bg-warning text-dark">Pending</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary"><?= htmlspecialchars($st) ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <a href="/documents/<?= $d['id'] ?? 0 ?>" class="btn btn-sm btn-outline-primary" title="View"><i class="fas fa-eye"></i></a>
                                        <a href="/documents/<?= $d['id'] ?? 0 ?>/download" class="btn btn-sm btn-outline-success" title="Download"><i class="fas fa-download"></i></a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
