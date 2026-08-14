<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><?= htmlspecialchars($pageTitle ?? 'Search Logs') ?></h1>
        <a href="<?= $base ?? BASE_URL ?>/logging/dashboard" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back</a>
    </div>
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body aps-cp-card-body">
            <form method="GET" action="<?= $base ?? BASE_URL ?>/logging/search" class="row g-3">
    <?php echo CSRFProtection::csrfField(); ?>
                <div class="col-md-4">
                    <label class="form-label">Keyword</label>
                    <input type="text" name="q" class="form-control" placeholder="Search message, file, IP..." value="<?= htmlspecialchars($query ?? '') ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Level</label>
                    <select name="level" class="form-select">
                        <option value="">All</option>
                        <option value="error" <?= ($level ?? '') === 'error' ? 'selected' : '' ?>>Error</option>
                        <option value="warning" <?= ($level ?? '') === 'warning' ? 'selected' : '' ?>>Warning</option>
                        <option value="info" <?= ($level ?? '') === 'info' ? 'selected' : '' ?>>Info</option>
                        <option value="debug" <?= ($level ?? '') === 'debug' ? 'selected' : '' ?>>Debug</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">From</label>
                    <input type="date" name="from" class="form-control" value="<?= htmlspecialchars($from ?? '') ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label">To</label>
                    <input type="date" name="to" class="form-control" value="<?= htmlspecialchars($to ?? '') ?>">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search me-1"></i>Search</button>
                </div>
            </form>
        </div>
    </div>
    <?php if (isset($searched) && $searched): ?>
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom">
            <h5 class="mb-0">Results (<?= (int)($total ?? 0) ?>)</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive" class="style-99097">
                <div class="table-responsive"><table class="table table-hover table-sm mb-0 table-responsive">
                    <thead class="table-light position-sticky top-0">
                        <tr><th>Level</th><th>Message</th><th>File</th><th>Line</th><th>Time</th></tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($results)): ?>
                            <?php foreach ($results as $r): ?>
                                <tr>
                                    <td><span class="badge bg-<?= ($r['level'] ?? 'info') === 'error' ? 'danger' : (($r['level'] ?? 'info') === 'warning' ? 'warning' : (($r['level'] ?? 'info') === 'debug' ? 'secondary' : 'info')) ?>"><?= htmlspecialchars($r['level'] ?? 'info') ?></span></td>
                                    <td><?= htmlspecialchars($r['message'] ?? '') ?></td>
                                    <td><code><?= htmlspecialchars(basename($r['file'] ?? '-')) ?></code></td>
                                    <td><?= (int)($r['line'] ?? 0) ?></td>
                                    <td class="text-nowrap small"><?= htmlspecialchars($r['created_at'] ?? '') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="5" class="text-center text-muted py-3">No results found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table></div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>
