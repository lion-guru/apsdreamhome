<?php $pageTitle = $page_title ?? 'Property Provenance'; ?>
<div class="container-fluid py-4">
    <h4 class="mb-4"><i class="fas fa-tree me-2"></i><?= htmlspecialchars($pageTitle) ?></h4>
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-header bg-transparent"><h5 class="mb-0"><i class="fas fa-building me-2"></i>Property</h5></div>
        <div class="card-body aps-cp-card-body">
            <p class="mb-1"><strong><?= htmlspecialchars($property['title'] ?? '-') ?></strong></p>
            <p class="mb-0 text-muted"><?= htmlspecialchars($property['city'] ?? '') ?></p>
        </div>
    </div>
    <div class="row g-3">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent"><h5 class="mb-0"><i class="fas fa-users me-2"></i>Ownership History</h5></div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <div class="table-responsive"><table class="table table-hover mb-0 table-responsive">
                            <thead class="table-light"><tr><th>From</th><th>To</th><th>Reason</th><th>Date</th></tr></thead>
                            <tbody>
                                <?php if (!empty($ownership_history)): ?>
                                    <?php foreach ($ownership_history as $oh): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($oh['previous_owner_name'] ?? '-') ?></td>
                                            <td><?= htmlspecialchars($oh['new_owner_name'] ?? '-') ?></td>
                                            <td><?= htmlspecialchars($oh['transfer_reason'] ?? '-') ?></td>
                                            <td><?= htmlspecialchars($oh['created_at'] ?? '-') ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="4" class="text-center text-muted py-3">No ownership history available</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent"><h5 class="mb-0"><i class="fas fa-file-alt me-2"></i>Document History</h5></div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <div class="table-responsive"><table class="table table-hover mb-0 table-responsive">
                            <thead class="table-light"><tr><th>Document</th><th>Uploaded By</th><th>Date</th></tr></thead>
                            <tbody>
                                <?php if (!empty($document_history)): ?>
                                    <?php foreach ($document_history as $dh): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($dh['document_type'] ?? '-') ?></td>
                                            <td><?= htmlspecialchars($dh['uploaded_by_name'] ?? '-') ?></td>
                                            <td><?= htmlspecialchars($dh['uploaded_date'] ?? '-') ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="3" class="text-center text-muted py-3">No document history available</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
