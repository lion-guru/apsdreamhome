<?php
/**
 * Employee Self-Service: View Survey Response / Results
 */
$page_title = $page_title ?? 'Survey Response';
$survey = $survey ?? [];
$response = $response ?? [];
$questions = $survey['questions'] ?? [];
$answers = $response['answers'] ?? [];
$submittedAt = !empty($response['submitted_at']) ? date('M d, Y h:i A', strtotime($response['submitted_at'])) : 'Submitted';
$timeSeconds = (int)($response['completion_time_seconds'] ?? 0);
$timeFormatted = $timeSeconds > 60 ? floor($timeSeconds / 60) . 'm ' . ($timeSeconds % 60) . 's' : $timeSeconds . 's';
?>

<div class="container py-4" style="max-width: 820px;">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/employee/self-service/surveys" class="text-decoration-none">Surveys</a></li>
            <li class="breadcrumb-item active" aria-current="page">My Response</li>
        </ol>
    </nav>

    <!-- Header Card -->
    <div class="card shadow-sm border-0 mb-4" style="border-radius: 14px;">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-2">
                <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2">
                    <i class="fas fa-check-circle me-1"></i>Response Recorded
                </span>
                <span class="text-muted small">
                    <i class="far fa-clock me-1"></i>Submitted on <?= $submittedAt ?>
                </span>
            </div>
            <h3 class="fw-bold mb-2 text-dark"><?= htmlspecialchars($survey['title'] ?? 'Survey') ?></h3>
            <?php if (!empty($survey['description'])): ?>
                <p class="text-muted mb-3" style="font-size: 0.95rem;">
                    <?= nl2br(htmlspecialchars($survey['description'])) ?>
                </p>
            <?php endif; ?>

            <div class="d-flex gap-4 pt-2 border-top">
                <div>
                    <small class="text-muted d-block">Privacy Status</small>
                    <span class="fw-semibold text-dark">
                        <?= !empty($survey['is_anonymous']) ? '<i class="fas fa-user-secret text-secondary me-1"></i>Anonymous' : '<i class="fas fa-user text-primary me-1"></i>Attributed' ?>
                    </span>
                </div>
                <?php if ($timeSeconds > 0): ?>
                <div>
                    <small class="text-muted d-block">Time Taken</small>
                    <span class="fw-semibold text-dark"><i class="fas fa-stopwatch me-1 text-info"></i><?= $timeFormatted ?></span>
                </div>
                <?php endif; ?>
                <div>
                    <small class="text-muted d-block">Questions Answered</small>
                    <span class="fw-semibold text-dark"><?= count($answers) ?> / <?= count($questions) ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Questions & Answers -->
    <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px;">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0 fw-semibold"><i class="fas fa-tasks me-2 text-primary"></i>Your Submitted Responses</h5>
        </div>
        <div class="card-body p-4">
            <?php if (empty($questions)): ?>
                <p class="text-muted mb-0">No questions found for this survey.</p>
            <?php else: ?>
                <?php foreach ($questions as $index => $q): 
                    $qText = is_array($q) ? ($q['question'] ?? $q['text'] ?? "Question " . ($index + 1)) : $q;
                    $qType = is_array($q) ? ($q['type'] ?? 'text') : 'text';
                    $userAns = $answers[$index] ?? null;
                ?>
                <div class="mb-4 pb-3 <?= $index < count($questions) - 1 ? 'border-bottom' : '' ?>">
                    <div class="d-flex align-items-baseline gap-2 mb-2">
                        <span class="badge bg-light text-dark border rounded-circle" style="width: 26px; height: 26px; display: inline-flex; align-items: center; justify-content: center;">
                            <?= $index + 1 ?>
                        </span>
                        <div class="fw-semibold text-dark">
                            <?= htmlspecialchars($qText) ?>
                        </div>
                    </div>

                    <div class="ps-4 ms-2">
                        <?php if ($userAns === null || $userAns === ''): ?>
                            <span class="text-muted fst-italic">No answer provided</span>
                        <?php elseif ($qType === 'rating'): ?>
                            <div class="d-flex align-items-center gap-2">
                                <div class="badge bg-primary fs-6 px-3 py-2">
                                    <i class="fas fa-star text-warning me-1"></i><?= htmlspecialchars($userAns) ?> / 5
                                </div>
                            </div>
                        <?php elseif (is_array($userAns)): ?>
                            <div class="d-flex flex-wrap gap-2">
                                <?php foreach ($userAns as $item): ?>
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1">
                                        <i class="fas fa-check me-1"></i><?= htmlspecialchars($item) ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="p-3 bg-light rounded text-dark" style="white-space: pre-wrap; font-size: 0.95rem;">
                                <?= htmlspecialchars((string)$userAns) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Back Button -->
    <div class="d-flex justify-content-between align-items-center">
        <a href="<?= BASE_URL ?>/employee/self-service/surveys" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Surveys
        </a>
    </div>
</div>
