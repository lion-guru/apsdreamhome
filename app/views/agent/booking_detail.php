<?php
$page_title = $page_title ?? 'Booking Detail';
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
            <p><strong>Booking No:</strong> <?= htmlspecialchars($item['booking_number'] ?? '') ?></p>
            <p><strong>Plot:</strong> <?= htmlspecialchars($item['plot_number'] ?? '') ?></p>
            <p><strong>Colony:</strong> <?= htmlspecialchars($item['colony_name'] ?? '') ?></p>
            <p><strong>Customer:</strong> <?= htmlspecialchars($item['customer_name'] ?? '') ?></p>
            <p><strong>Phone:</strong> <?= htmlspecialchars($item['customer_phone'] ?? '') ?></p>
            <p><strong>Total Value:</strong> <?= htmlspecialchars((string)($item['total_plot_value'] ?? '')) ?></p>
            <p><strong>Booking Amount:</strong> <?= htmlspecialchars((string)($item['booking_amount'] ?? '')) ?></p>
            <p><strong>Status:</strong> <?= htmlspecialchars($item['status'] ?? '') ?></p>
            <p><strong>Date:</strong> <?= htmlspecialchars($item['booking_date'] ?? '') ?></p>
            <p><strong>Notes:</strong> <?= htmlspecialchars($item['notes'] ?? '') ?></p>
        </div>
    </div>
    <a href="/agent/bookings" class="btn btn-secondary mt-3">Back</a>
</div>
</body>
</html>
