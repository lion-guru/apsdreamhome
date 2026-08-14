<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0"><i class="fas fa-seedling me-2"></i> OLN - Online Lead Nurturing</h3>
        <div>
            <a href="<?= BASE_URL ?>/admin/voice-users/dashboard" class="btn btn-outline-primary">
                <i class="fas fa-arrow-left"></i> Dashboard
            </a>
        </div>
    </div>

    <!-- Funnel Metrics -->
    <div class="row mb-4">
        <div class="col-md-3 col-6 mb-3">
            <div class="card border-primary">
                <div class="card-body text-center py-3">
                    <h6 class="text-primary small">Leads in Pipeline</h6>
                    <h3 class="mb-0 fw-bold text-primary"><?= $total_pipeline ?? 0 ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6 mb-3">
            <div class="card border-success">
                <div class="card-body text-center py-3">
                    <h6 class="text-success small">Conversion Rate</h6>
                    <h3 class="mb-0 fw-bold text-success"><?= $conversion_rate ?? 0 ?>%</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6 mb-3">
            <div class="card border-warning">
                <div class="card-body text-center py-3">
                    <h6 class="text-warning small">Nurture Queue</h6>
                    <h3 class="mb-0 fw-bold text-warning"><?= $stage_nurture ?? 0 ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6 mb-3">
            <div class="card border-info">
                <div class="card-body text-center py-3">
                    <h6 class="text-info small">Closed Won</h6>
                    <h3 class="mb-0 fw-bold text-info"><?= $stage_closed_won ?? 0 ?></h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Pipeline Kanban -->
    <div class="row mb-4 flex-nowrap" class="style-79146">
        <!-- New -->
        <div class="col" class="style-55803">
            <div class="card h-100">
                <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center">
                    <span class="fw-bold small">New</span>
                    <span class="badge bg-primary"><?= $stage_new ?? 0 ?></span>
                </div>
                <div class="card-body p-2" class="style-90270">
                    <?php foreach (array_filter($leads ?? [], function($l) { return ($l['status'] ?? '') === 'new'; }) as $lead): ?>
                    <div class="card mb-2 border-start border-3 border-primary lead-card" class="style-78508" data-bs-toggle="modal" data-bs-target="#leadJourneyModal"
                        data-id="<?= $lead['id'] ?>"
                        data-name="<?= htmlspecialchars($lead['name'] ?? '') ?>"
                        data-phone="<?= htmlspecialchars($lead['phone'] ?? '') ?>"
                        data-property="<?= htmlspecialchars($lead['property_interest'] ?? '') ?>"
                        data-budget="<?= htmlspecialchars($lead['budget'] ?? '') ?>"
                        data-status="<?= $lead['status'] ?? '' ?>"
                        data-score="<?= $lead['lead_score'] ?? 0 ?>"
                        onclick="showLeadJourney(this)">
                        <div class="card-body p-2">
                            <h6 class="mb-1 small"><?= htmlspecialchars($lead['name'] ?? 'Unknown') ?></h6>
                            <small class="text-muted d-block"><?= htmlspecialchars($lead['property_interest'] ?? '-') ?></small>
                            <div class="d-flex justify-content-between mt-1">
                                <small class="text-muted">Score: <?= (int)($lead['lead_score'] ?? 0) ?></small>
                                <small class="text-muted"><?= date('d M', strtotime($lead['created_at'] ?? 'now')) ?></small>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php if (!array_filter($leads ?? [], function($l) { return ($l['status'] ?? '') === 'new'; })): ?>
                    <p class="text-muted small text-center py-3">No leads</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Contacted -->
        <div class="col" class="style-55803">
            <div class="card h-100">
                <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center">
                    <span class="fw-bold small">Contacted</span>
                    <span class="badge bg-info"><?= $stage_contacted ?? 0 ?></span>
                </div>
                <div class="card-body p-2" class="style-90270">
                    <?php foreach (array_filter($leads ?? [], function($l) { return ($l['status'] ?? '') === 'contacted'; }) as $lead): ?>
                    <div class="card mb-2 border-start border-3 border-info lead-card" class="style-78508" data-bs-toggle="modal" data-bs-target="#leadJourneyModal"
                        data-id="<?= $lead['id'] ?>"
                        data-name="<?= htmlspecialchars($lead['name'] ?? '') ?>"
                        data-phone="<?= htmlspecialchars($lead['phone'] ?? '') ?>"
                        data-property="<?= htmlspecialchars($lead['property_interest'] ?? '') ?>"
                        data-budget="<?= htmlspecialchars($lead['budget'] ?? '') ?>"
                        data-status="<?= $lead['status'] ?? '' ?>"
                        data-score="<?= $lead['lead_score'] ?? 0 ?>"
                        onclick="showLeadJourney(this)">
                        <div class="card-body p-2">
                            <h6 class="mb-1 small"><?= htmlspecialchars($lead['name'] ?? 'Unknown') ?></h6>
                            <small class="text-muted d-block"><?= htmlspecialchars($lead['property_interest'] ?? '-') ?></small>
                            <div class="d-flex justify-content-between mt-1">
                                <small class="text-muted">Score: <?= (int)($lead['lead_score'] ?? 0) ?></small>
                                <small class="text-muted"><?= date('d M', strtotime($lead['created_at'] ?? 'now')) ?></small>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php if (!array_filter($leads ?? [], function($l) { return ($l['status'] ?? '') === 'contacted'; })): ?>
                    <p class="text-muted small text-center py-3">No leads</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Qualified -->
        <div class="col" class="style-55803">
            <div class="card h-100">
                <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center">
                    <span class="fw-bold small">Qualified</span>
                    <span class="badge bg-success"><?= $stage_qualified ?? 0 ?></span>
                </div>
                <div class="card-body p-2" class="style-90270">
                    <?php foreach (array_filter($leads ?? [], function($l) { return ($l['status'] ?? '') === 'qualified'; }) as $lead): ?>
                    <div class="card mb-2 border-start border-3 border-success lead-card" class="style-78508" data-bs-toggle="modal" data-bs-target="#leadJourneyModal"
                        data-id="<?= $lead['id'] ?>"
                        data-name="<?= htmlspecialchars($lead['name'] ?? '') ?>"
                        data-phone="<?= htmlspecialchars($lead['phone'] ?? '') ?>"
                        data-property="<?= htmlspecialchars($lead['property_interest'] ?? '') ?>"
                        data-budget="<?= htmlspecialchars($lead['budget'] ?? '') ?>"
                        data-status="<?= $lead['status'] ?? '' ?>"
                        data-score="<?= $lead['lead_score'] ?? 0 ?>"
                        onclick="showLeadJourney(this)">
                        <div class="card-body p-2">
                            <h6 class="mb-1 small"><?= htmlspecialchars($lead['name'] ?? 'Unknown') ?></h6>
                            <small class="text-muted d-block"><?= htmlspecialchars($lead['property_interest'] ?? '-') ?></small>
                            <div class="d-flex justify-content-between mt-1">
                                <small class="text-muted">Score: <?= (int)($lead['lead_score'] ?? 0) ?></small>
                                <small class="text-muted"><?= date('d M', strtotime($lead['created_at'] ?? 'now')) ?></small>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php if (!array_filter($leads ?? [], function($l) { return ($l['status'] ?? '') === 'qualified'; })): ?>
                    <p class="text-muted small text-center py-3">No leads</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Proposal -->
        <div class="col" class="style-55803">
            <div class="card h-100">
                <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center">
                    <span class="fw-bold small">Proposal</span>
                    <span class="badge bg-warning text-dark"><?= $stage_proposal ?? 0 ?></span>
                </div>
                <div class="card-body p-2" class="style-90270">
                    <?php $stageItems = array_filter($leads ?? [], function($l) { return ($l['status'] ?? '') === 'proposal'; }); ?>
                    <?php foreach ($stageItems as $lead): ?>
                    <div class="card mb-2 border-start border-3 border-warning lead-card" class="style-78508" data-bs-toggle="modal" data-bs-target="#leadJourneyModal"
                        data-id="<?= $lead['id'] ?>"
                        data-name="<?= htmlspecialchars($lead['name'] ?? '') ?>"
                        data-phone="<?= htmlspecialchars($lead['phone'] ?? '') ?>"
                        data-property="<?= htmlspecialchars($lead['property_interest'] ?? '') ?>"
                        data-budget="<?= htmlspecialchars($lead['budget'] ?? '') ?>"
                        data-status="<?= $lead['status'] ?? '' ?>"
                        data-score="<?= $lead['lead_score'] ?? 0 ?>"
                        onclick="showLeadJourney(this)">
                        <div class="card-body p-2">
                            <h6 class="mb-1 small"><?= htmlspecialchars($lead['name'] ?? 'Unknown') ?></h6>
                            <small class="text-muted d-block"><?= htmlspecialchars($lead['property_interest'] ?? '-') ?></small>
                            <div class="d-flex justify-content-between mt-1">
                                <small class="text-muted">Score: <?= (int)($lead['lead_score'] ?? 0) ?></small>
                                <small class="text-muted"><?= date('d M', strtotime($lead['created_at'] ?? 'now')) ?></small>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php if (empty($stageItems)): ?><p class="text-muted small text-center py-3">No leads</p><?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Negotiation -->
        <div class="col" class="style-55803">
            <div class="card h-100">
                <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center">
                    <span class="fw-bold small">Negotiation</span>
                    <span class="badge bg-secondary"><?= $stage_negotiation ?? 0 ?></span>
                </div>
                <div class="card-body p-2" class="style-90270">
                    <?php $stageItems = array_filter($leads ?? [], function($l) { return ($l['status'] ?? '') === 'negotiation'; }); ?>
                    <?php foreach ($stageItems as $lead): ?>
                    <div class="card mb-2 border-start border-3 border-secondary lead-card" class="style-78508" data-bs-toggle="modal" data-bs-target="#leadJourneyModal"
                        data-id="<?= $lead['id'] ?>"
                        data-name="<?= htmlspecialchars($lead['name'] ?? '') ?>"
                        data-phone="<?= htmlspecialchars($lead['phone'] ?? '') ?>"
                        data-property="<?= htmlspecialchars($lead['property_interest'] ?? '') ?>"
                        data-budget="<?= htmlspecialchars($lead['budget'] ?? '') ?>"
                        data-status="<?= $lead['status'] ?? '' ?>"
                        data-score="<?= $lead['lead_score'] ?? 0 ?>"
                        onclick="showLeadJourney(this)">
                        <div class="card-body p-2">
                            <h6 class="mb-1 small"><?= htmlspecialchars($lead['name'] ?? 'Unknown') ?></h6>
                            <small class="text-muted d-block"><?= htmlspecialchars($lead['property_interest'] ?? '-') ?></small>
                            <div class="d-flex justify-content-between mt-1">
                                <small class="text-muted">Score: <?= (int)($lead['lead_score'] ?? 0) ?></small>
                                <small class="text-muted"><?= date('d M', strtotime($lead['created_at'] ?? 'now')) ?></small>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php if (empty($stageItems)): ?><p class="text-muted small text-center py-3">No leads</p><?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Nurture -->
        <div class="col" class="style-55803">
            <div class="card h-100">
                <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center">
                    <span class="fw-bold small">Nurture</span>
                    <span class="badge bg-warning"><?= $stage_nurture ?? 0 ?></span>
                </div>
                <div class="card-body p-2" class="style-90270">
                    <?php $stageItems = array_filter($leads ?? [], function($l) { return ($l['status'] ?? '') === 'nurture'; }); ?>
                    <?php foreach ($stageItems as $lead): ?>
                    <div class="card mb-2 border-start border-3 border-warning lead-card" class="style-78508" data-bs-toggle="modal" data-bs-target="#leadJourneyModal"
                        data-id="<?= $lead['id'] ?>"
                        data-name="<?= htmlspecialchars($lead['name'] ?? '') ?>"
                        data-phone="<?= htmlspecialchars($lead['phone'] ?? '') ?>"
                        data-property="<?= htmlspecialchars($lead['property_interest'] ?? '') ?>"
                        data-budget="<?= htmlspecialchars($lead['budget'] ?? '') ?>"
                        data-status="<?= $lead['status'] ?? '' ?>"
                        data-score="<?= $lead['lead_score'] ?? 0 ?>"
                        onclick="showLeadJourney(this)">
                        <div class="card-body p-2">
                            <h6 class="mb-1 small"><?= htmlspecialchars($lead['name'] ?? 'Unknown') ?></h6>
                            <small class="text-muted d-block"><?= htmlspecialchars($lead['property_interest'] ?? '-') ?></small>
                            <div class="d-flex justify-content-between mt-1">
                                <small class="text-muted">Score: <?= (int)($lead['lead_score'] ?? 0) ?></small>
                                <small class="text-muted"><?= date('d M', strtotime($lead['created_at'] ?? 'now')) ?></small>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php if (empty($stageItems)): ?><p class="text-muted small text-center py-3">No leads</p><?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Closed Won -->
        <div class="col" class="style-55803">
            <div class="card h-100">
                <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center">
                    <span class="fw-bold small">Closed Won</span>
                    <span class="badge bg-success"><?= $stage_closed_won ?? 0 ?></span>
                </div>
                <div class="card-body p-2" class="style-90270">
                    <?php $stageItems = array_filter($leads ?? [], function($l) { return ($l['status'] ?? '') === 'closed_won'; }); ?>
                    <?php foreach ($stageItems as $lead): ?>
                    <div class="card mb-2 border-start border-3 border-success lead-card" class="style-78508" data-bs-toggle="modal" data-bs-target="#leadJourneyModal"
                        data-id="<?= $lead['id'] ?>"
                        data-name="<?= htmlspecialchars($lead['name'] ?? '') ?>"
                        data-phone="<?= htmlspecialchars($lead['phone'] ?? '') ?>"
                        data-property="<?= htmlspecialchars($lead['property_interest'] ?? '') ?>"
                        data-budget="<?= htmlspecialchars($lead['budget'] ?? '') ?>"
                        data-status="<?= $lead['status'] ?? '' ?>"
                        data-score="<?= $lead['lead_score'] ?? 0 ?>"
                        onclick="showLeadJourney(this)">
                        <div class="card-body p-2">
                            <h6 class="mb-1 small"><?= htmlspecialchars($lead['name'] ?? 'Unknown') ?></h6>
                            <span class="badge bg-success small">Won</span>
                            <small class="text-muted d-block"><?= htmlspecialchars($lead['property_interest'] ?? '-') ?></small>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php if (empty($stageItems)): ?><p class="text-muted small text-center py-3">No leads</p><?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Closed Lost -->
        <div class="col" class="style-55803">
            <div class="card h-100">
                <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center">
                    <span class="fw-bold small">Closed Lost</span>
                    <span class="badge bg-danger"><?= $stage_closed_lost ?? 0 ?></span>
                </div>
                <div class="card-body p-2" class="style-90270">
                    <?php $stageItems = array_filter($leads ?? [], function($l) { return ($l['status'] ?? '') === 'closed_lost'; }); ?>
                    <?php foreach ($stageItems as $lead): ?>
                    <div class="card mb-2 border-start border-3 border-danger lead-card" class="style-78508" data-bs-toggle="modal" data-bs-target="#leadJourneyModal"
                        data-id="<?= $lead['id'] ?>"
                        data-name="<?= htmlspecialchars($lead['name'] ?? '') ?>"
                        data-phone="<?= htmlspecialchars($lead['phone'] ?? '') ?>"
                        data-property="<?= htmlspecialchars($lead['property_interest'] ?? '') ?>"
                        data-budget="<?= htmlspecialchars($lead['budget'] ?? '') ?>"
                        data-status="<?= $lead['status'] ?? '' ?>"
                        data-score="<?= $lead['lead_score'] ?? 0 ?>"
                        onclick="showLeadJourney(this)">
                        <div class="card-body p-2">
                            <h6 class="mb-1 small"><?= htmlspecialchars($lead['name'] ?? 'Unknown') ?></h6>
                            <span class="badge bg-danger small">Lost</span>
                            <small class="text-muted d-block"><?= htmlspecialchars($lead['property_interest'] ?? '-') ?></small>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php if (empty($stageItems)): ?><p class="text-muted small text-center py-3">No leads</p><?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Auto-Nurture Settings -->
    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header bg-white">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-sliders-h me-1"></i> Auto-Nurture Settings</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?= BASE_URL ?>/admin/voice-users/settings">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        <input type="hidden" name="max_attempts" value="3">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Follow-up Cadence (Days)</label>
                            <div class="d-flex gap-2">
                                <input type="number" class="form-control" value="1" min="1" max="30" placeholder="Initial">
                                <input type="number" class="form-control" value="3" min="1" max="30" placeholder="Follow-up">
                                <input type="number" class="form-control" value="7" min="1" max="30" placeholder="Long-term">
                            </div>
                            <small class="text-muted">Initial contact, follow-up, and long-term nurture intervals</small>
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" checked id="autoAssignNurture">
                            <label class="form-check-label small" for="autoAssignNurture">Auto-assign leads to available users for nurturing</label>
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" checked id="smartPriority">
                            <label class="form-check-label small" for="smartPriority">Smart priority scoring for follow-up scheduling</label>
                        </div>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Save Nurture Settings</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header bg-white">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-chart-simple me-1"></i> Pipeline Summary</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <small>New</small>
                            <small><?= $stage_new ?? 0 ?></small>
                        </div>
                        <div class="progress" class="style-32124">
                            <div class="progress-bar bg-primary" class="style-41346"></div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <small>Contacted</small>
                            <small><?= $stage_contacted ?? 0 ?></small>
                        </div>
                        <div class="progress" class="style-32124">
                            <div class="progress-bar bg-info" class="style-73045"></div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <small>Qualified</small>
                            <small><?= $stage_qualified ?? 0 ?></small>
                        </div>
                        <div class="progress" class="style-32124">
                            <div class="progress-bar bg-success" class="style-72219"></div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <small>Proposal</small>
                            <small><?= $stage_proposal ?? 0 ?></small>
                        </div>
                        <div class="progress" class="style-32124">
                            <div class="progress-bar bg-warning" class="style-27170"></div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <small>Negotiation</small>
                            <small><?= $stage_negotiation ?? 0 ?></small>
                        </div>
                        <div class="progress" class="style-32124">
                            <div class="progress-bar bg-secondary" class="style-59087"></div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <small>Nurture</small>
                            <small><?= $stage_nurture ?? 0 ?></small>
                        </div>
                        <div class="progress" class="style-32124">
                            <div class="progress-bar bg-warning" class="style-1338"></div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <small>Closed Won</small>
                            <small><?= $stage_closed_won ?? 0 ?></small>
                        </div>
                        <div class="progress" class="style-32124">
                            <div class="progress-bar bg-success" class="style-57836"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Lead Journey Modal -->
