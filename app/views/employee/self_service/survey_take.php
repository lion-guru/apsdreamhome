<?php
/**
 * Employee Self-Service: Take Engagement Survey
 */
$page_title = $page_title ?? 'Take Survey';
$survey = $survey ?? [];
$questions = $questions ?? [];
?>

<div class="container py-4" style="max-width: 820px;">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/employee/self-service/surveys" class="text-decoration-none">Surveys</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($survey['title'] ?? 'Take Survey') ?></li>
        </ol>
    </nav>

    <!-- Header Card -->
    <div class="card shadow-sm border-0 mb-4" style="background: linear-gradient(135deg, #1e3a8a, #3b82f6); color: #fff; border-radius: 14px;">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-2">
                <span class="badge bg-white text-primary fw-semibold px-3 py-2">
                    <i class="fas fa-poll me-1"></i><?= ucfirst(htmlspecialchars($survey['survey_type'] ?? 'Survey')) ?>
                </span>
                <?php if (!empty($survey['is_anonymous'])): ?>
                    <span class="badge bg-black bg-opacity-25 text-white">
                        <i class="fas fa-user-secret me-1"></i>100% Confidential & Anonymous
                    </span>
                <?php endif; ?>
            </div>
            <h3 class="fw-bold mb-2"><?= htmlspecialchars($survey['title'] ?? '') ?></h3>
            <?php if (!empty($survey['description'])): ?>
                <p class="mb-0 text-white-50" style="font-size: 0.95rem; line-height: 1.6;">
                    <?= nl2br(htmlspecialchars($survey['description'])) ?>
                </p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Alert Messages -->
    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($_SESSION['error']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <!-- Survey Form -->
    <form action="<?= BASE_URL ?>/employee/self-service/surveys/take/<?= (int)($survey['id'] ?? 0) ?>" method="POST" id="surveyForm">
        <input type="hidden" name="completion_time_seconds" id="completionTimeSeconds" value="0">

        <?php if (empty($questions)): ?>
            <div class="card shadow-sm border-0 p-5 text-center">
                <i class="fas fa-clipboard fa-3x text-muted mb-3 opacity-50"></i>
                <h5 class="text-muted">No Questions Found</h5>
                <p class="text-muted small mb-0">This survey does not have any active questions at the moment.</p>
            </div>
        <?php else: ?>
            <?php foreach ($questions as $index => $q): 
                $qText = is_array($q) ? ($q['question'] ?? $q['text'] ?? "Question " . ($index + 1)) : $q;
                $qType = is_array($q) ? ($q['type'] ?? 'rating') : 'rating';
                $isRequired = is_array($q) ? !empty($q['required']) : false;
                $options = is_array($q) ? ($q['options'] ?? []) : [];
            ?>
            <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px;">
                <div class="card-body p-4">
                    <div class="d-flex align-items-baseline gap-2 mb-3">
                        <span class="badge bg-primary rounded-circle d-inline-flex justify-content-center align-items-center" style="width: 28px; height: 28px; font-size: 0.85rem;">
                            <?= $index + 1 ?>
                        </span>
                        <h6 class="fw-bold mb-0 text-dark flex-grow-1">
                            <?= htmlspecialchars($qText) ?>
                            <?php if ($isRequired): ?>
                                <span class="text-danger ms-1">*</span>
                            <?php endif; ?>
                        </h6>
                    </div>

                    <div class="ps-md-4">
                        <?php if ($qType === 'rating'): ?>
                            <!-- 1 to 5 Star / Numeric Rating -->
                            <div class="d-flex gap-2 align-items-center flex-wrap pt-2">
                                <?php for ($r = 1; $r <= 5; $r++): ?>
                                    <label class="btn btn-outline-secondary px-3 py-2 text-center" style="min-width: 60px;">
                                        <input type="radio" name="q_<?= $index ?>" value="<?= $r ?>" class="btn-check" <?= $isRequired && $r === 1 ? 'required' : '' ?>>
                                        <div class="fw-bold fs-5"><?= $r ?></div>
                                        <small style="font-size: 0.65rem;">
                                            <?= $r === 1 ? 'Poor' : ($r === 5 ? 'Excellent' : '&nbsp;') ?>
                                        </small>
                                    </label>
                                <?php endfor; ?>
                            </div>

                        <?php elseif ($qType === 'textarea' || $qType === 'text'): ?>
                            <!-- Text Response -->
                            <textarea class="form-control" name="q_<?= $index ?>" rows="3" placeholder="Type your response here..." <?= $isRequired ? 'required' : '' ?>></textarea>

                        <?php elseif ($qType === 'boolean' || $qType === 'yes_no'): ?>
                            <!-- Yes / No Choice -->
                            <div class="btn-group" role="group" aria-label="Yes or No">
                                <input type="radio" class="btn-check" name="q_<?= $index ?>" id="q_<?= $index ?>_yes" value="yes" <?= $isRequired ? 'required' : '' ?>>
                                <label class="btn btn-outline-success px-4" for="q_<?= $index ?>_yes"><i class="fas fa-check me-1"></i> Yes</label>

                                <input type="radio" class="btn-check" name="q_<?= $index ?>" id="q_<?= $index ?>_no" value="no">
                                <label class="btn btn-outline-danger px-4" for="q_<?= $index ?>_no"><i class="fas fa-times me-1"></i> No</label>
                            </div>

                        <?php elseif ($qType === 'single_choice' || $qType === 'radio'): ?>
                            <!-- Radio Options -->
                            <div class="d-flex flex-column gap-2">
                                <?php foreach ($options as $optIdx => $opt): 
                                    $optVal = is_array($opt) ? ($opt['value'] ?? $opt['label'] ?? '') : $opt;
                                    $optLabel = is_array($opt) ? ($opt['label'] ?? $opt['value'] ?? '') : $opt;
                                ?>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="q_<?= $index ?>" id="q_<?= $index ?>_<?= $optIdx ?>" value="<?= htmlspecialchars($optVal) ?>" <?= $isRequired ? 'required' : '' ?>>
                                    <label class="form-check-label" for="q_<?= $index ?>_<?= $optIdx ?>">
                                        <?= htmlspecialchars($optLabel) ?>
                                    </label>
                                </div>
                                <?php endforeach; ?>
                            </div>

                        <?php elseif ($qType === 'multiple_choice' || $qType === 'checkbox'): ?>
                            <!-- Checkbox Options -->
                            <div class="d-flex flex-column gap-2">
                                <?php foreach ($options as $optIdx => $opt): 
                                    $optVal = is_array($opt) ? ($opt['value'] ?? $opt['label'] ?? '') : $opt;
                                    $optLabel = is_array($opt) ? ($opt['label'] ?? $opt['value'] ?? '') : $opt;
                                ?>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="q_<?= $index ?>[]" id="q_<?= $index ?>_<?= $optIdx ?>" value="<?= htmlspecialchars($optVal) ?>">
                                    <label class="form-check-label" for="q_<?= $index ?>_<?= $optIdx ?>">
                                        <?= htmlspecialchars($optLabel) ?>
                                    </label>
                                </div>
                                <?php endforeach; ?>
                            </div>

                        <?php else: ?>
                            <!-- Default Textarea -->
                            <textarea class="form-control" name="q_<?= $index ?>" rows="3" placeholder="Share your feedback..." <?= $isRequired ? 'required' : '' ?>></textarea>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>

            <!-- Action Buttons -->
            <div class="d-flex justify-content-between align-items-center mb-5">
                <a href="<?= BASE_URL ?>/employee/self-service/surveys" class="btn btn-outline-secondary">
                    <i class="fas fa-times me-1"></i> Cancel
                </a>
                <button type="submit" class="btn btn-primary px-4 py-2 fw-semibold">
                    <i class="fas fa-paper-plane me-2"></i> Submit Survey
                </button>
            </div>
        <?php endif; ?>
    </form>
</div>

<script nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
document.addEventListener('DOMContentLoaded', function() {
    const startTime = Date.now();
    const timerInput = document.getElementById('completionTimeSeconds');
    const form = document.getElementById('surveyForm');
    
    if (form) {
        form.addEventListener('submit', function() {
            if (timerInput) {
                const elapsedSeconds = Math.round((Date.now() - startTime) / 1000);
                timerInput.value = elapsedSeconds;
            }
        });
    }
});
</script>
