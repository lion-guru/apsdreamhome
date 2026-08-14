<?php
// Data passed from route closure (routes/web.php /admin/ai/dashboard)
$stats = $stats ?? [];
$recentMessages = $recentMessages ?? [];
$topIntents = $topIntents ?? [];
$topScores = $topScores ?? [];
$priceModels = $priceModels ?? [];

$page_title = $page_title ?? 'AI Dashboard - APS Dream Home';
$page_heading = $page_heading ?? 'Self-Learning AI';
$content = ob_start();
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3"><i class="fas fa-brain"></i> Self-Learning AI Dashboard</h1>
        <button class="btn btn-primary" onclick="retrainAI()">
            <i class="fas fa-sync"></i> Retrain All Models
        </button>
    </div>

    <div class="alert alert-info">
        <i class="fas fa-info-circle"></i>
        <strong>Self-Hosted AI:</strong> No external APIs. All intelligence runs locally on our infrastructure.
        Every interaction is learned and used to improve responses.
    </div>

    <div class="row">
        <div class="col-md-3">
            <div class="card text-white bg-primary mb-3">
                <div class="card-body aps-cp-card-body">
                    <h5 class="card-title"><?= $stats['learning_events'] ?></h5>
                    <p class="card-text">Learning Events</p>
                    <small>+<?= $stats['learnings_24h'] ?> in 24h</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-success mb-3">
                <div class="card-body aps-cp-card-body">
                    <h5 class="card-title"><?= $stats['intent_patterns'] ?></h5>
                    <p class="card-text">Intent Patterns</p>
                    <small>Multi-language (Hindi/English)</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-info mb-3">
                <div class="card-body aps-cp-card-body">
                    <h5 class="card-title"><?= $stats['chat_sessions'] ?></h5>
                    <p class="card-text">Chat Sessions</p>
                    <small>+<?= $stats['chat_sessions_24h'] ?> in 24h</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-warning mb-3">
                <div class="card-body aps-cp-card-body">
                    <h5 class="card-title"><?= $stats['lead_scores'] ?></h5>
                    <p class="card-text">Leads Scored</p>
                    <small>AI-grade quality</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-4">
            <div class="card aps-cp-card">
                <div class="card-header aps-cp-card-header"><i class="fas fa-user-friends"></i> User Profiles</div>
                <div class="card-body aps-cp-card-body">
                    <h2><?= $stats['user_profiles'] ?></h2>
                    <p class="text-muted">Auto-learned from behavior</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card aps-cp-card">
                <div class="card-header aps-cp-card-header"><i class="fas fa-magic"></i> Recommendations</div>
                <div class="card-body aps-cp-card-body">
                    <h2><?= $stats['recommendations'] ?></h2>
                    <p class="text-muted">Personalized for users</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card aps-cp-card">
                <div class="card-header aps-cp-card-header"><i class="fas fa-chart-line"></i> Price Models</div>
                <div class="card-body aps-cp-card-body">
                    <h2><?= $stats['price_models'] ?></h2>
                    <p class="text-muted">ML regression models</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card aps-cp-card">
                <div class="card-header aps-cp-card-header"><i class="fas fa-comments"></i> Recent Chat Messages</div>
                <div class="card-body aps-cp-card-body" class="style-61454">
                    <?php foreach ($recentMessages as $m): ?>
                        <div class="mb-2 p-2 <?= $m['sender'] === 'user' ? 'bg-light' : 'bg-info text-white' ?>" class="style-2723">
                            <div><strong><?= $m['sender'] === 'user' ? 'ðŸ‘¤ User' : 'ðŸ¤– Bot' ?>:</strong> <?= htmlspecialchars(substr($m['message'], 0, 100)) ?></div>
                            <?php if ($m['detected_intent']): ?>
                                <small>Intent: <code><?= $m['detected_intent'] ?></code> (conf: <?= round($m['confidence'] * 100) ?>%)</small>
                            <?php endif; ?>
                            <div class="text-end"><small><?= $m['created_at'] ?></small></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card aps-cp-card">
                <div class="card-header aps-cp-card-header"><i class="fas fa-chart-bar"></i> Top Detected Intents</div>
                <div class="card-body aps-cp-card-body">
                    <div class="table-responsive"><table class="table">
                        <thead><tr><th>Intent</th><th>Count</th></tr></thead>
                        <tbody>
                            <?php foreach ($topIntents as $i): ?>
                                <tr>
                                    <td><code><?= htmlspecialchars($i['detected_intent']) ?></code></td>
                                    <td><span class="badge bg-primary"><?= $i['cnt'] ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card aps-cp-card">
                <div class="card-header aps-cp-card-header"><i class="fas fa-fire"></i> Top Scored Leads (AI-Powered)</div>
                <div class="card-body aps-cp-card-body">
                    <div class="table-responsive"><table class="table">
                        <thead>
                            <tr>
                                <th>Lead</th>
                                <th>Phone</th>
                                <th>Score</th>
                                <th>Grade</th>
                                <th>Predicted Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($topScores as $s): ?>
                                <tr>
                                    <td><?= htmlspecialchars($s['name'] ?? 'Lead #' . $s['lead_id']) ?></td>
                                    <td><?= htmlspecialchars($s['phone'] ?? '-') ?></td>
                                    <td><strong><?= $s['score'] ?></strong>/100</td>
                                    <td>
                                        <span class="badge bg-<?= match($s['grade']) { 'A' => 'success', 'B' => 'info', 'C' => 'warning', default => 'secondary' } ?>">
                                            <?= $s['grade'] ?>
                                        </span>
                                    </td>
                                    <td><small><?= htmlspecialchars($s['predicted_action'] ?? '-') ?></small></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card aps-cp-card">
                <div class="card-header aps-cp-card-header"><i class="fas fa-tags"></i> Price Prediction Models</div>
                <div class="card-body aps-cp-card-body">
                    <div class="table-responsive"><table class="table">
                        <thead>
                            <tr><th>Type</th><th>District</th><th>RÂ²</th><th>Samples</th><th>Trained At</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($priceModels as $m): ?>
                                <tr>
                                    <td><?= $m['property_type'] ?></td>
                                    <td><?= $m['location_id'] ?? 'All' ?></td>
                                    <td><?= round($m['r_squared'], 3) ?></td>
                                    <td><?= $m['sample_size'] ?></td>
                                    <td><?= $m['trained_at'] ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function retrainAI() {
    if (!confirm('Retrain all AI models? This may take a moment.')) return;
    fetch('<?= BASE_URL ?>/api/ai/retrain', { method: 'POST' })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                alert('âœ“ AI models retrained successfully!');
                location.reload();
            } else {
                alert('Error: ' + (data.error || 'unknown'));
            }
        });
}
</script>
<?php
$content = ob_get_clean();
