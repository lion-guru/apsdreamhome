<?php
$pageTitle = $pageTitle ?? 'API Documentation';
$base = $base ?? (defined('BASE_URL') ? BASE_URL : '/' . trim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/'));
$endpoints = $endpoints ?? [
    ['method' => 'GET', 'path' => '/api/properties', 'description' => 'List all properties', 'params' => 'page, limit, type, location', 'response' => '{"data": [...], "total": 100, "page": 1}'],
    ['method' => 'GET', 'path' => '/api/properties/{id}', 'description' => 'Get property details', 'params' => 'id (int)', 'response' => '{"id": 1, "title": "..."}'],
    ['method' => 'GET', 'path' => '/api/locations/countries', 'description' => 'List countries', 'params' => '', 'response' => '{"data": [{"id": 1, "name": "India"}]}'],
    ['method' => 'GET', 'path' => '/api/locations/states', 'description' => 'List states by country', 'params' => 'country_id', 'response' => '{"data": [{"id": 1, "name": "Uttar Pradesh"}]}'],
    ['method' => 'GET', 'path' => '/api/locations/cities', 'description' => 'List cities by district', 'params' => 'district_id', 'response' => '{"data": [{"id": 1, "name": "Gorakhpur"}]}'],
    ['method' => 'GET', 'path' => '/api/locations/pincode/{pincode}', 'description' => 'Lookup pincode details', 'params' => 'pincode', 'response' => '{"city": "Gorakhpur", "state": "Uttar Pradesh"}'],
    ['method' => 'GET', 'path' => '/api/banks/search', 'description' => 'Search banks', 'params' => 'q (search query)', 'response' => '{"data": [{"name": "SBI"}]}'],
    ['method' => 'GET', 'path' => '/api/banks/ifsc/{ifsc}', 'description' => 'IFSC code lookup', 'params' => 'ifsc', 'response' => '{"bank": "SBI", "branch": "Main Branch"}'],
    ['method' => 'POST', 'path' => '/api/newsletter/subscribe', 'description' => 'Subscribe to newsletter', 'params' => 'email, name', 'response' => '{"success": true}'],
    ['method' => 'POST', 'path' => '/api/ai/chatbot', 'description' => 'AI chatbot message', 'params' => 'message, session_id', 'response' => '{"reply": "...", "intent": "..."}'],
];
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><i class="fas fa-book me-2 text-primary"></i>API Documentation</h1>
        <a href="<?= $base ?>/admin/dashboard" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
    </div>
    <div class="row mb-4">
        <div class="col-12">
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-1"></i>
                Base URL: <code><?= $base ?>/api</code> | All responses are JSON. Authentication required for write endpoints.
            </div>
        </div>
    </div>
    <div class="card shadow">
        <div class="card-header py-3"><h6 class="m-0 fw-bold text-primary">API Endpoints (<?= count($endpoints) ?>)</h6></div>
        <div class="card-body aps-cp-card-body">
            <?php foreach ($endpoints as $i => $ep): ?>
            <div class="card mb-3 border-<?= $ep['method'] === 'GET' ? 'success' : ($ep['method'] === 'POST' ? 'primary' : ($ep['method'] === 'PUT' ? 'warning' : 'danger')) ?>">
                <div class="card-body aps-cp-card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <span class="badge bg-<?= $ep['method'] === 'GET' ? 'success' : ($ep['method'] === 'POST' ? 'primary' : ($ep['method'] === 'PUT' ? 'warning' : 'danger')) ?> me-2 p-2"><?= $ep['method'] ?></span>
                            <code class="fs-6"><?= htmlspecialchars($ep['path']) ?></code>
                        </div>
                        <a class="btn btn-sm btn-outline-secondary" data-bs-toggle="collapse" href="#epDetails<?= $i ?>"><i class="fas fa-chevron-down"></i></a>
                    </div>
                    <p class="mb-1"><?= htmlspecialchars($ep['description'] ?? '') ?></p>
                    <div class="collapse" id="epDetails<?= $i ?>">
                        <hr>
                        <?php if (!empty($ep['params'])): ?>
                        <h6 class="fw-bold">Parameters</h6>
                        <p><code><?= htmlspecialchars($ep['params']) ?></code></p>
                        <?php endif; ?>
                        <?php if (!empty($ep['response'])): ?>
                        <h6 class="fw-bold">Sample Response</h6>
                        <pre class="bg-dark text-light p-3 rounded"><code><?= htmlspecialchars($ep['response']) ?></code></pre>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
