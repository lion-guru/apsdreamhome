<?php
/**
 * AI Management Dashboard — overview of every AI sub-module
 */
$base = defined('BASE_URL') ? rtrim(BASE_URL, '/') : '';
$pages = [
    ['url' => '/admin/ai-management/integrations', 'icon' => 'fas fa-plug', 'color' => 'text-primary', 'title' => 'AI Integrations', 'desc' => 'Enable/disable third-party AI tools and services'],
    ['url' => '/admin/ai-management/lead-scores', 'icon' => 'fas fa-chart-bar', 'color' => 'text-success', 'title' => 'Lead Scores', 'desc' => 'AI lead scoring, hot/cold segmentation, recalc jobs'],
    ['url' => '/admin/ai-management/chatbot-logs', 'icon' => 'fas fa-history', 'color' => 'text-warning', 'title' => 'Chatbot Logs', 'desc' => 'Full conversation history from the floating chatbots'],
    ['url' => '/admin/ai-management/context-memory', 'icon' => 'fas fa-brain', 'color' => 'text-info', 'title' => 'Context Memory', 'desc' => 'Conversation context, memory length and retention'],
    ['url' => '/admin/ai-management/generated-content', 'icon' => 'fas fa-magic', 'color' => 'text-purple', 'title' => 'Generated Content', 'desc' => 'AI-generated descriptions, marketing copy, emails'],
    ['url' => '/admin/ai-management/suggestions', 'icon' => 'fas fa-lightbulb', 'color' => 'text-danger', 'title' => 'AI Suggestions', 'desc' => 'Recommended actions for leads, properties and campaigns'],
    ['url' => '/admin/ai-management/usage-analytics', 'icon' => 'fas fa-chart-pie', 'color' => 'text-secondary', 'title' => 'Usage Analytics', 'desc' => 'Provider usage, token spend, cost by module'],
];
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0"><i class="fas fa-cogs me-2 text-primary"></i>AI Management</h1>
        <p class="text-muted mb-0">Central control for all AI modules — RBAC-aware</p>
    </div>
    <a href="<?= $base ?>/admin/ai/hub" class="btn btn-outline-primary btn-sm"><i class="fas fa-arrow-left me-1"></i>AI Command Center</a>
</div>

<div class="row g-3">
    <?php foreach ($pages as $p): ?>
    <div class="col-md-6 col-xl-4">
        <a href="<?= $base . $p['url'] ?>" class="text-decoration-none">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <i class="<?= $p['icon'] ?> fa-2x <?= $p['color'] ?> mb-2"></i>
                    <h6 class="card-title text-dark"><?= htmlspecialchars($p['title']) ?></h6>
                    <p class="small text-muted mb-0"><?= htmlspecialchars($p['desc']) ?></p>
                </div>
            </div>
        </a>
    </div>
    <?php endforeach; ?>
</div>
