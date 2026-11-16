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
            <a href="<?= $base ?>/admin/legal/ai-generate" class="text-decoration-none">
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
