<?php

// TODO: Add proper error handling with try-catch blocks

$page_title = 'AI Dashboard - APS Dream Home';
$page_description = 'Advanced AI agent monitoring and management interface';
include __DIR__ . '/../layouts/base.php';
?>

<div class="container-fluid mt-4">
    <div class="row">
        <!-- AI Agent Control Panel -->
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-header bg-gradient-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-robot me-2"></i>
                        AI Agent Control Panel
                    </h5>
                </div>
                <div class="card-body">
                    <div class="ai-agent-status mb-4">
                        <div class="d-flex align-items-center mb-2">
                            <div class="ai-avatar me-3">
                                <i class="fas fa-robot fa-2x text-primary"></i>
                            </div>
                            <div>
                                <h6 class="mb-0">APS Assistant</h6>
                                <small class="text-muted">AI Agent v2.1</small>
                            </div>
                        </div>
                        <div class="mood-indicator">
                            <span class="badge bg-success">🧠 Learning Active</span>
                            <span class="badge bg-info ms-1">💬 Interactive</span>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="quick-actions">
                        <h6><i class="fas fa-bolt me-2"></i>Quick Actions</h6>
                        <div class="d-grid gap-2">
                            <button class="btn btn-outline-primary btn-sm" onclick="trainAI()">
                                <i class="fas fa-graduation-cap me-2"></i>Train AI
                            </button>
                            <button class="btn btn-outline-success btn-sm" onclick="resetAI()">
                                <i class="fas fa-redo me-2"></i>Reset Memory
                            </button>
                            <button class="btn btn-outline-warning btn-sm" onclick="exportData()">
                                <i class="fas fa-download me-2"></i>Export Data
                            </button>
                        </div>
                    </div>

                    <!-- AI Statistics -->
                    <div class="ai-stats mt-4">
                        <h6><i class="fas fa-chart-line me-2"></i>AI Statistics</h6>
                        <div class="row text-center">
                            <div class="col-6">
                                <div class="stat-item">
                                    <div class="stat-value">1,247</div>
                                    <div class="stat-label">Conversations</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="stat-item">
                                    <div class="stat-value">98.5%</div>
                                    <div class="stat-label">Accuracy</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Dashboard Area -->
        <div class="col-md-9">
            <!-- AI Performance Metrics -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="metric-card">
                        <div class="metric-value">98.5%</div>
                        <div class="metric-label">AI Accuracy</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="metric-card">
                        <div class="metric-value">24/7</div>
                        <div class="metric-label">Availability</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="metric-card">
                        <div class="metric-value">1.2s</div>
                        <div class="metric-label">Response Time</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="metric-card">
                        <div class="metric-value">4.8</div>
                        <div class="metric-label">User Rating</div>
                    </div>
                </div>
            </div>

            <!-- AI Training Interface -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5><i class="fas fa-graduation-cap me-2"></i>AI Training Interface</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="training-section">
                                <h6>Property Knowledge Base</h6>
                                <div class="progress mb-2">
                                    <div class="progress-bar bg-success" style="width: 85%">85%</div>
                                </div>
                                <small class="text-muted">425 properties learned</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="training-section">
                                <h6>Customer Interaction Patterns</h6>
                                <div class="progress mb-2">
                                    <div class="progress-bar bg-info" style="width: 92%">92%</div>
                                </div>
                                <small class="text-muted">1,247 interactions analyzed</small>
                            </div>
                        </div>
                    </div>

                    <div class="mt-3">
                        <button class="btn btn-primary" onclick="startTraining()">
                            <i class="fas fa-play me-2"></i>Start Training Session
                        </button>
                        <button class="btn btn-outline-secondary ms-2" onclick="viewTrainingLog()">
                            <i class="fas fa-file-alt me-2"></i>View Training Log
                        </button>
                    </div>
                </div>
            </div>

            <!-- Recent AI Interactions -->
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-comments me-2"></i>Recent AI Interactions</h5>
                </div>
                <div class="card-body">
                    <div class="activity-timeline">
                        <div class="activity-item">
                            <div class="d-flex align-items-start">
                                <div class="activity-icon activity-ai">
                                    <i class="fas fa-robot"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h6>Property Recommendation</h6>
                                    <p class="mb-1">AI recommended 3 luxury apartments in Gomti Nagar based on user preferences</p>
                                    <small class="text-muted">2 minutes ago</small>
                                </div>
                            </div>
                        </div>

                        <div class="activity-item">
                            <div class="d-flex align-items-start">
                                <div class="activity-icon activity-whatsapp">
                                    <i class="fas fa-whatsapp"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h6>WhatsApp Follow-up</h6>
                                    <p class="mb-1">AI sent automated follow-up message for property inquiry</p>
                                    <small class="text-muted">15 minutes ago</small>
                                </div>
                            </div>
                        </div>

                        <div class="activity-item">
                            <div class="d-flex align-items-start">
                                <div class="activity-icon activity-email">
                                    <i class="fas fa-envelope"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h6>Email Campaign</h6>
                                    <p class="mb-1">AI generated personalized email campaign for 50 users</p>
                                    <small class="text-muted">1 hour ago</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .metric-card {
        text-align: center;
        padding: 20px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 10px;
        margin: 10px 0;
    }

    .metric-value {
        font-size: 2.5em;
        font-weight: bold;
    }

    .metric-label {
        font-size: 0.9em;
        opacity: 0.9;
    }

    .ai-avatar {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.1);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .stat-item {
        padding: 10px;
    }

    .stat-value {
        font-size: 1.5em;
        font-weight: bold;
        color: var(--primary-color);
    }

    .stat-label {
        font-size: 0.8em;
        color: var(--gray-600);
    }

    .activity-timeline {
        position: relative;
        padding-left: 30px;
    }

    .activity-timeline::before {
        content: '';
        position: absolute;
        left: 15px;
        top: 0;
        bottom: 0;
        width: 2px;
        background: #dee2e6;
    }

    .activity-item {
        position: relative;
        margin: 15px 0;
        padding: 15px;
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .activity-item::before {
        content: '';
        position: absolute;
        left: -23px;
        top: 20px;
        width: 12px;
        height: 12px;
        background: #007bff;
        border-radius: 50%;
        border: 3px solid white;
    }

    .activity-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-right: 15px;
    }

    .activity-ai {
        background: #6f42c1;
        color: white;
    }

    .activity-whatsapp {
        background: #25d366;
        color: white;
    }

    .activity-email {
        background: #007bff;
        color: white;
    }
</style>

<script>
    function trainAI() {
        alert('AI Training session started. This may take a few minutes...');
        // Implement AI training logic
    }

    function resetAI() {
        if (confirm('Are you sure you want to reset AI memory? This action cannot be undone.')) {
            alert('AI memory reset successfully.');
            // Implement AI reset logic
        }
    }

    function exportData() {
        alert('Exporting AI training data...');
        // Implement data export logic
    }

    function startTraining() {
        alert('Training session initiated. Monitor progress in the training log.');
        // Implement training session logic
    }

    function viewTrainingLog() {
        alert('Opening training log...');
        // Implement training log viewer
    }
</script>