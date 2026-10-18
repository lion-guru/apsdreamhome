<?php
/**
 * RAG Knowledge Base admin desk (Session 341)
 * Health card + model switch + re-embed + document inventory.
 */
$page_title = $page_title ?? 'RAG Knowledge Base';
$health = $health ?? [];
$documents = $documents ?? [];
$csrf = $csrf ?? ($_SESSION['csrf_token'] ?? '');
$base = defined('BASE_URL') ? BASE_URL : '';
$degraded = !empty($health['degraded']);
$stale = (int)($health['stale_chunks'] ?? 0);
$total = (int)($health['total_chunks'] ?? 0);
$stored = $health['stored_models'] ?? [];
?>
<div class="container-fluid px-4 py-3">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="mb-1 fw-bold"><i class="fas fa-brain me-2 text-primary"></i>RAG Knowledge Base</h2>
            <p class="text-muted mb-0">Semantic search over your own documents. Embedding provider, health and re-indexing.</p>
        </div>
        <a href="<?= $base ?>/admin/ai-settings" class="btn btn-outline-primary"><i class="fas fa-cog me-1"></i>API Keys</a>
    </div>

    <?php if ($degraded): ?>
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-triangle me-2"></i>
        <strong>Embedding provider degraded.</strong> The last probe fell back to <code>hash-placeholder-v2</code>,
        which means RAG search quality is NOT real semantic matching right now.
        <?php if (empty($health['gemini_key_set']) && empty($health['hf_key_set'])): ?>
        No embedding key is configured — add a Gemini API key in
        <a href="<?= $base ?>/admin/ai-settings">AI Settings</a> (free tier is enough).
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <?php if ($stale > 0): ?>
    <div class="alert alert-warning">
        <i class="fas fa-sync-alt me-2"></i>
        <strong><?= $stale ?> of <?= $total ?> chunks</strong> still carry a placeholder vector.
        Run <strong>Re-embed</strong> below to upgrade them to the active provider.
    </div>
    <?php endif; ?>

    <!-- Health card -->
    <div class="row g-3 mb-3">
        <div class="col-md-3">
            <div class="card h-100"><div class="card-body">
                <div class="text-muted small">Provider</div>
                <div class="h5 mb-0">
                    <?php if ($degraded): ?>
                        <span class="text-danger"><i class="fas fa-times-circle me-1"></i>degraded</span>
                    <?php else: ?>
                        <span class="text-success"><i class="fas fa-check-circle me-1"></i><?= htmlspecialchars($health['probe_provider'] ?? '?') ?></span>
                    <?php endif; ?>
                </div>
                <div class="small text-muted"><?= htmlspecialchars($health['probe_model'] ?? '') ?></div>
            </div></div>
        </div>
        <div class="col-md-3">
            <div class="card h-100"><div class="card-body">
                <div class="text-muted small">Vector dimension</div>
                <div class="h5 mb-0"><?= (int)($health['probe_dimension'] ?? 0) ?><span class="fs-6 text-muted">-dim</span></div>
                <div class="small text-muted"><?= htmlspecialchars($health['configured_model'] ?? '') ?></div>
            </div></div>
        </div>
        <div class="col-md-3">
            <div class="card h-100"><div class="card-body">
                <div class="text-muted small">Chunks</div>
                <div class="h5 mb-0"><?= $total ?></div>
                <div class="small text-muted">
                    <?php if ($stale > 0): ?>
                        <span class="text-warning"><?= $stale ?> stale</span>
                    <?php else: ?>
                        <span class="text-success">all real</span>
                    <?php endif; ?>
                </div>
            </div></div>
        </div>
        <div class="col-md-3">
            <div class="card h-100"><div class="card-body">
                <div class="text-muted small">Keys</div>
                <div class="h6 mb-0">
                    <i class="fas fa-key me-1 text-<?= !empty($health['gemini_key_set']) ? 'success' : 'secondary' ?>"></i>Gemini
                    <i class="fas fa-key ms-2 me-1 text-<?= !empty($health['hf_key_set']) ? 'success' : 'secondary' ?>"></i>HF
                </div>
                <?php if (!empty($health['hf_last_error'])): ?>
                    <div class="small text-warning">HF: <?= htmlspecialchars((string)$health['hf_last_error']) ?></div>
                <?php endif; ?>
                <?php if (!empty($health['gemini_last_error'])): ?>
                    <div class="small text-warning">Gemini: <?= htmlspecialchars((string)$health['gemini_last_error']) ?></div>
                <?php endif; ?>
            </div></div>
        </div>
    </div>

    <!-- Model + re-embed -->
    <div class="card mb-3">
        <div class="card-header"><h5 class="mb-0"><i class="fas fa-sliders-h me-2"></i>Embedding provider &amp; re-index</h5></div>
        <div class="card-body">
            <form id="ragModelForm" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small">Embedding model</label>
                    <select class="form-select" name="model" id="ragModel">
                        <option value="gemini-embedding-001" <?= (($health['configured_model'] ?? '') === 'gemini-embedding-001') ? 'selected' : '' ?>>Gemini gemini-embedding-001 (FREE, 3072-dim)</option>
                        <option value="hf-all-MiniLM-L6-v2" <?= (($health['configured_model'] ?? '') === 'hf-all-MiniLM-L6-v2') ? 'selected' : '' ?>>HuggingFace all-MiniLM-L6-v2 (needs PRO)</option>
                    </select>
                    <div class="form-text">Provider fallback order is Gemini &rarr; HuggingFace &rarr; placeholder, so a switch never breaks search.</div>
                </div>
                <div class="col-md-3">
                    <label class="form-label small">Mode</label>
                    <select class="form-select" name="mode">
                        <option value="incremental">Stale chunks only (idempotent)</option>
                        <option value="force">Force — rewrite every chunk</option>
                    </select>
                </div>
                <div class="col-md-5 d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Save model</button>
                    <button type="button" class="btn btn-outline-secondary" id="ragReembedBtn"><i class="fas fa-sync-alt me-1"></i>Re-embed now</button>
                </div>
            </form>
            <div id="ragResult" class="mt-3"></div>
        </div>
    </div>

    <!-- Stored model composition -->
    <?php if ($stored): ?>
    <div class="card mb-3">
        <div class="card-header"><h6 class="mb-0">Stored chunk composition</h6></div>
        <div class="card-body py-2">
            <?php foreach ($stored as $model => $n): ?>
                <span class="badge <?= str_contains((string)$model, 'hash-placeholder') ? 'bg-warning text-dark' : 'bg-success' ?> me-1">
                    <?= htmlspecialchars((string)$model) ?> = <?= (int)$n ?>
                </span>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Documents -->
    <div class="card">
        <div class="card-header"><h5 class="mb-0"><i class="fas fa-book me-2"></i>Indexed documents</h5></div>
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead>
                    <tr>
                        <th>#</th><th>Title</th><th>Category</th><th>Source</th>
                        <th class="text-center">Chunks</th><th>Status</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (!$documents): ?>
                    <tr><td colspan="6" class="text-center text-muted py-4">No knowledge-base documents yet.</td></tr>
                <?php else: foreach ($documents as $doc): ?>
                    <tr>
                        <td><?= (int)$doc['id'] ?></td>
                        <td><?= htmlspecialchars((string)$doc['title']) ?></td>
                        <td><?= htmlspecialchars((string)($doc['tags'] ?? '-')) ?></td>
                        <td class="text-muted small"><?= htmlspecialchars((string)($doc['source'] ?? '-')) ?></td>
                        <td class="text-center"><?= (int)($doc['chunk_count'] ?? 0) ?></td>
                        <td>
                            <?php if ((int)$doc['is_active'] === 1): ?>
                                <span class="badge bg-success">active</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">off</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
