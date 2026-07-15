<?php
/**
 * AI Calling Training View
 * Data: $page_title
 */
$page_title = $page_title ?? 'AI Calling Training';
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="fas fa-robot me-2"></i><?= htmlspecialchars($page_title) ?></h2>
        <a href="<?= BASE_URL ?>/admin/ai/hub" class="btn btn-outline-primary"><i class="fas fa-home me-1"></i> AI Hub</a>
    </div>

    <div class="alert alert-info">
        <i class="fas fa-info-circle me- me-2"></i>
        <strong>AI Calling Training</strong> - This module is currently under development.
        <br>It will include:
        <ul class="mb-0 mt-2">
            <li>Training data management for voice models</li>
            <li>Conversation script management</li>
            <li>Intent recognition training</li>
            <li>Model evaluation and testing</li>
            <li>Real-time call monitoring</li>
        </ul>
    </div>

    <div class="row g-3">
        <div class="col-md-6">
            <div class="card aps-cp-card h-100">
                <div class="card-header aps-cp-card-header"><i class="fas fa-microphone me-2"></i>Voice Training</div>
                <div class="card-body">
                    <div class="text-center py-4">
                        <i class="fas fa-microphone fa-3x text-muted mb-3"></i>
                        <h5>Voice Model Training</h5>
                        <p class="text-muted">Upload audio samples and train custom voice models for calling.</p>
                        <button class="btn btn-outline-primary" disabled>Coming Soon</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card aps-cp-card h-100">
                <div class="card-header aps-cp-card-header"><i class="fas fa-comments me-2"></i>Conversation Scripts</div>
                <div class="card-body">
                    <div class="text-center py-4">
                        <i class="fas fa-comments fa-3x text-muted mb-3"></i>
                        <h5>Script Management</h5>
                        <p class="text-muted">Create and manage conversation scripts for different scenarios.</p>
                        <button class="btn btn-outline-primary" disabled>Coming Soon</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card aps-cp-card h-100">
                <div class="card-header aps-cp-card-header"><i class="fas fa-brain me-2"></i>Intent Recognition</div>
                <div class="card-body">
                    <div class="text-center py-4">
                        <i class="fas fa-brain fa-3x text-muted mb-3"></i>
                        <h5>Intent Training</h5>
                        <p class="text-muted">Train and improve intent recognition for customer queries.</p>
                        <button class="btn btn-outline-primary" disabled>Coming Soon</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card aps-cp-card h-100">
                <div class="card-header aps-cp-card-header"><i class="fas fa-chart-line me-2"></i>Performance</div>
                <div class="card-body">
                    <div class="text-center py-4">
                        <i class="fas fa-chart-line fa-3x text-muted mb-3"></i>
                        <h5>Model Performance</h5>
                        <p class="text-muted">Monitor and evaluate AI calling model performance metrics.</p>
                        <button class="btn btn-outline-primary" disabled>Coming Soon</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>