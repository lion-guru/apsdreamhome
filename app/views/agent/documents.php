<?php
$page_title = $page_title ?? 'My Documents';
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
                    <th>Type</th>
                    <th>Number</th>
                    <th>Status</th>
                    <th>Issued</th>
                    <th>Expiry</th>
                    <th>File</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                <tr>
                    <td><?= (int)$item['id'] ?></td>
                    <td><?= htmlspecialchars($item['document_type'] ?? $item['type'] ?? '') ?></td>
                    <td><?= htmlspecialchars($item['document_number'] ?? '') ?></td>
                    <td><?= htmlspecialchars($item['verification_status'] ?? '') ?></td>
                    <td><?= htmlspecialchars($item['issue_date'] ?? '') ?></td>
                    <td><?= htmlspecialchars($item['expiry_date'] ?? '') ?></td>
                    <td><?php if (!empty($item['url'])): ?><a href="<?= htmlspecialchars($item['url']) ?>" target="_blank">Open</a><?php endif; ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <a href="/agent/dashboard" class="btn btn-secondary">Back</a>
</div>
</body>
</html>