<div class="modal fade" id="leadJourneyModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-user me-2"></i> <span id="journeyLeadName">Lead Details</span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-4">
                    <div class="col-md-4">
                        <label class="small text-muted">Phone</label>
                        <div class="fw-medium" id="journeyPhone">-</div>
                    </div>
                    <div class="col-md-4">
                        <label class="small text-muted">Property Interest</label>
                        <div class="fw-medium" id="journeyProperty">-</div>
                    </div>
                    <div class="col-md-4">
                        <label class="small text-muted">Budget</label>
                        <div class="fw-medium" id="journeyBudget">-</div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="small text-muted fw-bold d-block">Status</label>
                    <span id="journeyStatus" class="badge bg-primary">-</span>
                    <span id="journeyScore" class="badge bg-info ms-2">Score: -</span>
                </div>

                <h6 class="fw-bold mt-4 mb-3"><i class="fas fa-timeline me-1"></i> Lead Journey Timeline</h6>
                <div class="timeline" id="journeyTimeline">
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-spinner fa-spin me-2"></i> Loading timeline...
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<style>
.timeline {
    position: relative;
    padding-left: 30px;
}
.timeline::before {
    content: '';
    position: absolute;
    left: 10px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #e5e7eb;
}
.timeline-item {
    position: relative;
    padding-bottom: 20px;
}
.timeline-item::before {
    content: '';
    position: absolute;
    left: -24px;
    top: 4px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background: #4f46e5;
    border: 2px solid #fff;
    box-shadow: 0 0 0 2px #4f46e5;
}
.lead-card:hover {
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    transform: translateY(-1px);
    transition: all 0.2s;
}
</style>

