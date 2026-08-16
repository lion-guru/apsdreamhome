<?php
$lead     = $lead ?? [];
$opinions = $opinions ?? [];
$id = (int)($lead['id'] ?? 0);
?>
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1"><i class="fas fa-gavel text-primary me-2"></i>Legal Opinions — Lead #<?= $id ?></h4>
            <small class="text-muted"><?= htmlspecialchars($lead['land_owner_name'] ?? '') ?></small>
        </div>
        <a href="<?= BASE_URL ?>/admin/land-inventory/leads/<?= $id ?>" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i>Back to Lead
        </a>
    </div>

    <div class="row g-3">
        <div class="col-md-5">
            <div class="aps-cp-card">
                <div class="aps-cp-card-header"><i class="fas fa-plus-circle me-2"></i>Record New Opinion</div>
                <div class="aps-cp-card-body">
                    <form method="post" action="<?= BASE_URL ?>/admin/land-inventory/leads/<?= $id ?>/opinions/store">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                        <div class="mb-2">
                            <label class="form-label small">Advocate Name <span class="text-danger">*</span></label>
                            <input type="text" name="advocate_name" class="form-control form-control-sm" required>
                        </div>
                        <div class="mb-2">
                            <label class="form-label small">Opinion Date <span class="text-danger">*</span></label>
                            <input type="date" name="opinion_date" class="form-control form-control-sm" value="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="mb-2">
                            <label class="form-label small">Overall Status</label>
                            <select name="status" class="form-select form-select-sm">
                                <option value="clear">Clear Title</option>
                                <option value="conditional">Conditional</option>
                                <option value="not_clear">Not Clear</option>
                            </select>
                        </div>
                        <div class="mb-2">
                            <label class="form-label small">Checks Performed</label>
                            <div class="form-check"><input class="form-check-input" type="checkbox" name="title_verified_chain" value="1" id="c1"><label class="form-check-label small" for="c1">Title chain verified</label></div>
                            <div class="form-check"><input class="form-check-input" type="checkbox" name="encumbrance_review" value="1" id="c2"><label class="form-check-label small" for="c2">Encumbrance reviewed (EC)</label></div>
                            <div class="form-check"><input class="form-check-input" type="checkbox" name="boundary_match" value="1" id="c3"><label class="form-check-label small" for="c3">Boundary matches records</label></div>
                            <div class="form-check"><input class="form-check-input" type="checkbox" name="co_owners_identified" value="1" id="c4"><label class="form-check-label small" for="c4">Co-owners identified</label></div>
                            <div class="form-check"><input class="form-check-input" type="checkbox" name="government_acquisition_check" value="1" id="c5"><label class="form-check-label small" for="c5">Not under govt. acquisition</label></div>
                        </div>
                        <div class="mb-2">
                            <label class="form-label small">Encroachment Risk</label>
                            <select name="encroachment_risk" class="form-select form-select-sm">
                                <option value="">— N/A —</option>
                                <option value="low">Low</option>
                                <option value="medium">Medium</option>
                                <option value="high">High</option>
                            </select>
                        </div>
                        <div class="mb-2">
                            <label class="form-label small">RERA Implications</label>
                            <textarea name="rera_implications" class="form-control form-control-sm" rows="2"></textarea>
                        </div>
                        <div class="mb-2">
                            <label class="form-label small">Remarks</label>
                            <textarea name="remarks" class="form-control form-control-sm" rows="2"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm w-100">
                            <i class="fas fa-save me-1"></i>Record Opinion
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-7">
            <div class="aps-cp-card">
                <div class="aps-cp-card-header"><i class="fas fa-list me-2"></i>Opinions (<?= count($opinions) ?>)</div>
                <div class="aps-cp-card-body p-0">
                    <?php foreach ($opinions as $o): ?>
                    <div class="border-bottom p-3">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <strong><?= htmlspecialchars($o['advocate_name'] ?? '—') ?></strong>
                                <span class="badge bg-<?= ($o['status'] ?? '') === 'clear' ? 'success' : (($o['status'] ?? '') === 'not_clear' ? 'danger' : 'warning') ?> ms-2">
                                    <?= htmlspecialchars(ucwords(str_replace('_',' ', $o['status'] ?? ''))) ?>
                                </span>
                                <br><small class="text-muted"><?= htmlspecialchars($o['opinion_date'] ?? '') ?></small>
                            </div>
                        </div>
                        <div class="row g-2 mt-2 small">
                            <div class="col-6"><span class="text-muted">Title Chain:</span> <?= !empty($o['title_verified_chain']) ? '✓' : '—' ?></div>
                            <div class="col-6"><span class="text-muted">Encumbrance:</span> <?= !empty($o['encumbrance_review']) ? '✓' : '—' ?></div>
                            <div class="col-6"><span class="text-muted">Boundary:</span> <?= !empty($o['boundary_match']) ? '✓' : '—' ?></div>
                            <div class="col-6"><span class="text-muted">Co-owners:</span> <?= !empty($o['co_owners_identified']) ? '✓' : '—' ?></div>
                            <div class="col-6"><span class="text-muted">Govt. Acq.:</span> <?= !empty($o['government_acquisition_check']) ? '✓' : '—' ?></div>
                            <div class="col-6"><span class="text-muted">Encroach Risk:</span> <?= htmlspecialchars($o['encroachment_risk'] ?? '—') ?></div>
                        </div>
                        <?php if (!empty($o['rera_implications'])): ?>
                            <div class="mt-2 small"><span class="text-muted">RERA:</span> <?= nl2br(htmlspecialchars($o['rera_implications'] ?? '')) ?></div>
                        <?php endif; ?>
                        <?php if (!empty($o['remarks'])): ?>
                            <div class="mt-2 small"><span class="text-muted">Remarks:</span> <?= nl2br(htmlspecialchars($o['remarks'] ?? '')) ?></div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                    <?php if (empty($opinions)): ?>
                        <div class="text-center text-muted py-5">
                            <i class="fas fa-gavel fa-2x mb-2 d-block"></i>
                            No legal opinions recorded yet.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
