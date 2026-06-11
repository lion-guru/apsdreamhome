<?php $pageTitle = $page_title ?? 'Edge Computing Case Studies'; ?>
<div class="container-fluid py-4">
    <h4 class="mb-4"><i class="fas fa-book-open me-2"></i><?= htmlspecialchars($pageTitle) ?></h4>
    <div class="row g-3">
        <?php foreach (($case_studies ?? []) as $key => $cs): ?>
            <div class="col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body aps-cp-card-body">
                        <h5 class="card-title"><?= htmlspecialchars($cs['title'] ?? ucfirst(str_replace('_', ' ', $key))) ?></h5>
                        <span class="badge bg-info mb-2"><?= htmlspecialchars($cs['industry'] ?? '') ?></span>
                        <p class="small"><strong>Challenge:</strong> <?= htmlspecialchars($cs['challenge'] ?? '') ?></p>
                        <p class="small"><strong>Solution:</strong> <?= htmlspecialchars($cs['solution'] ?? '') ?></p>
                        <p class="fw-bold mb-1">Results:</p>
                        <ul class="list-unstyled small">
                            <?php foreach (($cs['results'] ?? []) as $res): ?>
                                <li><i class="fas fa-check text-success me-1"></i><?= htmlspecialchars($res) ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <span class="badge bg-dark me-1"><?= htmlspecialchars($cs['implementation_time'] ?? '') ?></span>
                        <span class="badge bg-success">ROI: <?= htmlspecialchars($cs['roi_achieved'] ?? '') ?></span>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
