<?php $page_title = $page_title ?? 'Lead Segments'; ?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h2 class="mb-1 fw-bold"><i class="fas fa-layer-group me-2 text-primary"></i>Lead Segments</h2>
            <p class="text-muted mb-0">Group leads by criteria for targeted outreach</p>
        </div>
        <button class="btn btn-primary" onclick="new bootstrap.Modal(document.getElementById('segmentModal')).show()"><i class="fas fa-plus me-1"></i> New Segment</button>
    </div>


    <?php if (empty($segments)): ?>
        <div class="text-center py-5 bg-white rounded shadow-sm">
            <i class="fas fa-layer-group fa-3x text-muted mb-3"></i>
            <h5 class="text-muted">No segments created yet</h5>
            <p class="text-muted">Create segments to group leads by status, source, score, or budget</p>
            <button class="btn btn-primary" onclick="new bootstrap.Modal(document.getElementById('segmentModal')).show()"><i class="fas fa-plus me-1"></i> Create First Segment</button>
        </div>
    <?php else: ?>
        <div class="row g-3">
            <?php
            $colors = ['primary','success','warning','danger','info','info','danger','dark'];
            foreach ($segments as $i => $seg):
                $color = $colors[$i % count($colors)];
                $criteria = json_decode($seg['filter_criteria'] ?? '{}', true) ?? [];
            ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="style-60772">
                                        <i class="fas fa-<?= $color === 'primary' ? 'users' : ($color === 'success' ? 'check-circle' : ($color === 'warning' ? 'star' : 'filter')) ?> text-<?= $color ?>"></i>
                                    </div>
                                    <div>
                                        <h6 class="fw-bold mb-0"><?= htmlspecialchars($seg['name']) ?></h6>
                                        <small class="text-muted"><?= htmlspecialchars($seg['description'] ?? '') ?></small>
                                    </div>
                                </div>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-light" data-bs-toggle="dropdown"><i class="fas fa-ellipsis-v"></i></button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li><a class="dropdown-item" href="<?= BASE_URL ?>/admin/crm/segments/<?= $seg['id'] ?>/leads"><i class="fas fa-eye me-2"></i>View Leads</a></li>
                                        <li>
                                            <form method="POST" action="<?= BASE_URL ?>/admin/crm/bulk-send" class="d-inline">
                                                <input type="hidden" name="segment_id" value="<?= $seg['id'] ?>">
                                                <button class="dropdown-item"><i class="fas fa-paper-plane me-2"></i>Bulk Send</button>
                                            </form>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <form method="POST" action="<?= BASE_URL ?>/admin/crm/segments/<?= $seg['id'] ?>/delete" onsubmit="return confirm('Delete this segment?')">
                                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                                <button class="dropdown-item text-danger"><i class="fas fa-trash me-2"></i>Delete</button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="mt-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="style-60668"><?= (int)$seg['lead_count'] ?></span>
                                    <small class="text-muted">leads matched</small>
                                </div>
                                <div class="d-flex flex-wrap gap-1 mt-2">
                                    <?php foreach ($criteria as $k => $v): ?>
                                        <span class="badge bg-light text-dark"><?= ucfirst(str_replace('_',' ',$k)) ?>: <?= htmlspecialchars(is_array($v) ? json_encode($v) : $v) ?></span>
                                    <?php endforeach; ?>
                                    <?php if (empty($criteria)): ?>
                                        <span class="badge bg-light text-muted">All leads</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Create Segment Modal -->
<div class="modal fade" id="segmentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-layer-group me-2"></i>Create Segment</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="<?= BASE_URL ?>/admin/crm/segments/store">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Segment Name *</label>
                            <input type="text" class="form-control" name="name" required placeholder="e.g. High-Value Leads">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Description</label>
                            <input type="text" class="form-control" name="description" placeholder="Brief description">
                        </div>
                        <div class="col-12"><hr><h6 class="fw-bold text-muted">Filter Criteria</h6></div>
                        <div class="col-md-4">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="status">
                                <option value="">Any</option>
                                <?php foreach (['new','contacted','qualified','site_visit','proposal','negotiation','booking','won','lost','nurture'] as $s): ?>
                                    <option value="<?= $s ?>"><?= ucfirst(str_replace('_',' ',$s)) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Source</label>
                            <select class="form-select" name="source">
                                <option value="">Any</option>
                                <?php foreach (['website','google_ads','facebook','referral','walk_in','phone','email','social_media','event','other'] as $s): ?>
                                    <option value="<?= $s ?>"><?= ucfirst(str_replace('_',' ',$s)) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">City</label>
                            <input type="text" class="form-control" name="city" placeholder="e.g. Noida">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Min Score</label>
                            <input type="number" class="form-control" name="min_score" min="0" max="100" placeholder="0">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Min Budget</label>
                            <input type="number" class="form-control" name="min_budget" placeholder="e.g. 1000000">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Max Budget</label>
                            <input type="number" class="form-control" name="max_budget" placeholder="e.g. 5000000">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Create Segment</button>
                </div>
            </form>
        </div>
    </div>
</div>
