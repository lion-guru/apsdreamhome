<?php include APP_PATH . '/views/admin/layouts/header.php'; ?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <h2 class="mb-4"><i class="fas fa-server me-2"></i>Infrastructure & API Dashboard</h2>
        </div>
    </div>

    <!-- System Metrics -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card bg-dark text-white shadow-sm">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-heartbeat me-2 text-danger"></i>Server Health</h5>
                    <div class="row mt-4">
                        <div class="col-6">
                            <p class="mb-1 text-muted">Uptime</p>
                            <h4><?php echo $server_health['uptime']; ?></h4>
                        </div>
                        <div class="col-6">
                            <p class="mb-1 text-muted">Memory Usage</p>
                            <h4><?php echo $server_health['memory_usage']; ?></h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card bg-white shadow-sm">
                <div class="card-body">
                    <h5 class="card-title text-primary"><i class="fas fa-plug me-2"></i>AI API Usage (Today)</h5>
                    <div class="mt-4">
                        <div class="d-flex justify-content-between mb-1">
                            <span>OpenRouter</span>
                            <span class="fw-bold"><?php echo $api_usage['openrouter_calls']; ?> calls</span>
                        </div>
                        <div class="progress mb-3" style="height: 10px;">
                            <div class="progress-bar bg-primary" style="width: 65%"></div>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span>Gemini</span>
                            <span class="fw-bold"><?php echo $api_usage['gemini_calls']; ?> calls</span>
                        </div>
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar bg-info" style="width: 35%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- AI Agents Status -->
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Active AI Agents</h5>
                    <span class="badge bg-success">All Systems Operational</span>
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php foreach ($ai_agents_status as $agent): ?>
                        <div class="col-md-4 mb-3">
                            <div class="p-3 border rounded bg-light">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="mb-0"><?php echo $agent['name']; ?></h6>
                                    <span class="badge bg-success">Online</span>
                                </div>
                                <p class="small text-muted mb-0">Type: <?php echo $agent['type']; ?></p>
                                <p class="small text-muted">Mood: <?php echo $agent['mood']; ?></p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include APP_PATH . '/views/admin/layouts/footer.php'; ?>
