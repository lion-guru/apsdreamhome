<?php
/**
 * API Documentation — Swagger UI with admin layout
 *
 * @var array $groups   Grouped endpoint data from ApiDocService::getEndpoints()
 * @var int   $total    Total route count
 * @var string $specUrl URL to the JSON spec endpoint
 * @var string $activeVersion  'v1' or 'v2'
 */
$groups       = $groups ?? [];
$total        = $total ?? 0;
$specUrl      = $specUrl ?? (defined('BASE_URL') ? BASE_URL : '') . '/api/docs/spec';
$activeVersion = $activeVersion ?? 'v2';
$v1SpecUrl    = (defined('BASE_URL') ? BASE_URL : '') . '/api/docs/spec/v1';
$v2SpecUrl    = (defined('BASE_URL') ? BASE_URL : '') . '/api/docs/spec/v2';

$groupCount = count($groups);
?>

<style>
    /* Swagger UI dark-theme overrides for admin panel */
    #swagger-wrapper { background: #1a1d21; border-radius: 10px; overflow: hidden; }
    #swagger-wrapper .swagger-ui .topbar { background: #1a1d21; border-bottom: 1px solid #2d3136; }
    #swagger-wrapper .swagger-ui .info .title { color: #e1e4e8; }
    #swagger-wrapper .swagger-ui .info .description p,
    #swagger-wrapper .swagger-ui .info .description span { color: #9ca3af; }
    #swagger-wrapper .swagger-ui .scheme-container { background: #1a1d21; box-shadow: none; }
    #swagger-wrapper .swagger-ui .opblock-summary { border-radius: 6px; }
    #swagger-wrapper .swagger-ui .opblock-get { border-color: #3b82f6; }
    #swagger-wrapper .swagger-ui .opblock-post { border-color: #22c55e; }
    #swagger-wrapper .swagger-ui .opblock-put { border-color: #f59e0b; }
    #swagger-wrapper .swagger-ui .opblock-delete { border-color: #ef4444; }
    #swagger-wrapper .swagger-ui .opblock-patch { border-color: #14b8a6; }

    .doc-stats-card {
        background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
        border: 1px solid #334155;
        border-radius: 10px;
        padding: 16px 20px;
        color: #e2e8f0;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .doc-stats-card:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(0,0,0,0.3); }
    .doc-stats-card .stat-value { font-size: 1.8rem; font-weight: 700; }
    .doc-stats-card .stat-label { font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.05em; opacity: 0.7; }

    .group-card {
        background: #1e293b;
        border: 1px solid #334155;
        border-radius: 8px;
        margin-bottom: 8px;
        cursor: pointer;
        transition: all 0.15s;
    }
    .group-card:hover { border-color: #3b82f6; background: #253348; }
    .group-card .group-name { font-weight: 600; color: #e2e8f0; }
    .group-card .group-count { background: #334155; color: #94a3b8; padding: 2px 8px; border-radius: 12px; font-size: 0.75rem; }

    .method-badge {
        display: inline-block;
        font-size: 0.65rem;
        font-weight: 700;
        padding: 1px 6px;
        border-radius: 3px;
        text-transform: uppercase;
        min-width: 42px;
        text-align: center;
    }
    .method-GET    { background: #1e3a5f; color: #60a5fa; }
    .method-POST   { background: #14532d; color: #4ade80; }
    .method-PUT    { background: #451a03; color: #fbbf24; }
    .method-DELETE { background: #450a0a; color: #f87171; }
    .method-PATCH  { background: #3b0764; color: #5eead4; }

    .endpoint-row { padding: 4px 12px; border-bottom: 1px solid #1e293b; font-family: 'Fira Code', monospace; font-size: 0.78rem; }
    .endpoint-row:hover { background: #253348; }
    .endpoint-path { color: #e2e8f0; }
    .endpoint-auth { color: #64748b; font-size: 0.65rem; }

    .version-btn {
        padding: 6px 18px;
        border: 1px solid #334155;
        border-radius: 6px;
        background: transparent;
        color: #94a3b8;
        cursor: pointer;
        font-weight: 600;
        transition: all 0.15s;
    }
    .version-btn.active { background: #3b82f6; color: #fff; border-color: #3b82f6; }
    .version-btn:hover:not(.active) { border-color: #60a5fa; color: #e2e8f0; }

    #swagger-loading {
        display: flex; align-items: center; justify-content: center;
        min-height: 400px; color: #64748b; font-size: 1rem;
    }
    #swagger-loading .spinner { width: 32px; height: 32px; border: 3px solid #334155; border-top: 3px solid #3b82f6;
        border-radius: 50%; animation: spin 0.8s linear infinite; margin-right: 12px; }
    @keyframes spin { to { transform: rotate(360deg); } }
</style>

<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">
                <i class="fas fa-book me-2 text-primary"></i>API Documentation
            </h4>
            <p class="text-muted small mb-0">
                Auto-generated OpenAPI 3.0 specification from <code>routes/api.php</code> &mdash;
                <?= $total ?> endpoints across <?= $groupCount ?> groups
            </p>
        </div>
        <div class="d-flex align-items-center gap-3">
            <!-- Version Switcher -->
            <div class="btn-group" role="group">
                <button type="button" class="version-btn <?= $activeVersion === 'v1' ? 'active' : '' ?>"
                        onclick="switchVersion('v1')">v1 (Legacy)</button>
                <button type="button" class="version-btn <?= $activeVersion === 'v2' ? 'active' : '' ?>"
                        onclick="switchVersion('v2')">v2 (Current)</button>
            </div>
            <!-- Download button -->
            <button class="btn btn-outline-success btn-sm" onclick="downloadSpec()">
                <i class="fas fa-download me-1"></i>Export JSON
            </button>
            <!-- Direct link -->
            <a href="<?= $specUrl ?>" target="_blank" class="btn btn-outline-info btn-sm">
                <i class="fas fa-external-link-alt me-1"></i>Raw Spec
            </a>
        </div>
    </div>

    <!-- Stats row -->
    <div class="row mb-4">
        <div class="col-md-2">
            <div class="doc-stats-card">
                <div class="stat-value text-primary"><?= $total ?></div>
                <div class="stat-label">Total Endpoints</div>
            </div>
        </div>
        <div class="col-md-2">
            <?php
            $getCount = 0; $postCount = 0; $putCount = 0; $delCount = 0;
            foreach ($groups as $eps) { foreach ($eps as $e) {
                if ($e['method'] === 'GET') $getCount++;
                elseif ($e['method'] === 'POST') $postCount++;
                elseif ($e['method'] === 'PUT') $putCount++;
                elseif ($e['method'] === 'DELETE') $delCount++;
            }}
            ?>
            <div class="doc-stats-card">
                <div class="stat-value" style="color:#60a5fa"><?= $getCount ?></div>
                <div class="stat-label">GET</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="doc-stats-card">
                <div class="stat-value" style="color:#4ade80"><?= $postCount ?></div>
                <div class="stat-label">POST</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="doc-stats-card">
                <div class="stat-value" style="color:#fbbf24"><?= $putCount ?></div>
                <div class="stat-label">PUT</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="doc-stats-card">
                <div class="stat-value" style="color:#f87171"><?= $delCount ?></div>
                <div class="stat-label">DELETE</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="doc-stats-card">
                <div class="stat-value" style="color:#5eead4"><?= $groupCount ?></div>
                <div class="stat-label">Groups</div>
            </div>
        </div>
    </div>

    <!-- Endpoint groups sidebar + Swagger UI -->
    <div class="row">
        <!-- Left: endpoint index -->
        <div class="col-md-3" style="max-height: 70vh; overflow-y: auto;">
            <h6 class="text-muted mb-3 fw-bold text-uppercase" style="font-size:0.7rem; letter-spacing:0.1em;">
                <i class="fas fa-list me-1"></i>Endpoint Groups
            </h6>
            <?php foreach ($groups as $groupName => $endpoints): ?>
                <div class="group-card mb-2 p-2" onclick="scrollToTag('<?= htmlspecialchars($groupName) ?>')">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="group-name"><?= htmlspecialchars($groupName) ?></span>
                        <span class="group-count"><?= count($endpoints) ?></span>
                    </div>
                    <div class="mt-1" style="max-height: 100px; overflow-y: auto;">
                        <?php foreach (array_slice($endpoints, 0, 8) as $ep): ?>
                            <div class="endpoint-row d-flex align-items-center gap-2">
                                <span class="method-badge method-<?= $ep['method'] ?>"><?= $ep['method'] ?></span>
                                <span class="endpoint-path text-truncate" style="color:#94a3b8;">
                                    <?= htmlspecialchars(preg_replace('#^/api(/v\d+)?#', '', $ep['path'])) ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                        <?php if (count($endpoints) > 8): ?>
                            <div class="endpoint-row text-muted" style="font-size:0.7rem;">
                                +<?= count($endpoints) - 8 ?> more
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Right: Swagger UI -->
        <div class="col-md-9">
            <div id="swagger-wrapper">
                <div id="swagger-loading">
                    <div class="spinner"></div>
                    <span>Loading API documentation...</span>
                </div>
                <div id="swagger-ui"></div>
            </div>
        </div>
    </div>
</div>

<!-- Swagger UI from CDN -->
<link rel="stylesheet" href="https://unpkg.com/swagger-ui-dist@5/swagger-ui.css">
<script src="https://unpkg.com/swagger-ui-dist@5/swagger-ui-bundle.js"></script>

<script>
    const BASE_URL = '<?= defined('BASE_URL') ? BASE_URL : '/' . trim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/') ?>';
    let currentVersion = '<?= $activeVersion ?>';
    let swaggerUiInstance = null;

    function loadSwagger(specUrl) {
        const loading = document.getElementById('swagger-loading');
        const container = document.getElementById('swagger-ui');
        container.innerHTML = '';
        loading.style.display = 'flex';

        // Destroy previous instance
        if (swaggerUiInstance) {
            try { swaggerUiInstance.dispose(); } catch(e) {}
        }

        setTimeout(() => {
            loading.style.display = 'none';
            try {
                swaggerUiInstance = SwaggerUIBundle({
                    url: specUrl,
                    dom_id: '#swagger-ui',
                    deepLinking: true,
                    presets: [
                        SwaggerUIBundle.presets.apis,
                        SwaggerUIBundle.SwaggerUIStandalonePreset
                    ],
                    layout: 'BaseLayout',
                    tryItOutEnabled: true,
                    docExpansion: 'list',
                    defaultModelsExpandDepth: 0,
                    defaultModelExpandDepth: 1,
                    filter: true,
                    requestSnippetsEnabled: true,
                    syntaxHighlight: { activate: true, theme: 'monokai' },
                });
            } catch (e) {
                container.innerHTML = '<div class="p-4 text-danger">Failed to load Swagger UI: ' + e.message + '</div>';
            }
        }, 300);
    }

    function switchVersion(version) {
        currentVersion = version;
        document.querySelectorAll('.version-btn').forEach(btn => {
            btn.classList.toggle('active', btn.textContent.toLowerCase().includes(version));
        });
        const specUrl = version === 'v1'
            ? BASE_URL + '/api/docs/spec/v1'
            : BASE_URL + '/api/docs/spec/v2';
        loadSwagger(specUrl);
    }

    function scrollToTag(tagName) {
        // Swagger UI renders tag headings; try to scroll to them
        const el = document.querySelector('[id="tag/' + tagName.toLowerCase().replace(/[^a-z0-9]+/g, '-') + '"]');
        if (el) { el.scrollIntoView({ behavior: 'smooth', block: 'start' }); return; }
        // Fallback: use Swagger UI's built-in filter
        if (swaggerUiInstance) {
            try { swaggerUiInstance.layoutSelectors.modelSelectors.tagName.innerHTML; } catch(e) {}
        }
    }

    function downloadSpec() {
        window.open(BASE_URL + '/api/docs/spec/' + currentVersion + '?download=1', '_blank');
    }

    // Initial load
    document.addEventListener('DOMContentLoaded', function() {
        loadSwagger('<?= $specUrl ?>');
    });
</script>
