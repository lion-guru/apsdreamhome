<?php
/** Admin Engagement Survey Create/Edit */

$page_title = $page_title ?? ($mode === 'edit' ? 'Edit Survey' : 'Create Survey');
$mode = $mode ?? 'create';
$survey = $survey ?? [];
$questionTypes = $questionTypes ?? [];
$surveyTypes = $surveyTypes ?? [];
?>

<style nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
.question-row {
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 1rem;
    margin-bottom: 1rem;
    background: #fafafa;
    transition: all 0.2s;
}
.question-row:hover {
    border-color: #7c2d12;
    background: #fefcf6;
}
.question-row .drag-handle { cursor: grab; color: #7c2d12; }
.question-type-badge { font-size: 0.7rem; padding: 0.15rem 0.4rem; border-radius: 15px; }
</style>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold"><i class="fas fa-poll me-2 text-primary"></i><?= $page_title ?></h4>
            <p class="text-muted mb-0 small">Create an engagement survey with custom questions</p>
        </div>
        <a href="<?= BASE_URL ?>/admin/engagement/surveys" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Surveys
        </a>
    </div>

    <form method="POST" id="surveyForm" class="needs-validation" novalidate>
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
        <?php if ($mode === 'edit'): ?>
            <input type="hidden" name="id" value="<?= $survey['id'] ?>">
        <?php endif; ?>

        <!-- Basic Info -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0 fw-semibold"><i class="fas fa-info-circle me-2"></i>Survey Information</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label fw-semibold">Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" required value="<?= htmlspecialchars($survey['title'] ?? '') ?>" placeholder="e.g., Q4 Employee Pulse Survey">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Type</label>
                        <select name="survey_type" class="form-select">
                            <?php foreach ($surveyTypes as $key => $label): ?>
                                <option value="<?= $key ?>" <?= ($survey['survey_type'] ?? '') === $key ? 'selected' : '' ?>><?= $label ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Status</label>
                        <select name="status" class="form-select">
                            <option value="draft" <?= ($survey['status'] ?? '') === 'draft' ? 'selected' : '' ?>>Draft</option>
                            <option value="scheduled" <?= ($survey['status'] ?? '') === 'scheduled' ? 'selected' : '' ?>>Scheduled</option>
                            <option value="active" <?= ($survey['status'] ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
                            <option value="closed" <?= ($survey['status'] ?? '') === 'closed' ? 'selected' : '' ?>>Closed</option>
                            <option value="archived" <?= ($survey['status'] ?? '') === 'archived' ? 'selected' : '' ?>>Archived</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Start Date</label>
                        <input type="date" name="start_date" class="form-control" value="<?= htmlspecialchars($survey['start_date'] ?? '') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">End Date</label>
                        <input type="date" name="end_date" class="form-control" value="<?= htmlspecialchars($survey['end_date'] ?? '') ?>">
                    </div>
                    <div class="col-md-4">
                        <div class="form-check form-switch mt-3">
                            <input class="form-check-input" type="checkbox" name="is_anonymous" value="1" id="is_anonymous" <?= empty($survey['is_anonymous']) ? '' : 'checked' ?>>
                            <label class="form-check-label" for="is_anonymous">Anonymous Responses</label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-check form-switch mt-3">
                            <input class="form-check-input" type="checkbox" name="allow_multiple" value="1" id="allow_multiple" <?= empty($survey['allow_multiple']) ? '' : 'checked' ?>>
                            <label class="form-check-label" for="allow_multiple">Allow Multiple Responses</label>
                        </div>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Description</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Survey description and instructions..."><?= htmlspecialchars($survey['description'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- Questions -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-semibold"><i class="fas fa-question-circle me-2"></i>Questions</h5>
                <button type="button" class="btn btn-sm btn-primary" onclick="addQuestion()">
                    <i class="fas fa-plus me-1"></i> Add Question
                </button>
            </div>
            <div class="card-body">
                <div id="questionsContainer">
                    <?php 
                    $questions = $survey['questions'] ?? [];
                    if (empty($questions)) {
                        $questions = [['type' => 'rating', 'text' => '', 'required' => true, 'options' => []]];
                    }
                    foreach ($questions as $index => $q): 
                    ?>
                    <div class="question-row" data-index="<?= $index ?>">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="d-flex align-items-center gap-2">
                                <div class="drag-handle p-2 text-muted" title="Drag to reorder"><i class="fas fa-grip-lines"></i></div>
                                <span class="question-type-badge bg-info text-white"><?= strtoupper($q['type'] ?? 'rating') ?></span>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeQuestion(this)"><i class="fas fa-trash"></i></button>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label fw-semibold small">Question Text <span class="text-danger">*</span></label>
                                <input type="text" name="questions[<?= $index ?>][text]" class="form-control" required value="<?= htmlspecialchars($q['text'] ?? '') ?>" placeholder="Enter your question...">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold small">Type</label>
                                <select name="questions[<?= $index ?>][type]" class="form-select form-select-sm" onchange="toggleOptions(this, <?= $index ?>)">
                                    <?php foreach ($questionTypes as $key => $label): ?>
                                        <option value="<?= $key ?>" <?= ($q['type'] ?? '') === $key ? 'selected' : '' ?>><?= $label ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <div class="form-check form-switch mt-3">
                                    <input class="form-check-input" type="checkbox" name="questions[<?= $index ?>][required]" value="1" id="req_<?= $index ?>" <?= empty($q['required']) ? '' : 'checked' ?>>
                                    <label class="form-check-label small" for="req_<?= $index ?>">Required</label>
                                </div>
                            </div>
                            <div class="col-12 options-container" id="options_<?= $index ?>" style="display: <?= in_array($q['type'] ?? '', ['multiple_choice', 'checkbox']) ? 'block' : 'none' ?>;">
                                <label class="form-label fw-semibold small">Options (one per line)</label>
                                <textarea name="questions[<?= $index ?>][options]" class="form-control" rows="3" placeholder="Option 1&#10;Option 2&#10;Option 3"><?= is_array($q['options'] ?? null) ? implode("\n", $q['options']) : ($q['options'] ?? '') ?></textarea>
                                <div class="form-text small">Enter each option on a new line</div>
                            </div>
                            <div class="col-12 scale-container" id="scale_<?= $index ?>" style="display: <?= in_array($q['type'] ?? '', ['rating', 'nps', 'likert']) ? 'block' : 'none' ?>;">
                                <label class="form-label fw-semibold small">Scale Range</label>
                                <div class="row g-2">
                                    <div class="col-md-6">
                                        <input type="number" min="1" max="10" name="questions[<?= $index ?>][scale_min]" class="form-control" value="<?= $q['scale_min'] ?? 1 ?>" placeholder="Min (e.g., 1)">
                                    </div>
                                    <div class="col-md-6">
                                        <input type="number" min="2" max="10" name="questions[<?= $index ?>][scale_max]" class="form-control" value="<?= $q['scale_max'] ?? 5 ?>" placeholder="Max (e.g., 5, 10)">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <hr class="my-2">
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Settings -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0 fw-semibold"><i class="fas fa-cog me-2"></i>Survey Settings</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Thank You Message</label>
                        <textarea name="settings[thank_you_message]" class="form-control" rows="2"><?= htmlspecialchars(($survey['settings']['thank_you_message'] ?? 'Thank you for your feedback!')) ?></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Redirect URL (after completion)</label>
                        <input type="url" name="settings[redirect_url]" class="form-control" value="<?= htmlspecialchars($survey['settings']['redirect_url'] ?? '') ?>" placeholder="https://...">
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mt-4">
            <a href="<?= BASE_URL ?>/admin/engagement/surveys" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> Cancel
            </a>
            <button type="submit" class="btn btn-primary btn-lg">
                <i class="fas fa-save me-1"></i> Save Survey
            </button>
        </div>
    </form>
</div>

<script nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
let questionIndex = <?= count($survey['questions'] ?? []) ?>;

function addQuestion() {
    const container = document.getElementById('questionsContainer');
    const index = questionIndex++;
    const html = `
        <div class="question-row" data-index="${index}">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div class="d-flex align-items-center gap-2">
                    <div class="drag-handle p-2 text-muted" title="Drag to reorder"><i class="fas fa-grip-lines"></i></div>
                    <span class="question-type-badge bg-info text-white">RATING</span>
                </div>
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeQuestion(this)"><i class="fas fa-trash"></i></button>
            </div>
            <div class="row g-3">
                <div class="col-md-8">
                    <label class="form-label fw-semibold small">Question Text <span class="text-danger">*</span></label>
                    <input type="text" name="questions[${index}][text]" class="form-control" required placeholder="Enter your question...">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold small">Type</label>
                    <select name="questions[${index}][type]" class="form-select form-select-sm" onchange="toggleOptions(this, ${index})">
                        <?php foreach ($questionTypes as $key => $label): ?>
                            <option value="<?= $key ?>"><?= $label ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <div class="form-check form-switch mt-3">
                        <input class="form-check-input" type="checkbox" name="questions[${index}][required]" value="1" id="req_${index}" checked>
                        <label class="form-check-label small" for="req_${index}">Required</label>
                    </div>
                </div>
                <div class="col-12 options-container" id="options_${index}" style="display: none;">
                    <label class="form-label fw-semibold small">Options (one per line)</label>
                    <textarea name="questions[${index}][options]" class="form-control" rows="3" placeholder="Option 1&#10;Option 2&#10;Option 3"></textarea>
                    <div class="form-text small">Enter each option on a new line</div>
                </div>
                <div class="col-12 scale-container" id="scale_${index}" style="display: block;">
                    <label class="form-label fw-semibold small">Scale Range</label>
                    <div class="row g-2">
                        <div class="col-md-6">
                            <input type="number" min="1" max="10" name="questions[${index}][scale_min]" class="form-control" value="1" placeholder="Min">
                        </div>
                        <div class="col-md-6">
                            <input type="number" min="2" max="10" name="questions[${index}][scale_max]" class="form-control" value="5" placeholder="Max">
                        </div>
                    </div>
                </div>
                <hr class="my-2">
        `;
        container.insertAdjacentHTML('beforeend', html);
    }

function removeQuestion(btn) {
    if (confirm('Remove this question?')) {
        btn.closest('.question-row').remove();
    }
}

function toggleOptions(select, index) {
    const type = select.value;
    const optionsContainer = document.getElementById('options_' + index);
    const scaleContainer = document.getElementById('scale_' + index);
    const badge = select.closest('.question-row').querySelector('.question-type-badge');
    
    if (badge) badge.textContent = type.toUpperCase();
    
    if (type === 'multiple_choice' || type === 'checkbox') {
        optionsContainer.style.display = 'block';
        scaleContainer.style.display = 'none';
    } else if (type === 'rating' || type === 'nps' || type === 'likert') {
        optionsContainer.style.display = 'none';
        scaleContainer.style.display = 'block';
    } else {
        optionsContainer.style.display = 'none';
        scaleContainer.style.display = 'none';
    }
}

// Initialize
document.querySelectorAll('#questionsContainer select[name$="[type]"]').forEach((select, i) => {
    toggleOptions(select, select.closest('.question-row').dataset.index);
});

document.getElementById('surveyForm').addEventListener('submit', function(e) {
    if (!this.checkValidity()) {
        e.preventDefault();
        e.stopPropagation();
    }
    this.classList.add('was-validated');
});
</script>