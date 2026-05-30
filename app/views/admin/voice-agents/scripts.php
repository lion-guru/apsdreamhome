<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0"><i class="fas fa-scroll me-2"></i> Call Scripts</h3>
        <div>
            <a href="<?= BASE_URL ?>/admin/voice-agents/dashboard" class="btn btn-outline-primary">
                <i class="fas fa-arrow-left"></i> Dashboard
            </a>
        </div>
    </div>

    <div class="row">
        <?php if (empty($scripts)): ?>
        <div class="col-12">
            <div class="text-center py-5">
                <i class="fas fa-scroll fa-3x text-muted mb-3"></i>
                <p class="text-muted">No call scripts found</p>
            </div>
        </div>
        <?php else: ?>
        <?php foreach ($scripts as $script): ?>
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h5 class="card-title mb-1"><?= htmlspecialchars($script['script_name'] ?? 'Unnamed') ?></h5>
                            <span class="badge bg-light text-muted"><?= htmlspecialchars($script['script_code'] ?? '') ?></span>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" <?= ($script['is_active'] ?? 0) ? 'checked' : '' ?> disabled>
                        </div>
                    </div>

                    <div class="mb-3">
                        <span class="badge bg-info me-1"><?= ucfirst($script['voice_language'] ?? 'en') ?></span>
                        <span class="badge bg-secondary me-1"><?= ucfirst($script['voice_tone'] ?? 'professional') ?></span>
                    </div>

                    <div class="d-flex justify-content-between text-center mb-3">
                        <div>
                            <div class="fw-bold h5 mb-0"><?= (int)($script['usage_count'] ?? 0) ?></div>
                            <small class="text-muted">Used</small>
                        </div>
                        <div>
                            <div class="fw-bold h5 mb-0"><?= $script['success_rate'] ?? 0 ?>%</div>
                            <small class="text-muted">Success</small>
                        </div>
                        <div>
                            <div class="fw-bold h5 mb-0 <?= ($script['is_active'] ?? 0) ? 'text-success' : 'text-danger' ?>">
                                <?= ($script['is_active'] ?? 0) ? 'Active' : 'Inactive' ?>
                            </div>
                            <small class="text-muted">Status</small>
                        </div>
                    </div>

                    <button class="btn btn-outline-info btn-sm w-100" data-bs-toggle="modal" data-bs-target="#scriptDetailModal"
                        data-name="<?= htmlspecialchars($script['script_name'] ?? '') ?>"
                        data-code="<?= htmlspecialchars($script['script_code'] ?? '') ?>"
                        data-greeting="<?= htmlspecialchars($script['greeting_text'] ?? '') ?>"
                        data-intro="<?= htmlspecialchars($script['introduction_text'] ?? '') ?>"
                        data-pitch="<?= htmlspecialchars($script['property_pitch'] ?? '') ?>"
                        data-questions="<?= htmlspecialchars($script['questions_to_ask'] ?? '') ?>"
                        data-objections="<?= htmlspecialchars($script['objection_handling'] ?? '') ?>"
                        data-closing="<?= htmlspecialchars($script['closing_text'] ?? '') ?>"
                        data-language="<?= ucfirst($script['voice_language'] ?? 'en') ?>"
                        data-tone="<?= ucfirst($script['voice_tone'] ?? 'professional') ?>"
                        onclick="showScriptDetail(this)">
                        <i class="fas fa-eye me-1"></i> View Script
                    </button>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Script Detail Modal -->
<div class="modal fade" id="scriptDetailModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><span id="scriptModalName">Script</span> <small class="text-muted" id="scriptModalCode"></small></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <span class="badge bg-info me-2" id="scriptModalLang">EN</span>
                    <span class="badge bg-secondary" id="scriptModalTone">Professional</span>
                </div>

                <ul class="nav nav-tabs mb-3" id="scriptTabs">
                    <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#tabGreeting">Greeting</a></li>
                    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tabIntro">Introduction</a></li>
                    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tabPitch">Property Pitch</a></li>
                    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tabQuestions">Questions</a></li>
                    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tabObjections">Objections</a></li>
                    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tabClosing">Closing</a></li>
                </ul>

                <div class="tab-content">
                    <div class="tab-pane active" id="tabGreeting">
                        <div id="scriptGreeting" class="p-3 bg-light rounded small">-</div>
                    </div>
                    <div class="tab-pane" id="tabIntro">
                        <div id="scriptIntro" class="p-3 bg-light rounded small">-</div>
                    </div>
                    <div class="tab-pane" id="tabPitch">
                        <div id="scriptPitch" class="p-3 bg-light rounded small">-</div>
                    </div>
                    <div class="tab-pane" id="tabQuestions">
                        <div id="scriptQuestions" class="p-3 bg-light rounded small">-</div>
                    </div>
                    <div class="tab-pane" id="tabObjections">
                        <div id="scriptObjections" class="p-3 bg-light rounded small">-</div>
                    </div>
                    <div class="tab-pane" id="tabClosing">
                        <div id="scriptClosing" class="p-3 bg-light rounded small">-</div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
function showScriptDetail(btn) {
    document.getElementById('scriptModalName').textContent = btn.dataset.name || 'Script';
    document.getElementById('scriptModalCode').textContent = btn.dataset.code ? '(' + btn.dataset.code + ')' : '';
    document.getElementById('scriptModalLang').textContent = btn.dataset.language || 'EN';
    document.getElementById('scriptModalTone').textContent = btn.dataset.tone || 'Professional';
    document.getElementById('scriptGreeting').textContent = btn.dataset.greeting || '-';
    document.getElementById('scriptIntro').textContent = btn.dataset.intro || '-';
    document.getElementById('scriptPitch').textContent = btn.dataset.pitch || '-';
    document.getElementById('scriptQuestions').textContent = btn.dataset.questions || '-';
    document.getElementById('scriptObjections').textContent = btn.dataset.objections || '-';
    document.getElementById('scriptClosing').textContent = btn.dataset.closing || '-';
}
</script>
