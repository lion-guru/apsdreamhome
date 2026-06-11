<?php $pageTitle = $page_title ?? 'Edge Computing Partnerships'; ?>
<div class="container-fluid py-4">
    <h4 class="mb-4"><i class="fas fa-handshake me-2"></i><?= htmlspecialchars($pageTitle) ?></h4>
    <div class="row g-3">
        <?php foreach (($partners ?? []) as $key => $p): ?>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body aps-cp-card-body">
                        <h5 class="card-title"><?= htmlspecialchars($p['name'] ?? ucfirst(str_replace('_', ' ', $key))) ?></h5>
                        <span class="badge bg-info mb-2"><?= htmlspecialchars($p['type'] ?? '') ?></span>
                        <p class="small mb-1"><strong>Focus:</strong> <?= htmlspecialchars($p['focus'] ?? '') ?></p>
                        <p class="small mb-1"><strong>Since:</strong> <?= htmlspecialchars($p['collaboration_start'] ?? '') ?></p>
                        <p class="fw-bold mb-1 mt-2">Joint Solutions:</p>
                        <ul class="list-unstyled small mb-0">
                            <?php foreach (($p['joint_solutions'] ?? []) as $s): ?>
                                <li><i class="fas fa-check text-success me-1"></i><?= htmlspecialchars($s) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
