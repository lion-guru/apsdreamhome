<?php $pageTitle = $page_title ?? 'Edge Computing Security'; ?>
<div class="container-fluid py-4">
    <h4 class="mb-4"><i class="fas fa-shield-alt me-2"></i><?= htmlspecialchars($pageTitle) ?></h4>
    <?php $sd = $security_data ?? []; ?>
    <div class="row g-3">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent"><h5 class="mb-0"><i class="fas fa-lock me-2"></i>Edge Security Measures</h5></div>
                <div class="card-body aps-cp-card-body">
                    <div class="table-responsive"><table class="table table-sm table-responsive">
                        <?php foreach (($sd['edge_security_measures'] ?? []) as $k => $v): ?>
                            <tr><th class="w-50 text-capitalize"><?= htmlspecialchars(str_replace('_', ' ', $k)) ?></th><td><?= htmlspecialchars(is_array($v) ? ($v['coverage'] ?? $v['method'] ?? '-') : '-') ?></td></tr>
                        <?php endforeach; ?>
                    </table></div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent"><h5 class="mb-0"><i class="fas fa-user-shield me-2"></i>Data Protection</h5></div>
                <div class="card-body aps-cp-card-body">
                    <div class="table-responsive"><table class="table table-sm table-responsive">
                        <?php foreach (($sd['data_protection'] ?? []) as $k => $v): ?>
                            <tr><th class="w-50 text-capitalize"><?= htmlspecialchars(str_replace('_', ' ', $k)) ?></th><td><?= htmlspecialchars($v) ?></td></tr>
                        <?php endforeach; ?>
                    </table></div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent"><h5 class="mb-0"><i class="fas fa-file-contract me-2"></i>Compliance Features</h5></div>
                <div class="card-body aps-cp-card-body">
                    <div class="table-responsive"><table class="table table-sm table-responsive">
                        <?php foreach (($sd['compliance_features'] ?? []) as $k => $v): ?>
                            <tr><th class="w-50 text-capitalize"><?= htmlspecialchars(str_replace('_', ' ', $k)) ?></th><td><?= htmlspecialchars($v) ?></td></tr>
                        <?php endforeach; ?>
                    </table></div>
                </div>
            </div>
        </div>
    </div>
</div>
