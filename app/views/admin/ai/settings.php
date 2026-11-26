<?php
$page_title = $page_title ?? 'AI API Settings';
$settings = $settings ?? [];
$engine_status = $engine_status ?? [];
?>

<style>
.settings-header{background:linear-gradient(135deg,#1e1b4b 0%,#312e81 100%);color:#fff;border-radius:0 0 24px 24px;padding:32px 0;margin-bottom:24px}
.engine-card{background:#fff;border-radius:16px;border:1px solid #f0f0f5;padding:20px;transition:.3s}
.engine-card:hover{box-shadow:0 8px 24px rgba(0,0,0,.06)}
.engine-card .engine-status{width:12px;height:12px;border-radius:50%;display:inline-block;margin-right:8px}
.engine-card .engine-status.active{background:#10b981;box-shadow:0 0 8px rgba(16,185,129,.5)}
.engine-card .engine-status.inactive{background:#ef4444}
.api-input{border:2px solid #e5e7eb;border-radius:10px;padding:12px 16px;font-size:14px;width:100%;transition:.2s;font-family:monospace}
.api-input:focus{border-color:#6366f1;outline:none;box-shadow:0 0 0 3px rgba(99,102,241,.1)}
.api-label{font-size:13px;font-weight:700;color:#333;margin-bottom:6px;display:block}
.api-hint{font-size:11px;color:#888;margin-top:4px}
.save-btn{background:linear-gradient(135deg,#6366f1 0%,#8b5cf6 100%);border:none;border-radius:12px;padding:12px 32px;font-weight:700;color:#fff;transition:.3s}
.save-btn:hover{transform:translateY(-2px);box-shadow:0 8px 24px rgba(99,102,241,.3);color:#fff}
.free-badge{background:#dcfce7;color:#166534;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:700}
</style>

<div class="settings-header">
    <div class="container-fluid px-4">
        <h2 class="mb-1 fw-bold"><i class="fas fa-key me-2"></i>AI API Settings</h2>
        <p class="mb-0 opacity-75">Configure free AI engines. Cost: ₹0 — all free tier.</p>
    </div>
</div>

<div class="container-fluid px-4">
    <!-- Engine Status -->
    <div class="row g-3 mb-4">
        <?php foreach ($engine_status as $name => $info): ?>
            <div class="col-md-3">
                <div class="engine-card">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span ><?= $name ?></span>
                        <span class="engine-status <?= $info['available'] ? 'active' : 'inactive' ?>"></span>
                    </div>
                    <div ><?= $info['model'] ?? 'N/A' ?></div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted"><?= $info['cost'] ?? '' ?></span>
                        <span class="text-muted"><?= $info['speed'] ?? '' ?></span>
                    </div>
                    <?php if ($name === 'groq' || $name === 'openrouter' || $name === 'gemini'): ?>
                        <div class="mt-2"><span class="free-badge">FREE TIER</span></div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- API Key Form -->
    <div class="card">
        <div class="card-body p-4">
            <form method="POST">
    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                <div class="row g-4">
                    <!-- Groq -->
                    <div class="col-md-6">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="engine-status <?= !empty($engine_status['groq']['available']) ? 'active' : 'inactive' ?>"></span>
                            <label class="api-label mb-0">Groq API Key <span class="free-badge">FREE</span></label>
                        </div>
                        <input aria-label="Groq api key" type="password" name="groq_api_key" class="api-input" value="<?= htmlspecialchars($settings['groq_api_key'] ?? '') ?>" placeholder="gsk_...">
                        <div class="api-hint">Free: 30 RPM. <a href="https://console.groq.com/keys" target="_blank">Get key →</a> Fastest inference (~500 tok/s)</div>
                        <button type="button" class="btn btn-sm btn-outline-primary test-provider-btn" data-provider="groq" data-endpoint="/admin/ai_settings/test_provider"><i class="fas fa-bolt me-1"></i>Test Connection</button>
                    </div>

                    <!-- OpenRouter -->
                    <div class="col-md-6">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="engine-status <?= !empty($engine_status['openrouter']['available']) ? 'active' : 'inactive' ?>"></span>
                            <label class="api-label mb-0">OpenRouter API Key <span class="free-badge">FREE</span></label>
                        </div>
                        <input aria-label="Openrouter api key" type="password" name="openrouter_api_key" class="api-input" value="<?= htmlspecialchars($settings['openrouter_api_key'] ?? '') ?>" placeholder="sk-or-...">
                        <div class="api-hint">Free models: Llama 3, Mistral, Gemma. <a href="https://openrouter.ai/keys" target="_blank">Get key →</a></div>
                        <button type="button" class="btn btn-sm btn-outline-primary test-provider-btn" data-provider="openrouter" data-endpoint="/admin/ai_settings/test_provider"><i class="fas fa-bolt me-1"></i>Test</button>
                    </div>

                    <!-- Gemini -->
                    <div class="col-md-6">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="engine-status <?= !empty($engine_status['gemini']['available']) ? 'active' : 'inactive' ?>"></span>
                            <label class="api-label mb-0">Google Gemini API Key <span class="free-badge">FREE</span></label>
                        </div>
                        <input aria-label="Gemini api key" type="password" name="gemini_api_key" class="api-input" value="<?= htmlspecialchars($settings['api_key'] ?? '') ?>" placeholder="AIza...">
                        <div class="api-hint">Free: 15 RPM, 1M tokens/day. <a href="https://aistudio.google.com/apikey" target="_blank">Get key →</a></div>
                        <button type="button" class="btn btn-sm btn-outline-primary test-provider-btn" data-provider="gemini" data-endpoint="/admin/ai_settings/test_provider"><i class="fas fa-bolt me-1"></i>Test</button>
                    </div>

                    <!-- Hugging Face (RAG Embeddings) -->
                    <div class="col-md-6">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="engine-status <?= !empty($engine_status['hf']['available'] ?? true) ? 'active' : 'inactive' ?>"></span>
                            <label class="api-label mb-0">Hugging Face API Key (RAG Embeddings) <span class="free-badge">FREE</span></label>
                        </div>
                        <input aria-label="HF api key" type="password" name="hf_api_key" class="api-input" value="<?= htmlspecialchars($config['hf_api_key'] ?? '') ?>" placeholder="hf_...">
                        <div class="api-hint">Real 384-dim embeddings for RAG (sentence-transformers/all-MiniLM-L6-v2). <a href="https://huggingface.co/settings/tokens" target="_blank">Get key →</a></div>
                        <button type="button" class="btn btn-sm btn-outline-primary test-provider-btn" data-provider="hf" data-endpoint="/admin/ai_settings/test_provider"><i class="fas fa-bolt me-1"></i>Test</button>
                    </div>

                    <!-- Ollama -->
                    <div class="col-md-6">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="engine-status <?= !empty($engine_status['ollama']['available']) ? 'active' : 'inactive' ?>"></span>
                            <label class="api-label mb-0">Ollama (Local) <span class="free-badge">UNLIMITED</span></label>
                        </div>
                        <input aria-label="Ollama url" type="text" name="ollama_url" class="api-input" value="<?= htmlspecialchars($settings['ollama_url'] ?? 'http://localhost:11434') ?>" placeholder="http://localhost:11434">
                        <div class="api-hint">Unlimited, free, private. <a href="https://ollama.com" target="_blank">Install →</a> Run: <code>ollama pull llama3.1:8b</code></div>
                        <button type="button" class="btn btn-sm btn-outline-primary test-provider-btn" data-provider="ollama" data-endpoint="/admin/ai_settings/test_provider"><i class="fas fa-bolt me-1"></i>Test Connection</button>
                    </div>
                    <div class="mt-2">
                        <label class="api-label mb-0">Ollama Model</label>
                        <input aria-label="Ollama model" type="text" name="ollama_model" class="api-input mt-2" value="<?= htmlspecialchars($settings['ollama_model'] ?? 'llama3.1:8b') ?>" placeholder="llama3.1:8b">
                    </div>
                </div>

                <div class="mt-4 d-flex gap-3">
                    <button type="submit" class="save-btn"><i class="fas fa-save me-2"></i>Save Settings</button>
                    <button type="button" class="btn btn-primary" onclick="testAllProviders()"><i class="fas fa-bolt me-1"></i>Test All Providers</button>
                    <a href="<?= BASE_URL ?>/admin/ai-system" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>

            <!-- Quick Test All Banner -->
            <div class="alert alert-info mt-3 p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <i class="fas fa-robot me-2"></i>
                        <strong>Quick Test All</strong><br>
                        <small class="text-muted">Test all AI providers at once</small>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-primary test-provider-btn" data-provider="ollama" data-endpoint="/admin/ai_settings/test_provider"><i class="fas fa-plug me-1"></i>Ollama</button>
                    <button type="button" class="btn btn-sm btn-outline-primary test-provider-btn" data-provider="groq" data-endpoint="/admin/ai_settings/test_provider"><i class="fas fa-bolt me-1"></i>Groq</button>
                    <button type="button" class="btn btn-sm btn-outline-primary test-provider-btn" data-provider="openrouter" data-endpoint="/admin/ai_settings/test_provider"><i class="fas fa-rocket me-1"></i>OpenRouter</button>
                    <button type="button" class="btn btn-sm btn-outline-primary test-provider-btn" data-provider="gemini" data-endpoint="/admin/ai_settings/test_provider"><i class="fas fa-brain me-1"></i>Gemini</button>
                    <button type="button" class="btn btn-sm btn-outline-primary test-provider-btn" data-provider="hf" data-endpoint="/admin/ai_settings/test_provider"><i class="fas fa-hugging-face me-1"></i>HuggingFace</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Info -->
    <div class="card mt-4">
        <div class="card-body p-4">
            <h5 ><i class="fas fa-info-circle me-2"></i>How It Works</h5>
            <div class="row mt-3">
                <div class="col-md-4">
                    <h6 >Smart Routing</h6>
                    <p >AI Gateway tries engines in order: Ollama →' Groq →' OpenRouter →' Gemini. First successful response wins.</p>
                </div>
                <div class="col-md-4">
                    <h6 >All Free Forever</h6>
                    <p >Ollama: unlimited. Groq: 30 RPM. OpenRouter: free models. Gemini: 15 RPM. Total cost: ₹0.</p>
                </div>
                <div class="col-md-4">
                    <h6 >Zero Config Works</h6>
                    <p >Without any API keys, the system uses rule engine + SelfLearningAI + IntentDetector (all local). API keys make it smarter.</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.querySelectorAll('.test-provider-btn').forEach(btn => {
            btn.addEventListener('click', async function() {
                const provider = this.dataset.provider;
                const endpoint = this.dataset.endpoint;
                const btn = this;
                const originalText = btn.innerHTML;
                
                btn.innerHTML = '<i class="fas fa-circle-notch fa-spin me-1"></i>Testing...';
                btn.disabled = true;

                try {
                    const formData = new FormData();
                    formData.append('provider', provider);
                    formData.append('csrf_token', '<?= $_SESSION['csrf_token'] ?? '' ?>');

                    const response = await fetch(endpoint, {
                        method: 'POST',
                        body: formData,
                        credentials: 'same-origin'
                    });
                    const result = await response.json();

                    if (result.success) {
                        btn.innerHTML = '<i class="fas fa-check me-1"></i>Connected';
                        btn.classList.remove('btn-outline-primary');
                        btn.classList.add('btn-success');
                        setTimeout(() => {
                            btn.innerHTML = originalText;
                            btn.classList.remove('btn-success');
                            btn.classList.add('btn-outline-primary');
                            btn.disabled = false;
                        }, 3000);
                    } else {
                        btn.innerHTML = '<i class="fas fa-times me-1"></i>Failed';
                        btn.classList.remove('btn-outline-primary');
                        btn.classList.add('btn-danger');
                        setTimeout(() => {
                            btn.innerHTML = originalText;
                            btn.classList.remove('btn-danger');
                            btn.classList.add('btn-outline-primary');
                            btn.disabled = false;
                        }, 3000);
                    }
                } catch (err) {
                    btn.innerHTML = '<i class="fas fa-times me-1"></i>Error';
                    btn.classList.remove('btn-outline-primary');
                    btn.classList.add('btn-danger');
                    setTimeout(() => {
                        btn.innerHTML = originalText;
                        btn.classList.remove('btn-danger');
                        btn.classList.add('btn-outline-primary');
                        btn.disabled = false;
                    }, 3000);
                }
            });
        });
    }

    async function testAllProviders() {
        const buttons = document.querySelectorAll('.test-provider-btn');
        const results = [];

        for (let btn of buttons) {
            const provider = btn.dataset.provider;
            const endpoint = btn.dataset.endpoint;

            // Skip if already processing
            if (btn.disabled) continue;

            btn.innerHTML = '<i class="fas fa-circle-notch fa-spin me-1"></i>Testing...';
            btn.disabled = true;
            btn.classList.add('btn-warning');
            btn.classList.remove('btn-outline-primary');

            try {
                const formData = new FormData();
                formData.append('provider', provider);
                formData.append('csrf_token', '<?= $_SESSION['csrf_token'] ?? '' ?>');

                const response = await fetch(endpoint, {
                    method: 'POST',
                    body: formData,
                    credentials: 'same-origin'
                });
                const result = await response.json();

                if (result.success) {
                    btn.innerHTML = '<i class="fas fa-check me-1"></i>OK';
                    btn.classList.remove('btn-warning', 'btn-outline-primary');
                    btn.classList.add('btn-success');
                } else {
                    btn.innerHTML = '<i class="fas fa-times me-1"></i>Fail';
                    btn.classList.remove('btn-warning', 'btn-outline-primary');
                    btn.classList.add('btn-danger');
                }
            } catch (err) {
                btn.innerHTML = '<i class="fas fa-times me-1"></i>Err';
                btn.classList.remove('btn-warning', 'btn-outline-primary');
                btn.classList.add('btn-danger');
            }
        }

        setTimeout(() => {
            buttons.forEach(btn => {
                const provider = btn.dataset.provider;
                if (btn.classList.contains('btn-success')) {
                    btn.innerHTML = `<i class="fas fa-bolt me-1"></i>${provider.charAt(0).toUpperCase() + provider.slice(1)}`;
                    btn.classList.remove('btn-success');
                    btn.classList.add('btn-outline-primary');
                    btn.disabled = false;
                } else if (btn.classList.contains('btn-danger')) {
                    btn.innerHTML = `<i class="fas fa-bolt me-1"></i>${provider.charAt(0).toUpperCase() + provider.slice(1)}`;
                    btn.classList.remove('btn-danger');
                    btn.classList.add('btn-outline-primary');
                    btn.disabled = false;
                }
            });
        }, 2000);
    }
</script>
</div>
