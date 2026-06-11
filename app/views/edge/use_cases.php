<?php $pageTitle = $page_title ?? 'Edge Computing Use Cases'; ?>
<div class="container-fluid py-4">
    <h4 class="mb-4"><i class="fas fa-briefcase me-2"></i><?= htmlspecialchars($pageTitle) ?></h4>
    <div class="row g-3">
        <?php foreach (($use_cases ?? []) as $key => $uc): ?>
            <div class="col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body aps-cp-card-body">
                        <h5 class="card-title"><?= htmlspecialchars($uc['title'] ?? ucfirst(str_replace('_', ' ', $key))) ?></h5>
                        <p class="card-text"><?= htmlspecialchars($uc['description'] ?? '') ?></p>
                        <p class="fw-bold mb-1">Benefits:</p>
                        <ul class="list-unstyled small">
                            <?php foreach (($uc['benefits'] ?? []) as $b): ?>
                                <li><i class="fas fa-check text-success me-1"></i><?= htmlspecialchars($b) ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <span class="badge bg-<?= ($uc['implementation_complexity'] ?? 'Medium') === 'High' ? 'danger' : (($uc['implementation_complexity'] ?? 'Medium') === 'Medium' ? 'warning' : 'success') ?>"><?= htmlspecialchars($uc['implementation_complexity'] ?? 'Medium') ?></span>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
