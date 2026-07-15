<div class="container-fluid py-4">
    <h4 class="mb-4"><i class="fas fa-robot me-2 text-success"></i>AI Lead Qualifier</h4>
    <div class="row">
        <div class="col-md-4">
            <div class="card shadow-sm mb-3">
                <div class="card-body text-center">
                    <h5>Unqualified Leads</h5>
                    <h2 class="text-warning"><?= (int)($stats['unqualified'] ?? 0) ?></h2>
                    <form method="POST" action="<?= BASE_URL ?>/admin/ai-system/qualifier/run">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                        <button type="submit" class="btn btn-success mt-2"><i class="fas fa-play me-1"></i>Run Qualifier</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm mb-3">
                <div class="card-body text-center">
                    <h5>Hot Leads</h5>
                    <h2 class="text-danger"><?= (int)($stats['hot'] ?? 0) ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm mb-3">
                <div class="card-body text-center">
                    <h5>Cold Leads</h5>
                    <h2 class="text-secondary"><?= (int)($stats['cold'] ?? 0) ?></h2>
                </div>
            </div>
        </div>
    </div>
    <div class="card shadow-sm">
        <div class="card-header"><i class="fas fa-list me-1"></i>Recently Qualified</div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead><tr><th>Lead</th><th>Score</th><th>Category</th><th>Qualified At</th></tr></thead>
                    <tbody>
                        <?php if (!empty($recent)): ?>
                            <?php foreach ($recent as $r): ?>
                                <tr>
                                    <td><?= htmlspecialchars($r['name'] ?? '') ?></td>
                                    <td><?= (int)($r['lead_score'] ?? 0) ?></td>
                                    <td><span class="badge bg-<?= ($r['lead_category'] ?? '') === 'hot' ? 'danger' : (($r['lead_category'] ?? '') === 'warm' ? 'warning' : 'secondary') ?>"><?= htmlspecialchars($r['lead_category'] ?? 'unscored') ?></span></td>
                                    <td><small><?= htmlspecialchars($r['last_scored_at'] ?? '') ?></small></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="4" class="text-muted text-center">No results yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>