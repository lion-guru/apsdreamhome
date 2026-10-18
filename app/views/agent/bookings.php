<?php
$page_title = $page_title ?? 'My Bookings';
$items = $items ?? [];
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
    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Booking No</th>
                    <th>Plot</th>
                    <th>Colony</th>
                    <th>Customer</th>
                    <th>Value</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                <tr>
                    <td><?= (int)$item['id'] ?></td>
                    <td><?= htmlspecialchars($item['booking_number'] ?? '') ?></td>
                    <td><?= htmlspecialchars($item['plot_number'] ?? '') ?></td>
                    <td><?= htmlspecialchars($item['colony_name'] ?? '') ?></td>
                    <td><?= htmlspecialchars($item['customer_name'] ?? '') ?></td>
                    <td><?= htmlspecialchars((string)($item['total_plot_value'] ?? '')) ?></td>
                    <td><?= htmlspecialchars($item['status'] ?? '') ?></td>
                    <td><a href="/agent/bookings/<?= (int)$item['id'] ?>" class="btn btn-sm btn-outline-primary">View</a></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <a href="/agent/dashboard" class="btn btn-secondary">Back</a>
</div>
</body>
</html>