<script>
function showLeadJourney(btn) {
    document.getElementById('journeyLeadName').textContent = btn.dataset.name || 'Unknown';
    document.getElementById('journeyPhone').textContent = btn.dataset.phone || '-';
    document.getElementById('journeyProperty').textContent = btn.dataset.property || '-';
    document.getElementById('journeyBudget').textContent = btn.dataset.budget ? 'â‚¹' + btn.dataset.budget : '-';

    var status = btn.dataset.status || '';
    var statusBadge = document.getElementById('journeyStatus');
    var statusColors = {
        new: 'primary', contacted: 'info', qualified: 'success',
        proposal: 'warning', negotiation: 'secondary',
        nurture: 'warning', closed_won: 'success', closed_lost: 'danger'
    };
    statusBadge.className = 'badge bg-' + (statusColors[status] || 'secondary');
    statusBadge.textContent = status.replace(/_/g, ' ');

    document.getElementById('journeyScore').textContent = 'Score: ' + (btn.dataset.score || '0');

    var timeline = document.getElementById('journeyTimeline');
    timeline.innerHTML = '<div class="text-center text-muted py-4"><i class="fas fa-spinner fa-spin me-2"></i> Loading timeline...</div>';

    var leadId = btn.dataset.id;
    if (leadId) {
        fetch('<?= BASE_URL ?>/admin/voice-users/ajax/lead-timeline?lead_id=' + leadId)
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.success && data.timeline.length > 0) {
                    timeline.innerHTML = '';
                    data.timeline.forEach(function(item) {
                        var div = document.createElement('div');
                        div.className = 'timeline-item';
                        var iconMap = {
                            call: 'fa-phone', status: 'fa-tag',
                            followup: 'fa-calendar-check', note: 'fa-sticky-note',
                            email: 'fa-envelope', meeting: 'fa-handshake'
                        };
                        var icon = iconMap[item.type] || 'fa-circle';
                        var colorMap = {
                            call: 'text-primary', status: 'text-success',
                            followup: 'text-warning', note: 'text-info',
                            email: 'text-secondary', meeting: 'text-purple'
                        };
                        var color = colorMap[item.type] || 'text-muted';
                        div.innerHTML = '<div class="d-flex align-items-start">' +
                            '<div class="me-2 ' + color + '"><i class="fas ' + icon + '"></i></div>' +
                            '<div><strong>' + (item.action || '') + '</strong>' +
                            '<br><small class="text-muted">' + (item.description || '') + '</small>' +
                            '<br><small class="text-muted">' + (item.date || '') + '</small></div></div>';
                        timeline.appendChild(div);
                    });
                } else {
                    timeline.innerHTML = '<div class="text-center text-muted py-4">' +
                        '<i class="fas fa-info-circle me-2"></i>No timeline data available for this lead</div>';
                }
            })
            .catch(function() {
                timeline.innerHTML = '<div class="text-center text-danger py-4">' +
                    '<i class="fas fa-exclamation-triangle me-2"></i>Failed to load timeline</div>';
            });
    } else {
        timeline.innerHTML = '<div class="text-center text-muted py-4">No lead selected</div>';
    }
}
</script>
