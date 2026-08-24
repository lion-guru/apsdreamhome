<?php
// AI Provider Settings — multi-provider configuration dashboard
$cfg = $config ?? [];
$masked = $masked ?? [];
$hasKeys = $has_keys ?? [];
$usage = $usage ?? ['today' => 0, 'month' => 0, 'errors_30d' => 0, 'by_engine' => []];
$csrf = $_SESSION['csrf_token'] ?? '';
$providerMeta = [
    'groq' => ['name' => 'Groq', 'icon' => 'fa-bolt', 'color' => '#f55036', 'desc' => 'Primary chat engine (compound-mini) + Whisper STT + orpheus TTS', 'free' => '~14.4K req/day'],
    'openrouter' => ['name' => 'OpenRouter', 'icon' => 'fa-route', 'color' => '#8b5cf6', 'desc' => 'Fallback #2 — auto-discovers current free models (Nemotron etc.)', 'free' => '~50 req/day'],
    'gemini' => ['name' => 'Google Gemini', 'icon' => 'fa-gem', 'color' => '#4285f4', 'desc' => 'Fallback #3 — gemini-2.5-flash (thinkingBudget=0 enforced)', 'free' => '1M tokens/day'],
    'ollama' => ['name' => 'Ollama (local)', 'icon' => 'fa-server', 'color' => '#10b981', 'desc' => 'Offline/private mode — chain skips it when no models pulled', 'free' => 'unlimited local'],
];
$chainOrder = ['groq', 'openrouter', 'gemini'];
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
        <div>
            <h1 class="h3 mb-1"><i class="fas fa-microchip me-2 text-primary"></i>AI Provider Settings</h1>
            <p class="text-muted mb-0">Configure API keys, models and voice engines for every AI surface. Keys are stored server-side; only masked previews are shown.</p>
        </div>
        <div class="text-end">
            <span class="badge bg-success-subtle text-success fs-6 px-3 py-2"><i class="fas fa-cloud me-1"></i>Cloud-first chain active</span>
        </div>
    </div>

    <div class="row g-3 mb-4" id="statusCards">
        <?php foreach ($providerMeta as $key => $meta): ?>
            <?php if ($key !== 'ollama'): ?>
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                             style="width:46px;height:46px;background:<?= $meta['color'] ?>18;color:<?= $meta['color'] ?>">
                            <i class="fas <?= $meta['icon'] ?> fa-lg"></i>
                        </div>
                        <div class="flex-grow-1 min-w-0">
                            <div class="fw-semibold"><?= $meta['name'] ?> <span class="badge bg-secondary-subtle text-secondary ms-1"><?= $meta['free'] ?></span></div>
                            <div class="small text-muted text-truncate" id="masked-<?= $key ?>"><?= $hasKeys[$key] ? ($masked[$key] ?: 'key saved') : 'no key saved' ?></div>
                        </div>
                        <i class="fas fa-circle-question text-muted provider-status" data-provider="<?= $key ?>" title="Not tested yet"></i>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        <?php endforeach; ?>
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                         style="width:46px;height:46px;background:#10b98118;color:#10b981">
                        <i class="fas fa-server fa-lg"></i>
                    </div>
                    <div class="flex-grow-1 min-w-0">
                        <div class="fw-semibold">Ollama (local)</div>
                        <div class="small text-muted text-truncate"><?= htmlspecialchars((string)($cfg['ollama_url'] ?? '')) ?></div>
                    </div>
                    <i class="fas fa-circle-question text-muted provider-status" data-provider="ollama" title="Not tested yet"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0"><i class="fas fa-key me-2 text-warning"></i>API Keys &amp; Models</h5>
                </div>
                <div class="card-body">
                    <form id="configForm">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">

                        <?php foreach ($chainOrder as $i => $key): $meta = $providerMeta[$key]; ?>
                            <div class="mb-3 p-3 rounded-3" style="background:<?= $meta['color'] ?>0a;border-left:3px solid <?= $meta['color'] ?>">
                                <label class="form-label fw-semibold mb-1">
                                    <span class="badge bg-dark me-1">#<?= $i + 1 ?> in chain</span>
                                    <?= $meta['name'] ?> API Key
                                </label>
                                <div class="small text-muted mb-2"><?= $meta['desc'] ?></div>
                                <div class="input-group">
                                    <input type="password" class="form-control" name="<?= $key ?>_key" placeholder="<?= $hasKeys[$key] ? 'Saved: ' . htmlspecialchars($masked[$key]) . ' — leave blank to keep' : 'not configured — paste key' ?>" autocomplete="new-password">
                                    <button type="button" class="btn btn-outline-secondary toggle-key" tabindex="-1"><i class="fas fa-eye"></i></button>
                                    <button type="button" class="btn btn-outline-primary test-btn" data-provider="<?= $key ?>"><i class="fas fa-plug me-1"></i>Test</button>
                                </div>
                                <div class="small mt-1 test-result" data-provider="<?= $key ?>"></div>
                            </div>
                        <?php endforeach; ?>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Gemini Model</label>
                                <select class="form-select" name="gemini_model">
                                    <?php foreach (['gemini-2.5-flash', 'gemini-2.5-flash-lite', 'gemini-2.0-flash'] as $m): ?>
                                        <option value="<?= $m ?>" <?= ($cfg['gemini_model'] ?? '') === $m ? 'selected' : '' ?>><?= $m ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="form-text">Deprecated 1.x/2.0 refs were migrated to 2.5-flash project-wide.</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Ollama Endpoint / Model</label>
                                <div class="input-group input-group-sm mb-1">
                                    <input type="url" class="form-control" name="ollama_url" value="<?= htmlspecialchars((string)($cfg['ollama_url'] ?? '')) ?>" placeholder="http://localhost:11434">
                                    <button type="button" class="btn btn-outline-primary test-btn" data-provider="ollama"><i class="fas fa-plug"></i></button>
                                </div>
                                <input type="text" class="form-control form-control-sm" name="ollama_model" value="<?= htmlspecialchars((string)($cfg['ollama_model'] ?? '')) ?>" placeholder="llama3.2:3b">
                                <div class="small mt-1 test-result" data-provider="ollama"></div>
                            </div>
                        </div>

                        <hr class="my-4">
                        <h6 class="fw-semibold mb-3"><i class="fas fa-phone-volume me-2 text-info"></i>Voice Pipeline Engines (AI calling)</h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Text-to-Speech (TTS)</label>
                                <select class="form-select" name="tts_engine">
                                    <option value="groq" <?= ($cfg['tts_engine'] ?? '') === 'groq' ? 'selected' : '' ?>>Groq orpheus (natural English)</option>
                                    <option value="google" <?= ($cfg['tts_engine'] ?? '') === 'google' ? 'selected' : '' ?>>Google TTS (Hindi + English)</option>
                                    <option value="espeak" <?= ($cfg['tts_engine'] ?? '') === 'espeak' ? 'selected' : '' ?>>eSpeak (offline fallback)</option>
                                    <option value="ollama" <?= ($cfg['tts_engine'] ?? '') === 'ollama' ? 'selected' : '' ?>>Ollama/local</option>
                                </select>
                                <div class="form-text">Hindi replies always use Google TTS regardless of this setting.</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Speech-to-Text (STT) preference</label>
                                <select class="form-select" name="stt_engine">
                                    <option value="groq" <?= ($cfg['stt_engine'] ?? '') === 'groq' ? 'selected' : '' ?>>Groq Whisper large-v3 (cloud-first)</option>
                                    <option value="whisper" <?= ($cfg['stt_engine'] ?? '') === 'whisper' ? 'selected' : '' ?>>Local Whisper docker first</option>
                                </select>
                                <div class="form-text">Transcription already falls back to the other engine on failure.</div>
                            </div>
                        </div>

                        <div class="d-flex gap-2 mt-4">
                            <button type="submit" class="btn btn-primary px-4"><i class="fas fa-save me-1"></i>Save Configuration</button>
                            <button type="button" class="btn btn-outline-secondary" id="testAllBtn"><i class="fas fa-vial me-1"></i>Test All Providers</button>
                        </div>
                    </form>
                    <div id="saveResult" class="mt-3"></div>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0"><i class="fas fa-chart-line me-2 text-success"></i>Usage — last 30 days</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center mb-3">
                        <div class="col-4">
                            <div class="fs-3 fw-bold text-primary"><?= number_format($usage['today']) ?></div>
                            <div class="small text-muted">calls today</div>
                        </div>
                        <div class="col-4">
                            <div class="fs-3 fw-bold text-info"><?= number_format($usage['month']) ?></div>
                            <div class="small text-muted">last 30 days</div>
                        </div>
                        <div class="col-4">
                            <div class="fs-3 fw-bold <?= $usage['errors_30d'] > 0 ? 'text-danger' : 'text-success' ?>"><?= number_format($usage['errors_30d']) ?></div>
                            <div class="small text-muted">errors</div>
                        </div>
                    </div>
                    <?php if (empty($usage['by_engine'])): ?>
                        <p class="text-muted small mb-0">No AI calls logged yet in this window.</p>
                    <?php else: ?>
                        <table class="table table-sm align-middle mb-0">
                            <thead><tr><th>Engine</th><th class="text-end">Calls</th><th class="text-end">Avg ms</th><th class="text-end">Errors</th></tr></thead>
                            <tbody>
                            <?php foreach ($usage['by_engine'] as $u): ?>
                                <tr>
                                    <td><span class="badge bg-light text-dark border"><?= htmlspecialchars((string)$u['engine']) ?></span></td>
                                    <td class="text-end"><?= number_format((int)$u['calls']) ?></td>
                                    <td class="text-end"><?= (int)$u['avg_ms'] ?></td>
                                    <td class="text-end <?= (int)$u['errors'] > 0 ? 'text-danger fw-semibold' : 'text-muted' ?>"><?= (int)$u['errors'] ?></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0"><i class="fas fa-circle-info me-2 text-secondary"></i>Fallback Chain</h5>
                </div>
                <div class="card-body small">
                    <ol class="ps-3 mb-2">
                        <li><strong>Groq</strong> — compound-mini chat · whisper-large-v3 STT · orpheus TTS</li>
                        <li><strong>OpenRouter</strong> — live-discovered free NVIDIA Nemotron models (rotates; cached 6h)</li>
                        <li><strong>Gemini</strong> — 2.5-flash with thinkingBudget=0</li>
                        <li><strong>Ollama</strong> — skipped while no models are pulled</li>
                    </ol>
                    <p class="text-muted mb-0">Every AI surface routes through this chain: assistant chats, widget bot, voice pipeline, WhatsApp webhook, executive AI, lead parsing. Changing keys here takes effect immediately — no deploy needed. If you switch to a different Google/Groq account later, just paste the new keys above.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.min-w-0 { min-width: 0; }
