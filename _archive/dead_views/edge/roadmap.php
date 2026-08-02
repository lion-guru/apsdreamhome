<?php $pageTitle = $page_title ?? 'Edge Computing Roadmap'; ?>
<div class="container-fluid py-4">
    <h4 class="mb-4"><i class="fas fa-road me-2"></i><?= htmlspecialchars($pageTitle) ?></h4>
    <div class="row g-3">
        <?php foreach (($roadmap_data ?? []) as $year => $quarters): ?>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-transparent"><h5 class="mb-0"><?= htmlspecialchars($year) ?></h5></div>
                    <div class="card-body aps-cp-card-body">
                        <ul class="list-unstyled mb-0">
                            <?php foreach ($quarters as $q => $item): ?>
                                <li class="mb-2"><span class="badge bg-primary me-2"><?= strtoupper(htmlspecialchars($q)) ?></span><?= htmlspecialchars($item) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