(function () {
    const CSRF = <?= json_encode($csrf) ?>;
    const BASE = <?= json_encode($base) ?>;
    const out = document.getElementById('ragResult');

    function render(d) {
        if (!out) return;
        if (d.success) {
            let extra = '';
            if (d.reembed) {
                extra = `<div class="small mt-1">re-embedded ${d.reembed.reembedded}/${d.reembed.total}, ` +
                        `${d.reembed.skipped} skipped, ${d.reembed.failed} failed (provider: ${d.reembed.active_provider}).</div>`;
            }
            out.innerHTML = `<div class="alert alert-success mb-0">${d.message}${extra}</div>`;
            setTimeout(() => location.reload(), 2500);
        } else {
            out.innerHTML = `<div class="alert alert-danger mb-0">${d.message || 'Failed'}</div>`;
        }
    }

    document.getElementById('ragModelForm')?.addEventListener('submit', function (e) {
        e.preventDefault();
        const fd = new FormData(this);
        fd.append('csrf_token', CSRF);
        fd.append('reembed', '1');
        out.innerHTML = '<div class="text-muted"><i class="fas fa-spinner fa-spin me-1"></i>Saving and re-embedding…</div>';
        fetch(BASE + '/admin/rag/set-model', {
            method: 'POST', body: new URLSearchParams(fd),
            headers: { 'X-CSRF-Token': CSRF }
        }).then(r => r.json()).then(render).catch(x => render({ success: false, message: x.message }));
    });

    document.getElementById('ragReembedBtn')?.addEventListener('click', function () {
        const mode = document.querySelector('[name=mode]').value;
        out.innerHTML = '<div class="text-muted"><i class="fas fa-spinner fa-spin me-1"></i>Re-embedding chunks…</div>';
        fetch(BASE + '/admin/rag/reembed', {
            method: 'POST',
            headers: { 'X-CSRF-Token': CSRF, 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'csrf_token=' + encodeURIComponent(CSRF) + '&mode=' + encodeURIComponent(mode)
        }).then(r => r.json()).then(render).catch(x => render({ success: false, message: x.message }));
    });
})();
</script>