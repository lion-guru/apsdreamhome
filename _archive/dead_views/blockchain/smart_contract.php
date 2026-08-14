<?php $pageTitle = $page_title ?? 'Smart Contract Information'; ?>
<div class="container-fluid py-4">
    <h4 class="mb-4"><i class="fas fa-file-contract me-2"></i><?= htmlspecialchars($pageTitle) ?></h4>
    <?php $ci = $contract_info ?? []; ?>
    <div class="row g-3">
        <div class="col-md-5">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent"><h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Contract Details</h5></div>
                <div class="card-body aps-cp-card-body">
                    <div class="table-responsive"><table class="table table-sm table-responsive">
                        <tr><th>Name</th><td><?= htmlspecialchars($ci['name'] ?? '-') ?></td></tr>
                        <tr><th>Address</th><td class="style-85847"><code><?= htmlspecialchars($ci['address'] ?? '-') ?></code></td></tr>
                        <tr><th>Network</th><td><span class="badge bg-info"><?= htmlspecialchars($ci['network'] ?? '-') ?></span></td></tr>
                    </table></div>
                </div>
            </div>
        </div>
        <div class="col-md-7">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent"><h5 class="mb-0"><i class="fas fa-list me-2"></i>Contract Functions</h5></div>
                <div class="card-body aps-cp-card-body">
                    <div class="table-responsive">
                        <div class="table-responsive"><table class="table table-hover table-responsive">
                            <thead><tr><th>Function</th><th>Description</th></tr></thead>
                            <tbody>
                                <?php foreach (($ci['functions'] ?? []) as $func => $desc): ?>
                                    <tr><td><code><?= htmlspecialchars($func) ?></code></td><td><?= htmlspecialchars($desc) ?></td></tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
