<?php $pageTitle = $page_title ?? 'Document Verification'; ?>
<div class="container-fluid py-4">
    <h4 class="mb-4"><i class="fas fa-file-shield me-2"></i><?= htmlspecialchars($pageTitle) ?></h4>
    <div class="row g-3">
        <div class="col-md-5">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent"><h5 class="mb-0"><i class="fas fa-building me-2"></i>Property</h5></div>
                <div class="card-body aps-cp-card-body">
                    <p class="mb-1"><strong><?= htmlspecialchars($property['title'] ?? '-') ?></strong></p>
                    <p class="text-muted"><?= htmlspecialchars($property['city'] ?? '') ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-7">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent"><h5 class="mb-0"><i class="fas fa-file me-2"></i>Documents</h5></div>
                <div class="card-body aps-cp-card-body">
                    <div class="table-responsive">
                        <div class="table-responsive"><table class="table table-hover table-responsive">
                            <thead><tr><th>Document Type</th><th>Hash</th><th>Status</th></tr></thead>
                            <tbody>
                                <?php foreach (($documents ?? []) as $type => $hash): ?>
                                    <tr>
                                        <td class="text-capitalize"><?= htmlspecialchars(str_replace('_', ' ', $type)) ?></td>
                                        <td class="style-85847"><code><?= htmlspecialchars($hash) ?></code></td>
                                        <td><span class="badge bg-success">Ready</span></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
