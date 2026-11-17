<?php $pageTitle = $page_title ?? 'Smart Home Troubleshooting'; ?>
<div class="container-fluid py-4">
    <h4 class="mb-4"><i class="fas fa-life-ring me-2"></i><?= htmlspecialchars($pageTitle ?? '') ?></h4>
    <?php foreach (($troubleshooting_guide ?? []) as $key => $section): ?>
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-transparent"><h5 class="mb-0"><?= htmlspecialchars($section['title'] ?? ucfirst(str_replace('_', ' ', $key))) ?></h5></div>
            <div class="card-body aps-cp-card-body">
                <p class="fw-bold mb-2">Symptoms:</p>
                <ul class="list-unstyled mb-3">
                    <?php foreach (($section['symptoms'] ?? []) as $s): ?>
                        <li><i class="fas fa-exclamation-circle text-warning me-1"></i><?= htmlspecialchars($s ?? '') ?></li>
                    <?php endforeach; ?>
                </ul>
                <p class="fw-bold mb-2">Solutions:</p>
                <ol class="mb-0">
                    <?php foreach (($section['solutions'] ?? []) as $s): ?>
                        <li class="mb-1"><?= htmlspecialchars($s ?? '') ?></li>
                    <?php endforeach; ?>
                </ol>
            </div>
        </div>
    <?php endforeach; ?>
</div>
