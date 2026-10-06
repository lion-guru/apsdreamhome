<?php $page_title = 'IT Support Toolbox'; ?>
<div class="container-fluid py-4">
    <h2 class="mb-4"><i class="fas fa-toolbox me-2"></i>IT Support Toolbox</h2>

    <div class="row mb-4">
        <div class="col-md-2"><div class="card border-0 shadow-sm"><div class="card-body text-center"><span class="badge bg-<?= !empty($health['db']) ? 'success' : 'danger' ?> mb-2"><?= !empty($health['db']) ? 'DB UP' : 'DB DOWN' ?></span><br><small class="text-muted">Database</small></div></div></div>
        <div class="col-md-2"><div class="card border-0 shadow-sm"><div class="card-body text-center"><h4><?= number_format($health['tables'] ?? 0) ?></h4><small class="text-muted">Tables</small></div></div></div>
        <div class="col-md-2"><div class="card border-0 shadow-sm"><div class="card-body text-center"><h4><?= number_format($health['users'] ?? 0) ?></h4><small class="text-muted">Users</small></div></div></div>
        <div class="col-md-2"><div class="card border-0 shadow-sm"><div class="card-body text-center"><h4><?= number_format($health['audit_events'] ?? 0) ?></h4><small class="text-muted">Audit Events</small></div></div></div>
        <div class="col-md-2"><div class="card border-0 shadow-sm"><div class="card-body text-center"><h4><?= $health['disk_free_gb'] !== null ? $health['disk_free_gb'] . 'G' : '—' ?></h4><small class="text-muted">Disk Free</small></div></div></div>
        <div class="col-md-2"><div class="card border-0 shadow-sm"><div class="card-body text-center"><h4 class="fs-6"><?= htmlspecialchars($health['php'] ?? '') ?></h4><small class="text-muted">PHP</small></div></div></div>
    </div>

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white"><h6 class="mb-0"><i class="fas fa-wrench me-2"></i>Fixer Tools (no coding needed)</h6></div>
                <div class="card-body">
                    <div class="row g-3">
                        <?php foreach (($tools ?? []) as $t): ?>
                            <div class="col-md-6">
                                <a href="<?= BASE_URL . htmlspecialchars($t['url']) ?>" class="text-decoration-none">
                                    <div class="border rounded p-3 h-100 d-flex gap-3 align-items-start">
                                        <i class="fas <?= htmlspecialchars($t['icon']) ?> fa-lg text-primary mt-1"></i>
                                        <div>
                                            <strong><?= htmlspecialchars($t['name']) ?></strong><br>
                                            <small class="text-muted"><?= htmlspecialchars($t['desc']) ?></small>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white"><h6 class="mb-0"><i class="fas fa-terminal me-2"></i>Latest Error Log (tail)</h6></div>
                <div class="card-body p-0">
                    <?php if (empty($logTail)): ?>
                        <p class="text-muted text-center py-4">Log file not readable from web user.</p>
                    <?php else: ?>
                        <pre class="small bg-dark text-light p-3 mb-0" style="max-height:420px;overflow:auto;white-space:pre-wrap;"><?php foreach ($logTail as $ln): ?><?= htmlspecialchars(mb_convert_encoding($ln, 'UTF-8', 'UTF-8')) . "\n" ?><?php endforeach; ?></pre>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
