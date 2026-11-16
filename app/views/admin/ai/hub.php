<?php
/**
 * AI Command Center (AI Hub)
 */
$health = $rag_health ?? [];
$kb = $kb_counts ?? ['public' => 0, 'staff' => 0, 'admin' => 0];
$roles = $supported_roles ?? [];
$base = defined('BASE_URL') ? rtrim(BASE_URL, '/') : '';
$degraded = !empty($health['degraded']);

// RBAC-check helpers: which module does THIS user's role unlock?
$isAdmin = in_array($_SESSION['admin_role'] ?? $_SESSION['role'] ?? 'admin', ['super_admin', 'admin'], true);
$isManager = in_array($_SESSION['admin_role'] ?? $_SESSION['role'] ?? 'admin', ['manager', 'hr_admin', 'sales_manager', 'marketing_manager'], true);
?>
<div class="container-fluid px-4 py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="mb-1"><i class="fas fa-robot me-2 text-primary"></i>AI Command Center</h1>
            <p class="text-muted mb-0">Every AI capability in one place — RBAC-aware, health-verified.</p>
        </div>
        <div>
            <a href="<?= $base ?>/admin/ai/executive-assistant" class="btn btn-primary">
                <i class="fas fa-bolt me-1"></i> Executive Assistant
            </a>
        </div>
    </div>

    <?php if (!empty($ai_degraded)): ?>
    <div class="alert alert-danger d-flex align-items-center gap-2 mb-4" role="alert">
        <i class="fas fa-triangle-exclamation flex-shrink-0"></i>
        <div>
            <strong>AI engines degraded</strong> — the free LLM layer is currently unavailable,
            so the public chatbot is answering from its offline corpus instead of live models.
            <span class="small text-muted">Fresh check: <code>php scripts/cron_ai_health.php</code></span>
        </div>
    </div>
    <?php endif; ?>

    <!-- Health strip -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body">
                    <div class="text-muted small">Embedding provider</div>
                    <div class="h5 mb-0 <?= $degraded ? 'text-danger' : 'text-success' ?>">
                        <?= $degraded ? 'Degraded' : htmlspecialchars($health['probe_provider'] ?? 'Ready') ?>
                    </div>
                    <div class="small text-muted"><?= htmlspecialchars($health['probe_model'] ?? '') ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body">
                    <div class="text-muted small">KB documents (public)</div>
                    <div class="h5 mb-0 text-success"><?= (int)$kb['public'] ?></div>
                    <div class="small text-muted">Served to guests</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body">
                    <div class="text-muted small">KB documents (staff)</div>
                    <div class="h5 mb-0 text-warning"><?= (int)$kb['staff'] ?></div>
                    <div class="small text-muted">Employee/associate only</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body">
                    <div class="text-muted small">KB documents (admin)</div>
                    <div class="h5 mb-0 text-danger"><?= (int)$kb['admin'] ?></div>
                    <div class="small text-muted">Admin-only, never leaks</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Module grid -->
    <div class="row g-3">

    <!-- Predictive AI -->
    <div class="row g-3 mb-4">
        <div class="col-12"><h5 class="text-muted mb-2"><i class="fas fa-wand-magic-sparkles me-1"></i> Predictive AI</h5></div>

        <div class="col-md-4">
            <a href="<?= $base ?>/admin/ai-morning-brief" class="text-decoration-none">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body">
                        <i class="fas fa-fire-alt fa-2x text-danger mb-2"></i>
                        <h5 class="card-title">Lead Churn Prediction</h5>
                        <p class="card-text small text-muted"><?= (int)($churn_stats['high_critical'] ?? 0) ?> high/critical · <?= (int)($churn_stats['scored'] ?? 0) ?> leads scored. Which leads will go cold, who to call first.</p>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-4">
            <a href="<?= $base ?>/admin/ai-morning-brief" class="text-decoration-none">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body">
                        <i class="fas fa-chart-line fa-2x text-primary mb-2"></i>
                        <h5 class="card-title">Colony Demand Forecast</h5>
                        <p class="card-text small text-muted">30/60/90-day lead &amp; booking projections per colony; <?= (int)($low_stock_colonies ?? 0) ?> colony(ies) below 20% available stock.</p>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-4">
            <a href="<?= $base ?>/admin/ai-morning-brief" class="text-decoration-none">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body">
                        <i class="fas fa-user-check fa-2x text-success mb-2"></i>
                        <h5 class="card-title">Smart Lead Assign</h5>
                        <p class="card-text small text-muted"><?= (int)($unassigned_leads ?? 0) ?> unassigned open lead(s). Skill-weighted routing: who closes what, with a legacy fallback.</p>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <!-- AI Operations (Agents + AI Employees) -->
    <div class="row g-3 mb-4">
        <div class="col-12"><h5 class="text-muted mb-2"><i class="fas fa-robot me-1"></i> AI Operations</h5></div>

        <div class="col-md-4">
            <a href="<?= $base ?>/admin/agentic-ai" class="text-decoration-none">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body">
                        <i class="fas fa-gears fa-2x text-warning mb-2"></i>
                        <h5 class="card-title">Agentic AI</h5>
                        <p class="card-text small text-muted"><?= (int)($agent_counts['total'] ?? 0) ?> agents (<?= (int)($agent_counts['idle'] ?? 0) ?> idle, <?= (int)($agent_counts['active'] ?? 0) ?> active, <?= (int)($agent_counts['busy'] ?? 0) ?> busy) — reserved for explicit triggers only.</p>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-4">
            <a href="<?= $base ?>/admin/ai-employee" class="text-decoration-none">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body">
                        <i class="fas fa-user-tie fa-2x text-success mb-2"></i>
                        <h5 class="card-title">AI Employee Desk</h5>
                        <p class="card-text small text-muted"><?= (int)($ai_employee_counts['active'] ?? 0) ?> active · <?= (int)($ai_employee_counts['paused'] ?? 0) ?> paused — <?= (int)($ai_employee_counts['pending_tasks'] ?? 0) ?> task(s) pending. The staff that actually triages leads (Aira→Vivaan→Sahil).</p>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-4">
            <a href="<?= $base ?>/admin/crm/agentic" class="text-decoration-none">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body">
                        <i class="fas fa-paper-plane fa-2x text-info mb-2"></i>
                        <h5 class="card-title">Agentic CRM</h5>
                        <p class="card-text small text-muted">Auto follow-ups, score recalculation, auto-assignment, insights — assisted, not autonomous.</p>
                    </div>
                </div>
            </a>
        </div>
    </div>

        <div class="col-12"><h5 class="text-muted mb-2"><i class="fas fa-brain me-1"></i> Core AI</h5></div>

        <div class="col-md-4">
            <a href="<?= $base ?>/admin/ai/executive-assistant" class="text-decoration-none">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body">
                        <i class="fas fa-bolt fa-2x text-warning mb-2"></i>
                        <h5 class="card-title">Executive Assistant</h5>
                        <p class="card-text small text-muted">Board-level RBAC-aware assistant. Admin, CFO, CHRO, CTO, COO… opens KPIs + live data + role persona.</p>
                        <span class="badge bg-primary"><?= count($roles) ?> roles supported</span>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-4">
            <a href="<?= $base ?>/admin/ai-morning-brief" class="text-decoration-none">
                <div class="card h-100 border-0 shadow-sm" style="border-left: 4px solid #f59e0b;">
                    <div class="card-body">
                        <i class="fas fa-sun fa-2x text-warning mb-2"></i>
                        <h5 class="card-title">Morning Brief <span class="badge bg-warning text-dark">NEW</span></h5>
                        <p class="card-text small text-muted">Daily 3-minute sales brief: top leads to call, site visits today, "closed won – no booking yet" leads, hot/cold reviver.</p>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-4">
            <a href="<?= $base ?>/admin/rag" class="text-decoration-none">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body">
                        <i class="fas fa-search fa-2x text-info mb-2"></i>
                        <h5 class="card-title">RAG Knowledge Base</h5>
                        <p class="card-text small text-muted">Semantic search over your docs. Public/Staff/Admin visibility enforced. Re-embed in one click.</p>
                        <span class="badge bg-<?= $degraded ? 'danger' : 'success' ?>"><?= $degraded ? 'Degraded' : 'Healthy' ?></span>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-4">
            <a href="<?= $base ?>/admin/ai-settings" class="text-decoration-none">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body">
                        <i class="fas fa-cogs fa-2x text-secondary mb-2"></i>
                        <h5 class="card-title">AI Settings &amp; Keys</h5>
                        <p class="card-text small text-muted">Gemini, OpenRouter, Groq, HuggingFace keys. Free tier is enough. Test connections live.</p>
                        <span class="badge bg-dark">AES-256-CBC encrypted</span>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-4">
            <a href="<?= $base ?>/admin/ai-analytics" class="text-decoration-none">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body">
                        <i class="fas fa-chart-line fa-2x text-success mb-2"></i>
                        <h5 class="card-title">AI Analytics</h5>
                        <p class="card-text small text-muted">Usage, provider performance, conversation volume, failure rates.</p>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-4">
            <a href="<?= $base ?>/admin/ai-system" class="text-decoration-none">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body">
                        <i class="fas fa-server fa-2x text-purple mb-2"></i>
                        <h5 class="card-title">AI System Health</h5>
                        <p class="card-text small text-muted">Self-checking AI infrastructure, engine health, config verification.</p>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-4">
            <a href="<?= $base ?>/admin/ai-training" class="text-decoration-none">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body">
                        <i class="fas fa-graduation-cap fa-2x text-info mb-2"></i>
                        <h5 class="card-title">AI Training</h5>
                        <p class="card-text small text-muted">Self-learning training module, intent training, feedback learning.</p>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-12 mt-2"><h5 class="text-muted mb-2"><i class="fas fa-phone-volume me-1"></i> AI Voice &amp; Calls</h5></div>
        <div class="col-12"><p class="small text-muted mb-3">
            <i class="fas fa-circle-check text-success me-1"></i>Stored provider configs: <strong><?= (int)($voice_status['total'] ?? 0) ?> of 5</strong>
            (<code><?= htmlspecialchars(implode(', ', $voice_status['stored'] ?? []) ?: 'none') ?></code>) &middot;
            Active: <code><?= htmlspecialchars((string)($voice_status['active'] ?? '')) ?: 'not set' ?></code> &middot;
            <a href="<?= $base ?>/admin/service-configs" class="text-decoration-none">verify keys <i class="fas fa-arrow-right"></i></a>
            <br><span class="text-muted">"stored" means a config blob exists at rest &mdash; keys are not proven valid until a test call succeeds.</span>
        </p></div>

        <div class="col-md-4">
            <a href="<?= $base ?>/admin/ai-calling" class="text-decoration-none">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body">
                        <i class="fas fa-phone-alt fa-2x text-primary mb-2"></i>
                        <h5 class="card-title">AI Auto-Dialer</h5>
                        <p class="card-text small text-muted">AI-driven outbound calls, lead scoring, campaign manager.</p>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-4">
            <a href="<?= $base ?>/admin/voice-scheduler" class="text-decoration-none">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body">
                        <i class="fas fa-calendar-check fa-2x text-success mb-2"></i>
                        <h5 class="card-title">Voice Scheduler</h5>
                        <p class="card-text small text-muted">Scheduled outbound voice campaigns with auto-scheduling.</p>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-4">
            <a href="<?= $base ?>/admin/sim-calling" class="text-decoration-none">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body">
                        <i class="fas fa-headset fa-2x text-warning mb-2"></i>
                        <h5 class="card-title">SIM Calling Desk</h5>
                        <p class="card-text small text-muted">SIM-gateway calling, caller-ID, auto-dialer integration.</p>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-12 mt-2"><h5 class="text-muted mb-2"><i class="fas fa-wand-magic-sparkles me-1"></i> AI Content &amp; Documents</h5></div>

        <div class="col-md-4">
            <a href="<?= $base ?>/admin/legal/ai-composer" class="text-decoration-none">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body">
                        <i class="fas fa-file-signature fa-2x text-info mb-2"></i>
                        <h5 class="card-title">AI Document Generator</h5>
                        <p class="card-text small text-muted">AI-generated legal/booking documents from hints.</p>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-4">
            <a href="<?= $base ?>/admin/tools/document-extraction" class="text-decoration-none">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body">
                        <i class="fas fa-camera fa-2x text-purple mb-2"></i>
                        <h5 class="card-title">AI Document Extraction</h5>
                        <p class="card-text small text-muted">Aadhaar/PAN/RERA/booking form OCR + field extraction.</p>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-4">
            <a href="<?= $base ?>/admin/ai-management" class="text-decoration-none">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body">
                        <i class="fas fa-cogs fa-2x text-info mb-2"></i>
                        <h5 class="card-title">AI Management</h5>
                        <p class="card-text small text-muted">Integrations, lead scores, chatbot logs, context memory, usage — AI operating room.</p>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-4">
            <a href="<?= $base ?>/admin/ai-management/usage-analytics" class="text-decoration-none">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body">
                        <i class="fas fa-chart-pie fa-2x text-info mb-2"></i>
                        <h5 class="card-title">AI Usage &amp; Cost</h5>
                        <p class="card-text small text-muted">Provider usage, token spend and cost by module.</p>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="<?= $base ?>/admin/marketing-campaigns" class="text-decoration-none">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body">
                        <i class="fas fa-bullhorn fa-2x text-danger mb-2"></i>
                        <h5 class="card-title">Marketing + AI</h5>
                        <p class="card-text small text-muted">Campaigns, promo codes — AI-personalized outreach.</p>
                    </div>
                </div>
            </a>
        </div>

    </div>
</div>
