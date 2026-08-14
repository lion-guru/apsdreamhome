<?php $page_title = $page_title ?? 'Lead Capture Forms'; ?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h2 class="mb-1 fw-bold"><i class="fas fa-wpforms me-2 text-primary"></i>Lead Capture Forms</h2>
            <p class="text-muted mb-0">Create embeddable forms for website, landing pages, and social media</p>
        </div>
        <button class="btn btn-primary" onclick="new bootstrap.Modal(document.getElementById('formModal')).show()"><i class="fas fa-plus me-1"></i> New Form</button>
    </div>


    <?php if (empty($forms)): ?>
        <div class="text-center py-5 bg-white rounded shadow-sm">
            <i class="fas fa-wpforms fa-3x text-muted mb-3"></i>
            <h5 class="text-muted">No forms created yet</h5>
            <p class="text-muted">Build custom lead capture forms without code</p>
            <button class="btn btn-primary" onclick="new bootstrap.Modal(document.getElementById('formModal')).show()"><i class="fas fa-plus me-1"></i> Create First Form</button>
        </div>
    <?php else: ?>
        <div class="row g-3">
            <?php foreach ($forms as $f): 
                $fields = json_decode($f['fields'] ?? '[]', true) ?? [];
                $settings = json_decode($f['settings'] ?? '{}', true) ?? [];
            ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h6 class="fw-bold mb-0"><?= htmlspecialchars($f['name']) ?></h6>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-light" data-bs-toggle="dropdown"><i class="fas fa-ellipsis-v"></i></button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li><a class="dropdown-item" href="<?= BASE_URL ?>/admin/crm/forms/<?= $f['id'] ?>/preview"><i class="fas fa-eye me-2"></i>Preview</a></li>
                                        <li><a class="dropdown-item" href="<?= BASE_URL ?>/admin/crm/forms/<?= $f['id'] ?>/embed"><i class="fas fa-code me-2"></i>Embed Code</a></li>
                                        <li><a class="dropdown-item" href="<?= BASE_URL ?>/admin/crm/forms/<?= $f['id'] ?>/edit"><i class="fas fa-edit me-2"></i>Edit</a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <form method="POST" action="<?= BASE_URL ?>/admin/crm/forms/<?= $f['id'] ?>/delete" onsubmit="return confirm('Delete this form?')">
                                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                                <button class="dropdown-item text-danger"><i class="fas fa-trash me-2"></i>Delete</button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <p class="text-muted mb-2" class="style-87981"><?= htmlspecialchars(mb_strimwidth($f['description'] ?? '', 0, 80, '...')) ?></p>
                            <div class="d-flex gap-2 mb-2">
                                <span class="badge bg-primary-subtle text-primary-emphasis"><i class="fas fa-fields me-1"></i><?= count($fields) ?> fields</span>
                                <?php if (!empty($settings['auto_assign'])): ?>
                                    <span class="badge bg-success-subtle text-success-emphasis"><i class="fas fa-user-check me-1"></i>Auto-assign</span>
                                <?php endif; ?>
                                <?php if (!empty($settings['drip_campaign'])): ?>
                                    <span class="badge bg-info-subtle text-info-emphasis"><i class="fas fa-envelope-open me-1"></i>Drip campaign</span>
                                <?php endif; ?>
                            </div>
                            <div class="text-muted" class="style-86354">
                                Created <?= date('d M Y', strtotime($f['created_at'])) ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Create/Edit Form Modal -->
<div class="modal fade" id="formModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="formModalTitle"><i class="fas fa-wpforms me-2"></i>Create Form</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="<?= BASE_URL ?>/admin/crm/forms/store" id="formModalForm">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                <div class="modal-body p-3">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label fw-bold">Form Name *</label>
                            <input type="text" class="form-control" name="name" id="modalFormName" required placeholder="e.g. Property Inquiry Form">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Submit Button Text</label>
                            <input type="text" class="form-control" name="submit_text" id="modalSubmitText" value="Submit" placeholder="Submit">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Description</label>
                            <input type="text" class="form-control" name="description" id="modalDescription" placeholder="Internal description">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Success Message</label>
                            <input type="text" class="form-control" name="success_message" id="modalSuccessMsg" value="Thank you for your interest! We'll contact you soon.">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Redirect URL (optional)</label>
                            <input type="url" class="form-control" name="redirect_url" id="modalRedirectUrl" placeholder="https://example.com/thank-you">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Auto-assign to Agent</label>
                            <select class="form-select" name="assign_to" id="modalAssignTo">
                                <option value="">Don't auto-assign</option>
                                <?php
                                try {
                                    $db = \App\Core\Database\Database::getInstance()->getConnection();
                                    $agents = $db->query("SELECT id, name FROM users WHERE role IN ('associate','employee','agent') AND status = 'active' ORDER BY name")->fetchAll(\PDO::FETCH_ASSOC) ?: [];
                                    foreach ($agents as $a): ?>
                                        <option value="<?= $a['id'] ?>"><?= htmlspecialchars($a['name']) ?></option>
                                    <?php endforeach;
                                } catch (\Throwable $e) {}
                                ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Drip Campaign (optional)</label>
                            <select class="form-select" name="drip_campaign" id="modalDripCampaign">
                                <option value="">None</option>
                                <?php
                                try {
                                    $db = \App\Core\Database\Database::getInstance()->getConnection();
                                    $campaigns = $db->query("SELECT id, name FROM campaigns WHERE status = 'active' ORDER BY name")->fetchAll(\PDO::FETCH_ASSOC) ?: [];
                                    foreach ($campaigns as $c): ?>
                                        <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                                    <?php endforeach;
                                } catch (\Throwable $e) {}
                                ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Tags (comma-separated)</label>
                            <input type="text" class="form-control" name="tags" id="modalTags" placeholder="e.g. website, landing-page, facebook">
                        </div>
                    </div>
                    <input type="hidden" name="fields" id="modalFieldsJson" value="[]">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="openBuilder()"><i class="fas fa-magic me-1"></i> Open Visual Builder</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openBuilder() {
    const name = document.getElementById('modalFormName').value;
    if (!name) { alert('Please enter a form name first'); return; }
    const url = '<?= BASE_URL ?>/admin/crm/forms/create' + (name ? '?name=' + encodeURIComponent(name) : '');
    window.open(url, '_blank', 'width=1200,height=800');
}
</script>