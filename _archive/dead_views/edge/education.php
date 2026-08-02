<?php $pageTitle = $page_title ?? 'Edge Computing Education'; ?>
<div class="container-fluid py-4">
    <h4 class="mb-4"><i class="fas fa-graduation-cap me-2"></i><?= htmlspecialchars($pageTitle) ?></h4>
    <div class="row g-3">
        <?php foreach (($training_programs ?? []) as $key => $prog): ?>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body aps-cp-card-body">
                        <h5 class="card-title"><?= htmlspecialchars($prog['title'] ?? ucfirst(str_replace('_', ' ', $key))) ?></h5>
                        <span class="badge bg-<?= ($prog['level'] ?? 'Beginner') === 'Advanced' ? 'danger' : (($prog['level'] ?? 'Beginner') === 'Intermediate' ? 'warning' : 'success') ?> mb-2"><?= htmlspecialchars($prog['level'] ?? 'Beginner') ?></span>
                        <p class="small"><strong>Duration:</strong> <?= htmlspecialchars($prog['duration'] ?? '') ?></p>
                        <p class="fw-bold mb-1">Topics:</p>
                        <ul class="list-unstyled small">
                            <?php foreach (($prog['topics'] ?? []) as $t): ?>
                                <li><i class="fas fa-book text-primary me-1"></i><?= htmlspecialchars($t) ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <span class="badge bg-dark"><?= htmlspecialchars($prog['certification'] ?? '') ?></span>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