.provider-status.fa-check { color: #22c55e !important; }
.provider-status.fa-xmark { color: #ef4444 !important; }
.provider-status.fa-spinner { color: #f59e0b !important; }
.test-result.ok { color: #16a34a; }
.test-result.fail { color: #dc2626; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const csrf = '<?= htmlspecialchars($csrf) ?>';

    document.querySelectorAll('.toggle-key').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const input = btn.closest('.input-group').querySelector('input');
            const show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            btn.innerHTML = show ? '<i class="fas fa-eye-slash"></i>' : '<i class="fas fa-eye"></i>';
        });
    });

    function runTest(provider) {
        const icon = document.querySelector('.provider-status[data-provider="' + provider + '"]');
        const out = document.querySelector('.test-result[data-provider="' + provider + '"]');
        if (icon) { icon.className = 'fas fa-spinner fa-spin provider-status'; }
        if (out) { out.className = 'small mt-1 test-result'; out.textContent = 'Testing…'; }
        fetch('<?= BASE_URL ?>/admin/ai-settings/test-provider', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ provider: provider, csrf_token: csrf })
        }).then(r => r.json()).then(function (r) {
            if (icon) { icon.className = 'fas ' + (r.success ? 'fa-check' : 'fa-xmark') + ' provider-status'; }
            if (out) {
                out.className = 'small mt-1 test-result ' + (r.success ? 'ok' : 'fail');
                out.textContent = (r.success ? '✓ ' : '✗ ') + (r.message || '') + ' (' + (r.latency_ms || 0) + 'ms)';
            }
        }).catch(function () {
            if (icon) { icon.className = 'fas fa-xmark provider-status'; }
            if (out) { out.className = 'small mt-1 test-result fail'; out.textContent = '✗ Request failed'; }
        });
    }

    document.querySelectorAll('.test-btn').forEach(function (btn) {
        btn.addEventListener('click', function () { runTest(btn.dataset.provider); });
    });

    const allBtn = document.getElementById('testAllBtn');
    if (allBtn) {
        allBtn.addEventListener('click', function () {
            ['groq', 'openrouter', 'gemini', 'ollama'].forEach(runTest);
        });
    }

    document.getElementById('configForm').addEventListener('submit', function (e) {
        e.preventDefault();
        const box = document.getElementById('saveResult');
        box.innerHTML = '<span class="text-muted"><i class="fas fa-spinner fa-spin me-1"></i>Saving…</span>';
        fetch('<?= BASE_URL ?>/admin/ai-settings/save-config', {
            method: 'POST',
            body: new URLSearchParams(new FormData(this))
        }).then(r => r.json()).then(function (r) {
            box.innerHTML = r.success
                ? '<div class="alert alert-success py-2 mb-0"><i class="fas fa-check-circle me-1"></i>' + (r.message || 'Saved') + '</div>'
                : '<div class="alert alert-danger py-2 mb-0"><i class="fas fa-exclamation-circle me-1"></i>' + (r.message || 'Save failed') + '</div>';
            if (r.success) { setTimeout(() => window.location.reload(), 900); }
        }).catch(function () {
            box.innerHTML = '<div class="alert alert-danger py-2 mb-0">Request failed</div>';
        });
    });

    // Auto-test all providers on page load
    ['groq', 'openrouter', 'gemini', 'ollama'].forEach(runTest);
});
</script>
