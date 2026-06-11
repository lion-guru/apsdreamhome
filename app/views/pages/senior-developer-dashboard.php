<?php
$developer = $developer ?? null;
$status = $status ?? [];
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Senior Developer Dashboard - APS Dream Home</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container py-4">
    <h1>Senior Developer Dashboard</h1>
    <div class="card shadow mt-3">
        <div class="card-header bg-dark text-white"><h5 class="mb-0">System Status Overview</h5></div>
        <div class="card-body aps-cp-card-body">
            <p class="text-muted">Developer dashboard — system monitoring and diagnostic tools.</p>
            <?php if (!empty($status)): ?>
            <pre><?= htmlspecialchars(print_r($status, true)) ?></pre>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>
