<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><?= htmlspecialchars($pageTitle ?? 'Smart Search') ?></h1>
        <a href="<?= $base ?? BASE_URL ?>/features/dashboard" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back</a>
    </div>
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body aps-cp-card-body">
            <div class="row g-2 align-items-end">
                <div class="col-md-5">
                    <label class="form-label">Search Query</label>
                    <input type="text" id="searchQuery" class="form-control form-control-lg" placeholder="e.g., 3 BHK flat under 50 lakhs in Gorakhpur..." value="<?= htmlspecialchars($query ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Type</label>
                    <select id="searchType" class="form-select">
                        <option value="all" <?= ($type ?? 'all') === 'all' ? 'selected' : '' ?>>All</option>
                        <option value="property" <?= ($type ?? '') === 'property' ? 'selected' : '' ?>>Properties</option>
                        <option value="project" <?= ($type ?? '') === 'project' ? 'selected' : '' ?>>Projects</option>
                        <option value="customer" <?= ($type ?? '') === 'customer' ? 'selected' : '' ?>>users</option>
                        <option value="lead" <?= ($type ?? '') === 'lead' ? 'selected' : '' ?>>Leads</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <button class="btn btn-primary w-100 btn-lg" onclick="doSearch()"><i class="fas fa-search me-1"></i>Search</button>
                </div>
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <button class="btn btn-outline-secondary w-100 btn-lg" onclick="clearSearch()"><i class="fas fa-times me-1"></i>Clear</button>
                </div>
            </div>
        </div>
    </div>
    <?php if (isset($searchPerformed) && $searchPerformed): ?>
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Results (<?= (int)($totalResults ?? 0) ?>)</h5>
            <small class="text-muted">Found in <?= round((float)($searchTime ?? 0), 3) ?>s</small>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <div class="table-responsive"><table class="table table-hover align-middle mb-0 table-responsive">
                    <thead class="table-light">
                        <tr><th>Type</th><th>Title / Name</th><th>Location</th><th>Match</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($results)): ?>
                            <?php foreach ($results as $r): ?>
                                <tr>
                                    <td><span class="badge bg-<?= ($r['type'] ?? 'property') === 'property' ? 'primary' : (($r['type'] ?? '') === 'project' ? 'success' : (($r['type'] ?? '') === 'customer' ? 'info' : 'warning')) ?>"><?= htmlspecialchars($r['type'] ?? '-') ?></span></td>
                                    <td><strong><?= htmlspecialchars($r['title'] ?? '') ?></strong></td>
                                    <td><?= htmlspecialchars($r['location'] ?? '-') ?></td>
                                    <td>
                                        <div class="progress" style="height:6px;width:100px"><div class="progress-bar bg-success" style="width:<?= (int)($r['score'] ?? 0) ?>%"></div></div>
                                        <small class="text-muted"><?= (int)($r['score'] ?? 0) ?>%</small>
                                    </td>
                                    <td>
                                        <?php if ($r['url'] ?? ''): ?>
                                            <a href="<?= htmlspecialchars($r['url']) ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-external-link-alt"></i></a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="5" class="text-center text-muted py-4"><i class="fas fa-search fa-2x d-block mb-2"></i>No results found for your query.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table></div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>
<script>
function doSearch() {
    const q = document.getElementById('searchQuery').value.trim();
    const t = document.getElementById('searchType').value;
    if (!q) return;
    window.location.href = '<?= $base ?? BASE_URL ?>/features/smart-search?q=' + encodeURIComponent(q) + '&type=' + encodeURIComponent(t);
}
function clearSearch() {
    window.location.href = '<?= $base ?? BASE_URL ?>/features/smart-search';
}
document.getElementById('searchQuery')?.addEventListener('keydown', function(e) { if (e.key === 'Enter') doSearch(); });
</script>
