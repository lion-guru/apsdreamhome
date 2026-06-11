<?php $vd = $vision_data ?? []; $landscape = $vd['2030_security_landscape'] ?? []; $aps_vision = $vd['aps_security_vision'] ?? []; $tech = $vd['technology_innovations'] ?? []; ?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="fas fa-eye me-2"></i>Security Future Vision</h4>
    </div>
    <div class="row g-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-danger text-white"><h5 class="mb-0"><i class="fas fa-triangle-exclamation me-2"></i>2030 Security Landscape</h5></div>
                <div class="card-body aps-cp-card-body">
                    <?php if (!empty($landscape)): foreach ($landscape as $k => $v): ?>
                        <div class="mb-3 p-3 bg-light rounded">
                            <h6 class="fw-bold text-danger"><?= ucwords(str_replace('_', ' ', $k)) ?></h6>
                            <p class="text-muted small mb-0"><?= htmlspecialchars($v) ?></p>
                        </div>
                    <?php endforeach; else: ?><p class="text-muted mb-0">No data</p><?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-success text-white"><h5 class="mb-0"><i class="fas fa-bullseye me-2"></i>APS Security Vision</h5></div>
                <div class="card-body aps-cp-card-body">
                    <?php if (!empty($aps_vision)): foreach ($aps_vision as $k => $v): ?>
                        <div class="mb-3 p-3 bg-light rounded">
                            <h6 class="fw-bold text-success"><?= ucwords(str_replace('_', ' ', $k)) ?></h6>
                            <p class="text-muted small mb-0"><?= htmlspecialchars($v) ?></p>
                        </div>
                    <?php endforeach; else: ?><p class="text-muted mb-0">No data</p><?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-primary text-white"><h5 class="mb-0"><i class="fas fa-lightbulb me-2"></i>Technology Innovations</h5></div>
                <div class="card-body aps-cp-card-body">
                    <?php if (!empty($tech)): foreach ($tech as $k => $v): ?>
                        <div class="mb-3 p-3 bg-light rounded">
                            <h6 class="fw-bold text-primary"><?= ucwords(str_replace('_', ' ', $k)) ?></h6>
                            <p class="text-muted small mb-0"><?= htmlspecialchars($v) ?></p>
                        </div>
                    <?php endforeach; else: ?><p class="text-muted mb-0">No data</p><?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
