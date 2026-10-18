<?php
$page_title = $page_title ?? 'Site Visit Detail';
$item = $item ?? [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?> - APS Dream Home</title>
    <link href="<?= BASE_URL ?>/assets/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
    <h1 class="h4 mb-3"><?= htmlspecialchars($page_title) ?></h1>
    <div class="card">
        <div class="card-body">
            <p><strong>Plot:</strong> <?= htmlspecialchars($item['plot_number'] ?? '') ?></p>
            <p><strong>Colony:</strong> <?= htmlspecialchars($item['colony_name'] ?? '') ?></p>
            <p><strong>Visitor:</strong> <?= htmlspecialchars($item['visitor_name'] ?? '') ?></p>
            <p><strong>Phone:</strong> <?= htmlspecialchars($item['visitor_phone'] ?? '') ?></p>
            <p><strong>Date:</strong> <?= htmlspecialchars($item['visit_date'] ?? '') ?> <?= htmlspecialchars($item['visit_time'] ?? '') ?></p>
            <p><strong>Status:</strong> <?= htmlspecialchars($item['status'] ?? '') ?></p>
            <p><strong>Notes:</strong> <?= htmlspecialchars($item['notes'] ?? '') ?></p>
            <p><strong>Feedback:</strong> <?= htmlspecialchars($item['feedback'] ?? '') ?></p>
        </div>
    </div>
    <a href="/agent/site-visits" class="btn btn-secondary mt-3">Back</a>
</div>
</body>
</html>
