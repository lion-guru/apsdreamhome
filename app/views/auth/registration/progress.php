<?php
/** Shared progress bar partial for the registration wizard */
$progress = $progress ?? 25;
$stepNum = $step_num ?? 1;
$stepLabels = [
    1 => ['label' => 'Account', 'icon' => 'fa-user'],
    2 => ['label' => 'Profile', 'icon' => 'fa-id-card'],
    3 => ['label' => 'Preferences', 'icon' => 'fa-sliders-h'],
    4 => ['label' => 'Verify', 'icon' => 'fa-shield-alt'],
];
?>
<div class="wizard-progress mb-4">
    <div class="progress" class="style-74911">
        <div class="progress-bar bg-primary" role="progressbar"
             class="style-77565"
             aria-valuenow="<?= (int)$progress ?>" aria-valuemin="0" aria-valuemax="100"></div>
    </div>
    <div class="d-flex justify-content-between mt-2">
        <?php foreach ($stepLabels as $num => $info): ?>
            <?php
            $isDone = $num < $stepNum;
            $isActive = $num === $stepNum;
            $circleClass = $isActive ? 'bg-primary text-white' : ($isDone ? 'bg-success text-white' : 'bg-light text-muted');
            ?>
            <div class="text-center flex-fill">
                <div class="rounded-circle d-inline-flex align-items-center justify-content-center <?= $circleClass ?>"
                     class="style-71789">
                    <i class="fas <?= $info['icon'] ?>"></i>
                </div>
                <div class="small mt-1 <?= $isActive ? 'fw-bold text-primary' : 'text-muted' ?>">
                    Step <?= $num ?>: <?= htmlspecialchars($info['label'] ?? '') ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
