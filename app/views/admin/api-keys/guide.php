<?php
$extraHead = '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">';
$extraHead .= '<style>
.guide-card { border: none; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.08); margin-bottom: 20px; }
.step-card { border-left: 4px solid #0d6efd; background: #f8f9fa; padding: 15px 20px; margin-bottom: 10px; border-radius: 8px; }
.api-status { display: inline-flex; align-items: center; padding: 4px 12px; border-radius: 20px; font-size: 12px; }
.api-active { background: #d4edda; color: #155724; }
.api-inactive { background: #f8d7da; color: #721c24; }
</style>';

$providers = [
    [
        'name' => 'OpenRouter',
        'icon' => 'fa-route',
        'color' => '#5c4dff',
        'models' => ['Qwen Coder (Free)', 'DeepSeek Chat (Free)', 'Claude 3 Haiku (Free)', 'Mistral Nemo (Free)'],
        'url' => 'https://openrouter.ai/keys',
        'free' => true,
        'desc' => 'Best for FREE AI access. Multiple free models available. Just sign up and get API key instantly.'
    ],
    [
        'name' => 'Groq',
        'icon' => 'fa-bolt',
        'color' => '#ff6b35',
        'models' => ['Llama 3.1 8B (Free)', 'Mixtral 8x7B (Free)', 'Gemma 2 9B (Free)'],
        'url' => 'https://console.groq.com/keys',
        'free' => true,
        'desc' => 'Extremely fast inference. Free tier with high rate limits. Great for production use.'
    ],
    [
        'name' => 'OpenAI',
        'icon' => 'fa-brain',
        'color' => '#10a37f',
        'models' => ['GPT-4o', 'GPT-4o-mini', 'GPT-3.5-turbo'],
        'url' => 'https://platform.openai.com/api-keys',
        'free' => false,
        'desc' => 'Premium AI models. Requires payment but offers best quality. $5 free credits for new users.'
    ],
    [
        'name' => 'Google Gemini',
        'icon' => 'fa-gem',
        'color' => '#4285f4',
        'models' => ['Gemini 2.0 Flash', 'Gemini 1.5 Pro', 'Gemini 1.5 Flash'],
        'url' => 'https://makersuite.google.com/app/apikey',
        'free' => false,
        'desc' => 'Google AI with large context windows. Free tier available with limits.'
    ],
    [
        'name' => 'Anthropic Claude',
        'icon' => 'fa-user-astronaut',
        'color' => '#d4a574',
        'models' => ['Claude 3.5 Sonnet', 'Claude 3 Opus', 'Claude 3 Haiku'],
        'url' => 'https://console.anthropic.com/settings/keys',
        'free' => false,
        'desc' => 'Best for complex reasoning and long documents. $5 free credits for new users.'
    ],
    [
        'name' => 'Hugging Face',
        'icon' => 'fa-robot',
        'color' => '#ffd70f',
        'models' => ['Mistral 7B (Free)', 'Llama 3 8B (Free)', 'Various open models'],
        'url' => 'https://huggingface.co/settings/tokens',
        'free' => true,
        'desc' => 'Open source models. Free inference endpoints available. Great for experimentation.'
    ]
];

$currentKeys = $this->db->query("SELECT * FROM api_keys WHERE is_active = 1")->fetchAll(\PDO::FETCH_ASSOC);
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-robot me-2"></i>AI API Keys Guide</h2>
        <a href="<?= BASE_URL ?>/admin/api-keys" class="btn btn-primary">
            <i class="fas fa-key me-2"></i>Manage API Keys
        </a>
    </div>

    <?php if (count($currentKeys) > 0): ?>
    <div class="alert alert-success">
        <i class="fas fa-check-circle me-2"></i>
        <strong><?= count($currentKeys) ?> API key(s) active.</strong> 
        Your AI features are ready to use.
    </div>
    <?php else: ?>
    <div class="alert alert-warning">
        <i class="fas fa-exclamation-triangle me-2"></i>
        <strong>No active API keys.</strong> Add an API key below to enable AI features.
    </div>
    <?php endif; ?>

    <div class="row">
        <!-- Free Providers First -->
        <div class="col-lg-6">
            <h4 class="mb-3"><i class="fas fa-gift text-success me-2"></i>FREE Options (Recommended)</h4>
            
            <?php foreach ($providers as $p): if (!$p['free']) continue; ?>
            <div class="card guide-card">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                        <div class="me-3" style="width:40px;height:40px;background:<?= $p['color'] ?>;border-radius:8px;display:flex;align-items:center;justify-content:center;">
                            <i class="fas <?= $p['icon'] ?> text-white"></i>
                        </div>
                        <div>
                            <h5 class="mb-0"><?= $p['name'] ?></h5>
                            <span class="badge bg-success">FREE TIER</span>
                        </div>
                    </div>
                    <p class="text-muted small mb-2"><?= $p['desc'] ?></p>
                    <p class="mb-2 small"><strong>Available Models:</strong></p>
                    <ul class="small mb-3">
                        <?php foreach ($p['models'] as $m): ?>
                        <li><?= $m ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <a href="<?= $p['url'] ?>" target="_blank" class="btn btn-success btn-sm">
                        <i class="fas fa-external-link-alt me-1"></i>Get Free API Key
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Paid Providers -->
        <div class="col-lg-6">
            <h4 class="mb-3"><i class="fas fa-credit-card text-danger me-2"></i>Paid Options (Premium)</h4>
            
            <?php foreach ($providers as $p): if ($p['free']) continue; ?>
            <div class="card guide-card">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                        <div class="me-3" style="width:40px;height:40px;background:<?= $p['color'] ?>;border-radius:8px;display:flex;align-items:center;justify-content:center;">
                            <i class="fas <?= $p['icon'] ?> text-white"></i>
                        </div>
                        <div>
                            <h5 class="mb-0"><?= $p['name'] ?></h5>
                            <span class="badge bg-secondary">PAID</span>
                        </div>
                    </div>
                    <p class="text-muted small mb-2"><?= $p['desc'] ?></p>
                    <p class="mb-2 small"><strong>Models:</strong></p>
                    <ul class="small mb-3">
                        <?php foreach ($p['models'] as $m): ?>
                        <li><?= $m ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <a href="<?= $p['url'] ?>" target="_blank" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-external-link-alt me-1"></i>Get API Key
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- How to Add -->
    <div class="card guide-card mt-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="fas fa-question-circle me-2"></i>How to Add API Key</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <div class="step-card">
                        <strong>Step 1</strong>
                        <p class="mb-0 small text-muted">Click "Manage API Keys" button above</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="step-card">
                        <strong>Step 2</strong>
                        <p class="mb-0 small text-muted">Click "Add New Key" button</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="step-card">
                        <strong>Step 3</strong>
                        <p class="mb-0 small text-muted">Select service (OpenRouter, Groq, etc.)</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="step-card">
                        <strong>Step 4</strong>
                        <p class="mb-0 small text-muted">Paste API key and save</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Current Keys -->
    <?php if (count($currentKeys) > 0): ?>
    <div class="card guide-card mt-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-list me-2"></i>Current Active Keys</h5>
        </div>
        <div class="card-body p-0">
            <table class="table mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Service</th>
                        <th>Key Name</th>
                        <th>Status</th>
                        <th>Usage</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($currentKeys as $key): ?>
                    <tr>
                        <td><?= htmlspecialchars($key['service_name']) ?></td>
                        <td><code><?= substr(htmlspecialchars($key['key_value']), 0, 15) ?>...</code></td>
                        <td><span class="api-status api-active"><i class="fas fa-check me-1"></i>Active</span></td>
                        <td><?= $key['usage_count'] ?? 0 ?> times</td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>
</div>
