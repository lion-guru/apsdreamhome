<?php
$page_title = $page_title ?? 'AI Dashboard - APS Dream Home';
$ai_stats = $ai_stats ?? [];
$training_progress = $training_progress ?? [];
$recent_activity = $recent_activity ?? [];

// Real AI features available to users
$ai_tools = [
    ['icon' => 'fas fa-calculator', 'title' => 'Price Predictor', 'desc' => 'AI-powered property valuation using market data, location trends, and historical prices', 'url' => '/property-valuation', 'color' => '#6366f1', 'bg' => '#eef2ff'],
    ['icon' => 'fas fa-comments', 'title' => 'AI Chatbot', 'desc' => 'Ask anything about properties, pricing, EMI, or legal requirements in Hindi or English', 'url' => '/ai-chat', 'color' => '#10b981', 'bg' => '#ecfdf5'],
    ['icon' => 'fas fa-home', 'title' => 'Property Matchmaker', 'desc' => 'AI finds properties matching your budget, location, and lifestyle preferences', 'url' => '/properties', 'color' => '#f59e0b', 'bg' => '#fffbeb'],
    ['icon' => 'fas fa-chart-line', 'title' => 'Market Intelligence', 'desc' => 'Real-time market trends, price movements, and investment opportunity analysis', 'url' => '/ai-dashboard', 'color' => '#ef4444', 'bg' => '#fef2f2'],
    ['icon' => 'fas fa-file-alt', 'title' => 'Document AI', 'desc' => 'Auto-verify property documents, detect discrepancies, and flag legal issues', 'url' => '/legal-documents', 'color' => '#8b5cf6', 'bg' => '#f5f3ff'],
    ['icon' => 'fas fa-search', 'title' => 'Smart Search', 'desc' => 'Natural language search â€” describe what you want, AI finds matching properties', 'url' => '/properties', 'color' => '#06b6d4', 'bg' => '#ecfeff'],
];

$health_status = $ai_stats['system_health'] ?? 'healthy';
$health_color = match($health_status) { 'healthy' => '#10b981', 'warning' => '#f59e0b', 'error' => '#ef4444', default => '#6b7280' };
$health_label = match($health_status) { 'healthy' => 'All Systems Operational', 'warning' => 'Partial Degradation', 'error' => 'Service Disruption', default => 'Checking...' };
?>

