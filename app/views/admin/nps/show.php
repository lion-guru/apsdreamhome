<?php
$page_title = $page_title ?? 'Survey Details';
$page_heading = $page_heading ?? 'NPS Survey';
$content = $content ?? '';
$survey = $survey ?? [];
$responses = $responses ?? [];
$stats = $stats ?? [];
ob_start();
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1"><?= htmlspecialchars($survey['title']) ?></h2>
            <p class="text-muted mb-0">
                <span class="badge bg-<?= $survey['is_active'] ? 'success' : 'secondary' ?>">
                    <?= $survey['is_active'] ? 'Active' : 'Inactive' ?>
                </span>
                · <?= number_format($stats['total_responses'] ?? 0) ?> responses
                · NPS: <span class="text-<?= ($stats['nps_score'] ?? 0) >= 0 ? 'success' : 'danger' ?>"><strong><?= number_format($stats['nps_score'] ?? 0, 1) ?></strong></span>
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= BASE_URL ?>/admin/nps/edit/<?= $survey['id'] ?>" class="btn btn-sm btn-outline-secondary"><i class="fas fa-edit me-1"></i> Edit</a>
            <a href="<?= BASE_URL ?>/admin/nps" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Back to List</a>
            <a href="<?= BASE_URL ?>/admin/nps/delete?id=<?= $survey['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this survey and all responses?')"><i class="fas fa-trash"></i></a>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body aps-cp-card-body">
                    <h6 class="mb-3">Survey Details</h6>
                    <p><strong>Question:</strong> <?= htmlspecialchars($survey['question_text']) ?></p>
                    <p><strong>Scale:</strong> <?= htmlspecialchars($survey['scale_min_label']) ?> (0) to <?= htmlspecialchars($survey['scale_max_label']) ?> (10)</p>
                    <?php if ($survey['follow_up_question']): ?>
                        <p><strong>Follow-up:</strong> <?= htmlspecialchars($survey['follow_up_question']) ?></p>
                    <?php endif; ?>
                    <p><strong>Trigger:</strong> <?= ucfirst(str_replace('_', ' ', $survey['trigger_event'])) ?></p>
                    <p><strong>Created:</strong> <?= date('M j, Y H:i', strtotime($survey['created_at'])) ?> by <?= htmlspecialchars($survey['creator_name'] ?? 'Unknown') ?></p>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h6 class="mb-0">Response Statistics</h6>
                </div>
                <div class="card-body aps-cp-card-body">
                    <?php if (!empty($stats)): ?>
                        <div class="row g-2">
                            <div class="col-md-6">
                                <div class="text-center">
                                    <small class="text-muted d-block">Promoters (9-10)</small>
                                    <h3 class="text-success"><?= number_format($stats['promoters'] ?? 0) ?></h3>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="text-center">
                                    <small class="text-muted d-block">Passives (7-8)</small>
                                    <h3 class="text-warning"><?= number_format($stats['passives'] ?? 0) ?></h3>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="text-center">
                                    <small class="text-muted d-block">Detractors (0-6)</small>
                                    <h3 class="text-danger"><?= number_format($stats['detractors'] ?? 0) ?></h3>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="text-center">
                                    <small class="text-muted d-block">Average Score</small>
                                    <h3 class="text-info"><?= number_format($stats['avg_score'] ?? 0, 2) ?></h3>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mt-4">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0"><i class="fas fa-comments me-2"></i>Recent Responses (<?= count($responses) ?>)</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Respondent</th>
                            <th>Score</th>
                            <th>Category</th>
                            <th>Follow-up</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($responses)): ?>
                            <tr><td colspan="6" class="text-center text-muted py-4"><i class="fas fa-inbox fa-2x d-block mb-2 text-muted" aria-hidden="true"></i>No responses yet</td></tr>
                        <?php else: ?>
                            <?php foreach ($responses as $r): ?>
                                <tr>
                                    <td>#<?= $r['id'] ?></td>
                                    <td>
                                        <?php if ($r['user_name']): ?>
                                            <strong><?= htmlspecialchars($r['user_name']) ?></strong>
                                            <?php if ($r['visitor_name'] && $r['visitor_name'] !== $r['user_name']): ?>
                                                <br><small class="text-muted"><?= htmlspecialchars($r['visitor_name']) ?></small>
                                            <?php endif; ?>
                                        <?php elseif ($r['visitor_name']): ?>
                                            <strong><?= htmlspecialchars($r['visitor_name']) ?></strong>
                                        <?php else: ?>
                                            <span class="text-muted">Anonymous</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($r['score'] >= 9): ?>
                                            <span class="badge bg-success"><?= $r['score'] ?></span>
                                        <?php elseif ($r['score'] >= 7): ?>
                                            <span class="badge bg-warning"><?= $r['score'] ?></span>
                                        <?php else: ?>
                                            <span class="badge bg-danger"><?= $r['score'] ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= [$r['category'] === 'promoter' => 'success', $r['category'] === 'passive' => 'warning', $r['category'] === 'detractor' => 'danger'][$r['category']] ?? 'secondary' ?>">
                                            <?= ucfirst($r['category']) ?>
                                        </span>
                                    </td>
                                    <td><?= htmlspecialchars($r['follow_up_answer'] ?? '') ?></td>
                                    <td><small><?= date('M j, H:i', strtotime($r['responded_at'])) ?></small></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
include APP_PATH . '/views/layouts/admin.php';
