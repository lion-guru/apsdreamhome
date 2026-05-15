<?php
$developer = $developer ?? null;
$status = $status ?? [];
?>
<div class="container py-4">
    <h1>Senior Developer Dashboard</h1>
    <div class="card shadow mt-3">
        <div class="card-header bg-dark text-white"><h5 class="mb-0">System Status Overview</h5></div>
        <div class="card-body">
            <p class="text-muted">Developer dashboard — system monitoring and diagnostic tools.</p>
            <?php if (!empty($status)): ?>
            <pre><?= htmlspecialchars(print_r($status, true)) ?></pre>
            <?php endif; ?>
        </div>
    </div>
</div>
