<?php
/** Admin Engagement Survey Responses */

$page_title = $page_title ?? 'Survey Responses';
$survey = $survey ?? [];
$responses = $responses ?? [];
?>

<style nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
.response-answers { font-size: 0.85rem; }
.answer-row { padding: 0.25rem 0; border-bottom: 1px solid #f1f5f9; }
.answer-label { font-weight: 600; color: #475569; }
.answer-value { color: #1e293b; }
</style>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold"><i class="fas fa-list me-2 text-primary"></i>Survey Responses</h4>
            <p class="text-muted mb-0 small">
                <?= htmlspecialchars($survey['title'] ?? 'Survey') ?>
                <span class="badge bg-<?= 
                    ($survey['status'] ?? '') === 'active' ? 'success' : 
                    (($survey['status'] ?? '') === 'draft' ? 'secondary' : 'warning') 
                ?> ms-2">
                    <?= ucfirst($survey['status'] ?? 'unknown') ?>
                </span>
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= BASE_URL ?>/admin/engagement/surveys/analytics/<?= $survey['id'] ?>" class="btn btn-outline-primary">
                <i class="fas fa-chart-bar me-1"></i> Analytics
            </a>
            <a href="<?= BASE_URL ?>/admin/engagement/surveys" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>

    <?php if (empty($responses)): ?>
    <div class="card shadow-sm border-0">
        <div class="card-body text-center py-5">
            <i class="fas fa-poll fa-3x text-muted mb-3"></i>
            <h5 class="text-muted">No Responses Yet</h5>
            <p class="text-muted small mb-3">Responses will appear here as employees complete the survey</p>
        </div>
    </div>
    <?php else: ?>
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-semibold">Responses (<?= count($responses) ?>)</h5>
            <div class="d-flex gap-2">
                <a href="<?= BASE_URL ?>/admin/engagement/surveys/analytics/<?= $survey['id'] ?>" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-chart-bar me-1"></i> View Analytics
                </a>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 50px">#</th>
                            <th>Employee</th>
                            <th>Submitted</th>
                            <th>Completion Time</th>
                            <th style="width: 100px">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($responses as $index => $response): ?>
                        <tr>
                            <td><?= $index + 1 ?></td>
                            <td>
                                <?php if ($survey['is_anonymous']): ?>
                                    <span class="text-muted fst-italic">Anonymous</span>
                                <?php else: ?>
                                    <div class="fw-semibold"><?= htmlspecialchars($response['employee_name'] ?? 'Unknown') ?></div>
                                    <div class="small text-muted"><?= htmlspecialchars($response['employee_email'] ?? '') ?></div>
                                <?php endif; ?>
                            </td>
                            <td><?= date('d M Y H:i', strtotime($response['submitted_at'])) ?></td>
                            <td>
                                <?php 
                                $time = $response['completion_time_seconds'] ?? 0;
                                if ($time > 0) {
                                    echo $time < 60 ? "{$time}s" : (round($time / 60, 1) . ' min');
                                } else {
                                    echo '<span class="text-muted">—</span>';
                                }
                                ?>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary" onclick="viewResponse(<?= $response['id'] ?>)">
                                    <i class="fas fa-eye"></i> View
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- View Response Modal -->
<div class="modal fade" id="viewResponseModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-poll me-2"></i>Response Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="responseModalBody">
                Loading...
            </div>
        </div>
    </div>
</div>

<script nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
function viewResponse(responseId) {
    const modal = new bootstrap.Modal(document.getElementById('viewResponseModal'));
    const body = document.getElementById('responseModalBody');
    body.innerHTML = '<div class="text-center py-4"><i class="fas fa-spinner fa-spin fa-2x text-primary"></i></div>';
    modal.show();
    
    fetch('<?= BASE_URL ?>/admin/engagement/surveys/response/' + responseId)
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                renderResponse(data.response);
            } else {
                body.innerHTML = '<div class="alert alert-danger">Failed to load response</div>';
            }
        })
        .catch(() => {
            body.innerHTML = '<div class="alert alert-danger">Error loading response</div>';
        });
}

function renderResponse(response) {
    const body = document.getElementById('responseModalBody');
    const answers = response.answers ?? {};
    
    let html = '';
    
    if (!response.is_anonymous) {
        html += '<div class="mb-3 p-3 bg-light rounded">';
        html += '<div class="fw-semibold">' + escapeHtml(response.employee_name) + '</div>';
        html += '<div class="small text-muted">' + escapeHtml(response.employee_email) + ' • ' + escapeHtml(response.department) + '</div>';
        html += '</div>';
    }
    
    html += '<div class="small text-muted mb-3">Submitted: ' + new Date(response.submitted_at).toLocaleString() + '</div>';
    
    if (response.completion_time_seconds > 0) {
        html += '<div class="small text-muted mb-3">Completion Time: ' + 
            (response.completion_time_seconds < 60 ? response.completion_time_seconds + 's' : Math.round(response.completion_time_seconds / 60) + ' min') + '</div>';
    }
    
    html += '<div class="response-answers">';
    Object.entries(answers).forEach(([key, value]) => {
        const qIndex = parseInt(key);
        html += '<div class="answer-row d-flex flex-column">';
        html += '<span class="answer-label">Q' + (qIndex + 1) + '</span>';
        html += '<span class="answer-value">';
        if (Array.isArray(value)) {
            html += value.map(v => '<span class="badge bg-light text-dark me-1" style="background:#f1f5f9;color:#475569;">' + escapeHtml(v) + '</span>').join('');
        } else {
            html += escapeHtml(value);
        }
        html += '</span>';
        html += '</div>';
    });
    html += '</div>';
    
    body.innerHTML = html;
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>