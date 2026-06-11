<?php $res = $resources ?? []; $tools = $res['security_tools'] ?? []; $docs = $res['documentation'] ?? []; $training = $res['training_materials'] ?? []; ?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="fas fa-boxes me-2"></i>Security Resources & Tools</h4>
    </div>
    <div class="row g-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-bottom"><h5 class="mb-0"><i class="fas fa-wrench text-primary me-2"></i>Security Tools</h5></div>
                <div class="card-body aps-cp-card-body">
                    <?php if (!empty($tools)): foreach ($tools as $k => $v): ?>
                        <div class="mb-3 p-3 bg-light rounded">
                            <h6 class="fw-bold"><?= ucwords(str_replace('_', ' ', $k)) ?></h6>
                            <p class="text-muted small mb-0"><?= htmlspecialchars($v) ?></p>
                        </div>
                    <?php endforeach; else: ?><p class="text-muted mb-0">No tools listed</p><?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-bottom"><h5 class="mb-0"><i class="fas fa-file-lines text-warning me-2"></i>Documentation</h5></div>
                <div class="card-body aps-cp-card-body">
                    <?php if (!empty($docs)): foreach ($docs as $k => $v): ?>
                        <div class="mb-3 p-3 bg-light rounded">
                            <h6 class="fw-bold"><?= ucwords(str_replace('_', ' ', $k)) ?></h6>
                            <p class="text-muted small mb-0"><?= htmlspecialchars($v) ?></p>
                        </div>
                    <?php endforeach; else: ?><p class="text-muted mb-0">No documentation</p><?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-bottom"><h5 class="mb-0"><i class="fas fa-graduation-cap text-success me-2"></i>Training Materials</h5></div>
                <div class="card-body aps-cp-card-body">
                    <?php if (!empty($training)): foreach ($training as $k => $v): ?>
                        <div class="mb-3 p-3 bg-light rounded">
                            <h6 class="fw-bold"><?= ucwords(str_replace('_', ' ', $k)) ?></h6>
                            <p class="text-muted small mb-0"><?= htmlspecialchars($v) ?></p>
                        </div>
                    <?php endforeach; else: ?><p class="text-muted mb-0">No training materials</p><?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
