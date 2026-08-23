<?php
/** Shared sidebar progress indicator for the property listing wizard. */
$progress = $progress ?? 12;
$stepNum = $step_num ?? 1;
$steps = [
    1 => ['label' => 'Basics', 'icon' => 'fa-info-circle'],
    2 => ['label' => 'Location', 'icon' => 'fa-map-marker-alt'],
    3 => ['label' => 'Dimensions', 'icon' => 'fa-ruler-combined'],
    4 => ['label' => 'Pricing', 'icon' => 'fa-rupee-sign'],
    5 => ['label' => 'Amenities', 'icon' => 'fa-concierge-bell'],
    6 => ['label' => 'Images', 'icon' => 'fa-images'],
    7 => ['label' => 'Review', 'icon' => 'fa-clipboard-check'],
    8 => ['label' => 'Contact', 'icon' => 'fa-user-check'],
];
?>
<aside class="wizard-sidebar card border-0 shadow-sm mb-3">
    <div class="card-body p-3">
        <h6 class="fw-bold mb-3"><i class="fas fa-list-ul me-1"></i> Listing Progress</h6>
        <div class="progress mb-3 style-29939">
            <div class="progress-bar bg-success style-42872"></div>
        </div>
        <ul class="list-unstyled mb-0">
            <?php foreach ($steps as $num => $info): ?>
                <?php
                $isDone = $num < $stepNum;
                $isActive = $num === $stepNum;
                $cls = $isActive ? 'text-primary fw-bold' : ($isDone ? 'text-success' : 'text-muted');
                $icon = $isDone ? 'fa-check-circle' : $info['icon'];
                ?>
                <li class="d-flex align-items-center py-1 <?= $cls ?>">
                    <i class="fas <?= $icon ?> me-2"></i>
                    <span class="small"><?= $num ?>. <?= htmlspecialchars($info['label'] ?? '') ?></span>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</aside>
