<?php
$page_title = $page_title ?? 'Free AI Engines - APS Dream Home';
$engine_status = $engine_status ?? [];
?>
<style>
.free-engine-card{background:#fff;border-radius:16px;border:1px solid #f0f0f5;padding:20px;transition:.3s;margin-bottom:16px}
.free-engine-card:hover{box-shadow:0 8px 24px rgba(0,0,0,.06)}
.engine-status-dot{width:12px;height:12px;border-radius:50%;display:inline-block;margin-right:8px}
.engine-status-dot.active{background:#10b981;box-shadow:0 0 8px rgba(16,185,129,.5)}
.engine-status-dot.inactive{background:#ef4444}
.engine-status-dot.unknown{background:#f59e0b}
.test-btn{border-radius:8px;padding:6px 14px;font-size:12px;font-weight:600}
.test-result{font-size:12px;margin-top:6px;min-height:18px}
.provider-badge{padding:3px 10px;border-radius:20px;font-size:11px;font-weight:700}
.provider-badge.free{background:#dcfce7;color:#166534}
.provider-badge.cloud{background:#dbeafe;color:#1e40af}
.provider-badge.local{background:#fef3c7;color:#92400e}
</style>

<div class="container-fluid px-4 py-3">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h2 class="mb-1 fw-bold"><i class="fas fa-brain me-2 text-primary"></i>Free AI Engines</h2>
            <p class="text-muted mb-0">Zero-cost AI providers — all free tier. Chain: Groq → xAI → Hugging Face → Ollama → OpenRouter → Gemini</p>
        </div>
        <a href="<?= BASE_URL ?>/admin/ai/settings" class="btn btn-outline-primary"><i class="fas fa-cog me-1"></i>Configure Keys</a>
    </div>

    <!-- Status Summary -->
    <div class="row g-3 mb-4">
        <?php
        $available = 0; $total = 0;
        foreach ($engine_status as $name => $info) {
            $total++;
            if (!empty($info['available'])) $available++;
        }
        ?>
        <div class="col-md-3">
            <div class="free-engine-card text-center">
                <div class="display-6 fw-bold text-primary"><?= $available ?>/<?= $total ?></div>
                <div class="text-muted small">Engines Available</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="free-engine-card text-center">
                <div class="display-6 fw-bold text-success">₹0</div>
                <div class="text-muted small">Cost (All Free)</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="free-engine-card text-center">
                <div class="display-6 fw-bold text-info">6</div>
                <div class="text-muted small">Providers in Chain</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="free-engine-card text-center">
                <div class="display-6 fw-bold text-warning">0</div>
                <div class="text-muted small">Ollama Models Pulled</div>
            </div>
        </div>
    </div>

    <!-- Engine Cards -->
    <div class="row g-3">
        <?php foreach ($engine_status as $name => $info): ?>
        <div class="col-md-6 col-lg-4">
            <div class="free-engine-card">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div class="d-flex align-items-center">
                        <span class="engine-status-dot <?= !empty($info['available']) ? 'active' : 'inactive' ?>"></span>
                        <span class="fw-bold"><?= ucfirst(str_replace('_', ' ', $name)) ?></span>
                    </div>
                    <span class="provider-badge <?= $name === 'ollama' ? 'local' : 'cloud' ?>"><?= $name === 'ollama' ? 'LOCAL' : 'CLOUD' ?></span>
                </div>
                <div class="small text-muted mb-1">Model: <code><?= htmlspecialchars($info['model'] ?? 'N/A') ?></code></div>
                <div class="small text-muted mb-1">Cost: <?= htmlspecialchars($info['cost'] ?? 'Free') ?></div>
                <div class="small text-muted mb-3">Speed: <?= htmlspecialchars($info['speed'] ?? 'N/A') ?></div>
                <button type="button" class="btn btn-outline-primary test-btn w-100" data-provider="<?= $name ?>">
                    <i class="fas fa-vial me-1"></i>Test <?= ucfirst(str_replace('_', ' ', $name)) ?>
                </button>
                <div class="small mt-2 test-result" data-provider="<?= $name ?>"></div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Chain Order -->
    <div class="card mt-4">
        <div class="card-body p-4">
            <h5 class="fw-bold mb-3"><i class="fas fa-link me-2 text-primary"></i>Fallback Chain Order</h5>
            <div class="d-flex flex-wrap gap-2 align-items-center">
                <?php
                $chain = ['Groq', 'xAI Grok', 'Hugging Face', 'Ollama', 'OpenRouter', 'Gemini'];
                foreach ($chain as $i => $eng): ?>
                    <span class="badge bg-<?= $i === 0 ? 'primary' : 'secondary' ?> p-2"><?= $i + 1 ?>. <?= $eng ?></span>
                    <?php if ($i < count($chain) - 1): ?><i class="fas fa-arrow-right text-muted"></i><?php endif; ?>
                <?php endforeach; ?>
            </div>
            <div class="small text-muted mt-2">First successful response wins. Chain tries each in order; skips unavailable engines.</div>
        </div>
    </div>

    <!-- Quick Test -->
    <div class="card mt-4">
        <div class="card-body p-4">
            <h5 class="fw-bold mb-3"><i class="fas fa-bolt me-2 text-warning"></i>Quick Generate Test</h5>
            <div class="input-group">
                <input type="text" id="testPrompt" class="form-control" placeholder="Type a prompt to test all free engines...">
                <button class="btn btn-primary" onclick="testGenerate()"><i class="fas fa-play me-1"></i>Generate</button>
            </div>
            <div id="generateResult" class="mt-3"></div>
        </div>
    </div>
</div>

<script nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
function testGenerate() {
    var prompt = document.getElementById('testPrompt').value.trim();
    if (!prompt) { alert('Enter a prompt'); return; }
    var res = document.getElementById('generateResult');
    res.innerHTML = '<div class="text-muted"><i class="fas fa-spinner fa-spin me-1"></i>Testing all free engines...</div>';
    fetch('<?= BASE_URL ?>/admin/ai/engines/test', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded', 'X-CSRF-Token': '<?= $_SESSION['csrf_token'] ?? '' ?>'},
        body: 'provider=test&prompt=' + encodeURIComponent(prompt)
    }).then(r => r.json()).then(d => {
        if (d.success) {
            res.innerHTML = '<div class="alert alert-success"><strong>Engine:</strong> ' + d.message + '<br><strong>Response:</strong> ' + (d.text || '').substring(0, 300) + '</div>';
        } else {
            res.innerHTML = '<div class="alert alert-danger">' + (d.message || 'All engines failed') + '</div>';
        }
    }).catch(e => { res.innerHTML = '<div class="alert alert-danger">Network error: ' + e.message + '</div>'; });
}
</script>