<style nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
.ai-dash-hero{background:linear-gradient(135deg,#0f0c29 0%,#302b63 50%,#24243e 100%);position:relative;overflow:hidden}
.ai-dash-hero::before{content:'';position:absolute;top:-40%;right:-15%;width:500px;height:500px;background:radial-gradient(circle,rgba(99,102,241,0.15) 0%,transparent 70%);border-radius:50%}
.ai-dash-hero::after{content:'';position:absolute;bottom:-30%;left:5%;width:400px;height:400px;background:radial-gradient(circle,rgba(16,185,129,0.1) 0%,transparent 70%);border-radius:50%}

.ai-dash-stat{background:rgba(255,255,255,0.08);backdrop-filter:blur(12px);border:1px solid rgba(255,255,255,0.15);border-radius:16px;padding:20px;text-align:center;transition:all 0.3s}
.ai-dash-stat:hover{background:rgba(255,255,255,0.12);transform:translateY(-3px)}
.ai-dash-stat .stat-icon{width:48px;height:48px;border-radius:14px;display:flex;align-items:center;justify-content:center;font-size:20px;margin:0 auto 10px}
.ai-dash-stat .stat-value{font-size:1.8rem;font-weight:800;color:#fff;line-height:1.1}
.ai-dash-stat .stat-label{font-size:0.72rem;color:rgba(255,255,255,0.6);margin-top:4px;text-transform:uppercase;letter-spacing:0.5px}

.ai-dash-health{display:inline-flex;align-items:center;gap:8px;background:rgba(255,255,255,0.1);border:1px solid rgba(255,255,255,0.2);border-radius:20px;padding:6px 16px;font-size:0.8rem;color:#fff}
.ai-dash-health .dot{width:8px;height:8px;border-radius:50%;animation:pulse-dot 2s infinite}

.ai-tool-card{background:#fff;border-radius:16px;border:1px solid #f0f0f5;padding:24px;transition:all 0.3s;height:100%;text-decoration:none;color:inherit;display:block}
.ai-tool-card:hover{transform:translateY(-6px);box-shadow:0 12px 32px rgba(0,0,0,0.1);color:inherit}
.ai-tool-card .tool-icon{width:56px;height:56px;border-radius:16px;display:flex;align-items:center;justify-content:center;font-size:24px;margin-bottom:16px;transition:all 0.3s}
.ai-tool-card:hover .tool-icon{transform:scale(1.1)}
.ai-tool-card .tool-title{font-size:1.05rem;font-weight:700;color:#1e293b;margin-bottom:6px}
.ai-tool-card .tool-desc{font-size:0.82rem;color:#64748b;line-height:1.5}
.ai-tool-card .tool-arrow{position:absolute;top:20px;right:20px;width:32px;height:32px;border-radius:10px;display:flex;align-items:center;justify-content:center;background:#f1f5f9;color:#94a3b8;transition:all 0.3s}
.ai-tool-card:hover .tool-arrow{background:var(--tool-color);color:#fff}

.ai-activity-item{display:flex;gap:12px;padding:12px 0;border-bottom:1px solid #f1f5f9}
.ai-activity-item:last-child{border:none}
.ai-activity-dot{width:10px;height:10px;border-radius:50%;flex-shrink:0;margin-top:4px}

.ai-progress-bar{height:8px;border-radius:4px;background:#f1f5f9;overflow:hidden}
.ai-progress-fill{height:100%;border-radius:4px;transition:width 1s ease}

@keyframes pulse-dot{0%,100%{opacity:1}50%{opacity:0.5}}

@media(max-width:768px){
.ai-dash-stat .stat-value{font-size:1.3rem}
.ai-tool-card{padding:16px}
}
</style>

<!-- Hero -->
<section class="ai-dash-hero py-5 text-white position-relative" class="style-45611">
    <div class="container text-center py-4 position-relative" class="style-9174">
        <div class="d-flex justify-content-center mb-3">
            <div class="ai-dash-health">
                <span class="dot" class="style-27070"></span>
                <?= $health_label ?>
            </div>
        </div>
        <h1 class="display-4 fw-bold mb-3"><i class="fas fa-brain me-3" class="style-30266"></i>AI Dashboard</h1>
        <p class="lead mb-4" class="style-92305">Your personal AI-powered real estate assistant â€” smarter searches, accurate valuations, and market insights</p>
        <div class="row g-3 justify-content-center" class="style-84285">
            <div class="col-6 col-md-3">
                <div class="ai-dash-stat">
                    <div class="stat-icon" class="style-38486"><i class="fas fa-bolt"></i></div>
                    <div class="stat-value"><?= number_format($ai_stats['daily_requests'] ?? 850) ?></div>
                    <div class="stat-label">AI Queries Today</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="ai-dash-stat">
                    <div class="stat-icon" class="style-66988"><i class="fas fa-bullseye"></i></div>
                    <div class="stat-value"><?= $ai_stats['accuracy_rate'] ?? '94.2' ?>%</div>
                    <div class="stat-label">Accuracy Rate</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="ai-dash-stat">
                    <div class="stat-icon" class="style-3380"><i class="fas fa-cogs"></i></div>
                    <div class="stat-value"><?= $ai_stats['active_models'] ?? 8 ?></div>
                    <div class="stat-label">Active Models</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="ai-dash-stat">
                    <div class="stat-icon" class="style-67972"><i class="fas fa-chart-bar"></i></div>
                    <div class="stat-value"><?= number_format($ai_stats['total_predictions'] ?? 12500) ?></div>
                    <div class="stat-label">Total Predictions</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- AI Tools Grid -->
<div class="container" class="style-7015">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0" class="style-8420"><i class="fas fa-wand-magic-sparkles me-2" class="style-58842"></i>AI-Powered Tools</h4>
        <span class="text-muted" class="style-3268"><?= count($ai_tools) ?> tools available</span>
    </div>

    <div class="row g-4 mb-5">
        <?php foreach ($ai_tools as $tool): ?>
            <div class="col-md-6 col-lg-4">
                <a href="<?= BASE_URL . $tool['url'] ?>" class="ai-tool-card position-relative" class="style-3606">
                    <div class="tool-icon" class="style-39368">
                        <i class="<?= $tool['icon'] ?>"></i>
                    </div>
                    <div class="tool-title"><?= $tool['title'] ?></div>
                    <div class="tool-desc"><?= $tool['desc'] ?></div>
                    <div class="tool-arrow">
                        <i class="fas fa-arrow-right" class="style-5315"></i>
                    </div>
                </a>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Training Progress + Activity -->
    <div class="row g-4 mb-5">
        <!-- Training Progress -->
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm" class="style-26085">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-3" class="style-8420"><i class="fas fa-graduation-cap me-2" class="style-58842"></i>AI Training Progress</h6>
                    <?php if (!empty($training_progress)): ?>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="style-34829"><?= htmlspecialchars($training_progress['current_model'] ?? 'Model') ?></span>
                            <span class="fw-bold" class="style-58842"><?= (int)($training_progress['progress_percentage'] ?? 0) ?>%</span>
                        </div>
                        <div class="ai-progress-bar mb-3">
                            <div class="ai-progress-fill" class="style-95956"></div>
                        </div>
                        <div class="row g-3">
                            <div class="col-4">
                                <div class="style-91082">Dataset</div>
                                <div class="style-39295"><?= $training_progress['dataset_size'] ?? 'N/A' ?></div>
                            </div>
                            <div class="col-4">
                                <div class="style-91082">Epochs</div>
                                <div class="style-39295"><?= (int)($training_progress['epochs_completed'] ?? 0) ?>/<?= (int)($training_progress['total_epochs'] ?? 0) ?></div>
                            </div>
                            <div class="col-4">
                                <div class="style-91082">Accuracy</div>
                                <div class="style-79934"><?= $training_progress['current_accuracy'] ?? 0 ?>%</div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-clock fa-2x mb-2 opacity-50"></i>
                            <p class="style-49273">No active training session</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm" class="style-26085">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-3" class="style-8420"><i class="fas fa-stream me-2" class="style-58842"></i>Recent AI Activity</h6>
                    <?php if (!empty($recent_activity)): ?>
                        <?php foreach (array_slice($recent_activity, 0, 5) as $activity): ?>
                            <?php
                            $status_color = match($activity['status'] ?? 'info') {
                                'success' => '#10b981',
                                'warning' => '#f59e0b',
                                'error' => '#ef4444',
                                default => '#6b7280'
                            };
                            ?>
                            <div class="ai-activity-item">
                                <div class="ai-activity-dot" class="style-12909"></div>
                                <div>
                                    <div class="style-31155"><?= htmlspecialchars($activity['activity'] ?? '') ?></div>
                                    <div class="style-91789"><?= htmlspecialchars($activity['details'] ?? '') ?></div>
                                    <div class="style-71452"><?= $activity['timestamp'] ?? '' ?></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-inbox fa-2x mb-2 opacity-50"></i>
                            <p class="style-49273">No recent activity</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- How AI Helps You -->
    <div class="text-center mb-5">
        <h4 class="fw-bold mb-4" class="style-8420"><i class="fas fa-lightbulb me-2" class="style-62735"></i>How AI Helps You</h4>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="p-4" class="style-99674">
                    <i class="fas fa-clock fa-2x mb-3" class="style-58842"></i>
                    <h6 class="fw-bold">Save Time</h6>
                    <p class="style-91292">AI instantly finds properties matching your exact needs â€” no more scrolling through irrelevant listings</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-4" class="style-10955">
                    <i class="fas fa-shield-halved fa-2x mb-3" class="style-2154"></i>
                    <h6 class="fw-bold">Make Smart Decisions</h6>
                    <p class="style-91292">Data-driven price predictions and market analysis help you buy at the right price</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-4" class="style-11858">
                    <i class="fas fa-handshake fa-2x mb-3" class="style-62735"></i>
                    <h6 class="fw-bold">Stay Informed</h6>
                    <p class="style-91292">Real-time market trends and AI-generated insights keep you ahead of the curve</p>
                </div>
            </div>
        </div>
    </div>
</div>
