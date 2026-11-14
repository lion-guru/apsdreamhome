<?php
/**
 * Migration Audit dashboard — READ-ONLY visibility into schema management.
 * Shows live tracked runs vs files on disk; highlights the gap.
 */
$applied = $applied ?? [];
$diskFiles = $diskFiles ?? [];
$unknown = $unknown ?? [];
$missingRefs = $missingRefs ?? [];
$liveTableCount = $liveTableCount ?? 0;
$matches = $matches ?? 0;
$unknownCount = $unknownCount ?? 0;
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h2 class="mb-0"><i class="fas fa-database me-2 text-primary"></i>Migration Audit</h2>
            <small class="text-muted">Read-only — confirm what has shipped, what is live, what is missing.</small>
        </div>
        <a href="<?= BASE_URL ?>/admin/backups" class="btn btn-outline-secondary btn-sm"><i class="fas fa-shield-alt me-1"></i>Backup Desk</a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card h-100 border-start border-info border-4">
                <div class="card-body d-flex align-items-center gap-3">
                    <i class="fas fa-file-code fa-2x text-info"></i>
                    <div>
                        <div class="text-muted small text-uppercase">On disk</div>
                        <div class="fs-3 fw-bold text-light"><?= count($diskFiles) ?></div>
                        <small class="text-muted">.sql/.php files in database/migrations</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card h-100 border-start border-success border-4">
                <div class="card-body d-flex align-items-center gap-3">
                    <i class="fas fa-check-circle fa-2x text-success"></i>
                    <div>
                        <div class="text-muted small text-uppercase">Recorded</div>
                        <div class="fs-3 fw-bold text-light"><?= count($applied) ?></div>
                        <small class="text-muted">rows in schema_migrations</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card h-100 border-start border-warning border-4">
                <div class="card-body d-flex align-items-center gap-3">
                    <i class="fas fa-exclamation-triangle fa-2x text-warning"></i>
                    <div>
                        <div class="text-muted small text-uppercase">Unrecorded</div>
                        <div class="fs-3 fw-bold text-light"><?= $unknownCount ?></div>
                        <small class="text-muted">files with no matching live row</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card h-100 border-start border-primary border-4">
                <div class="card-body d-flex align-items-center gap-3">
                    <i class="fas fa-table fa-2x text-primary"></i>
                    <div>
                        <div class="text-muted small text-uppercase">Live tables</div>
                        <div class="fs-3 fw-bold text-light"><?= (int)$liveTableCount ?></div>
                        <small class="text-muted">information_schema count</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h5 class="mb-0"><i class="fas fa-clipboard-list me-2"></i>Recorded migrations (live DB)</h5>
            <span class="badge bg-success">source of truth for the <?= count($applied) ?> tracked runs</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr class="text-muted small"><th>#</th><th>Version</th><th>Name</th><th>Executed at</th></tr>
                    </thead>
                    <tbody>
                        <?php if (!$applied): ?>
                            <tr><td colspan="4" class="text-center text-muted py-4">schema_migrations table is empty — nothing tracked.</td></tr>
                        <?php else: ?>
                            <?php foreach ($applied as $i => $row): ?>
                                <tr>
                                    <td><?= (int)$i + 1 ?></td>
                                    <td><code><?= htmlspecialchars($row['version']) ?></code></td>
                                    <td><?= htmlspecialchars($row['name']) ?></td>
                                    <td><small class="text-muted"><?= htmlspecialchars($row['executed_at']) ?></small></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card mb-4 border-start border-warning border-4">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h5 class="mb-0"><i class="fas fa-exclamation-triangle me-2 text-warning"></i>Unrecorded migration files (<?= count($diskFiles) - (int)$matches ?>)</h5>
            <span class="badge bg-warning text-dark">on disk but not marked applied</span>
        </div>
        <div class="card-body">
            <div class="alert alert-warning mb-3 py-2">
                <strong>Why this matters:</strong> every migration file in the repo has not been reconciled against the live database. Some may have been applied via SQL directly; some may be awaiting apply.
                This page is <strong>read-only</strong>; it cannot record runs in the ledger.
            </div>
            <div class="row g-2" style="max-height: 420px; overflow-y: auto;">
                <?php $unrecorded = 0; ?>
                <?php foreach ($diskFiles as $df): ?>
                    <?php if ($df['tracked']) continue; $unrecorded++; ?>
                    <div class="col-md-4">
                        <div class="border rounded p-2 h-100 bg-dark border-secondary">
                            <div class="d-flex justify-content-between align-items-center mb-1 gap-2">
                                <code class="small text-light text-truncate" title="<?= htmlspecialchars($df['name']) ?>"><?= htmlspecialchars($df['name']) ?></code>
                                <?php if ($df['mtime'] ?? 0): ?>
                                    <small class="text-muted flex-shrink-0"><?= date('Y-m-d', $df['mtime']) ?></small>
                                <?php endif; ?>
                            </div>
                            <small class="text-muted"><?= number_format(max(0, (int)$df['size']) / 1024, 1) ?> KB</small>
                        </div>
                    </div>
                <?php endforeach; ?>
                <?php if ($unrecorded === 0): ?>
                    <div class="col-12"><div class="alert alert-success mb-0">All migration files on disk are tracked in the live ledger.</div></div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Gap analysis</h5></div>
        <div class="card-body">
            <?php if ($missingRefs): ?>
                <div class="alert alert-danger">Referenced tables/files missing: <strong><?= htmlspecialchars(implode(', ', $missingRefs)) ?></strong></div>
            <?php else: ?>
                <div class="alert alert-success mb-0">
                    No missing references. Every table this audit reads exists in the live database.
                    <small class="d-block text-muted mt-1">Next hard step: wire an idempotent runner (e.g. <code>scripts/run_all_migrations.php</code>) so each migration records itself in <code>schema_migrations</code> — currently <?= count($applied) ?> of <?= count($diskFiles) ?> files are tracked.</small>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
