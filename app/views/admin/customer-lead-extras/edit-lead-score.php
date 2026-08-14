<?php
// Session started by controller
$page_title = 'Edit Lead Score';
$page_description = 'Manually adjust lead score';
?>
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-2">Edit Lead Score</h1>
            <p class="text-muted">Manually adjust lead score</p>
            <a href="<?php echo BASE_URL; ?>/admin/customer-lead/lead-scores" class="btn btn-outline-primary mb-3">
                <i class="fas fa-arrow-left me-2"></i> Back to List
            </a>
        </div>
    </div>

    <?php if ($leadScore): ?>
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0">Edit Lead Score for <?php echo htmlspecialchars($leadScore['lead_name'] ?? 'Unknown'); ?></h5>
            </div>
            <div class="card-body aps-cp-card-body">
                <form method="POST" action="<?php echo BASE_URL; ?>/admin/customer-lead/lead-scores/update/<?php echo $leadScore['id']; ?>">
                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Current Score</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-tachometer-alt"></i></span>
                                    <input type="number" class="form-control" value="<?php echo $leadScore['score'] ?? 0; ?>" readonly>
                                </div>
                                <small class="text-muted">Score: <?php echo $leadScore['score'] ?? 0; ?>%</small>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">New Score (0-100)</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-edit"></i></span>
                                    <input type="number" class="form-control" name="score" min="0" max="100" required value="0">
                                </div>
                                <div class="progress mt-2" class="style-51309">
                                    <div class="progress-bar bg-info" id="scoreProgress" class="style-16671"></div>
                                </div>
                                <small class="text-muted" id="scoreValue">0%</small>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Reason for Change</label>
                                <textarea class="form-control" name="reason" rows="3" placeholder="Please provide a reason for manually adjusting this score..."></textarea>
                                <small class="text-muted">This reason will be recorded in the score criteria</small>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Lead Information</label>
                                <div class="border rounded p-3">
                                    <p><strong>Name:</strong> <?php echo htmlspecialchars($leadScore['lead_name'] ?? 'Unknown'); ?></p>
                                    <p><strong>Email:</strong> <?php echo htmlspecialchars($leadScore['lead_email'] ?? ''); ?></p>
                                    <p><strong>Phone:</strong> <?php echo htmlspecialchars($leadScore['lead_phone'] ?? ''); ?></p>
                                    <?php if (!empty($leadScore['criteria'])): ?>
                                        <p><strong>Current Criteria:</strong></p>
                                        <small class="text-muted"><?php echo nl2br(htmlspecialchars($leadScore['criteria'])); ?></small>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Score Impact Preview</label>
                                <div class="alert alert-info">
                                    <p><strong>New Score:</strong> <span id="previewScore">0</span>%</p>
                                    <p><strong>Category:</strong> <span id="previewCategory">Low (0-59)</span></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="fas fa-save me-2"></i> Update Score
                        </button>
                        <a href="<?php echo BASE_URL; ?>/admin/customer-lead/lead-scores" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-2"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    <?php else: ?>
        <div class="alert alert-danger">
            <h4>Lead Score Not Found</h4>
            <p>The requested lead score could not be found.</p>
            <a href="<?php echo BASE_URL; ?>/admin/customer-lead/lead-scores" class="btn btn-primary">
                <i class="fas fa-arrow-left me-2"></i> Back to List
            </a>
        </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const scoreInput = document.querySelector('input[name="score"]');
    const scoreProgress = document.getElementById('scoreProgress');
    const scoreValue = document.getElementById('scoreValue');
    const previewScore = document.getElementById('previewScore');
    const previewCategory = document.getElementById('previewCategory');
    
    function updateScoreDisplay() {
        const value = parseInt(scoreInput.value) || 0;
        scoreProgress.style.width = value + '%';
        scoreValue.textContent = value + '%';
        previewScore.textContent = value;
        
        // Update category
        if (value >= 80) {
            previewCategory.textContent = 'High Risk (80-100)';
            previewCategory.className = 'badge bg-danger';
            scoreProgress.className = 'progress-bar bg-danger';
        } else if (value >= 60) {
            previewCategory.textContent = 'Medium (60-79)';
            previewCategory.className = 'badge bg-warning';
            scoreProgress.className = 'progress-bar bg-warning';
        } else {
            previewCategory.textContent = 'Low (0-59)';
            previewCategory.className = 'badge bg-success';
            scoreProgress.className = 'progress-bar bg-success';
        }
    }
    
    // Initial update
    updateScoreDisplay();
    
    // Update on input change
    scoreInput.addEventListener('input', updateScoreDisplay);
});
</script>