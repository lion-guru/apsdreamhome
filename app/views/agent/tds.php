<?php
$page_title = $page_title ?? 'My TDS';
$items = $items ?? [];
$summary = $summary ?? ['entries' => 0, 'gross' => 0, 'tds' => 0, 'total' => 0];
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
    <div class="row mb-3">
        <div class="col-md-3"><div class="card"><div class="card-body"><strong>Entries</strong><div><?= (int)$summary['entries'] ?></div></div></div></div>
        <div class="col-md-3"><div class="card"><div class="card-body"><strong>Gross</strong><div><?= htmlspecialchars((string)$summary['gross']) ?></div></div></div></div>
        <div class="col-md-3"><div class="card"><div class="card-body"><strong>TDS</strong><div><?= htmlspecialchars((string)$summary['tds']) ?></div></div></div></div>
        <div class="col-md-3"><div class="card"><div class="card-body"><strong>Total TDS</strong><div><?= htmlspecialchars((string)$summary['total']) ?></div></div></div></div>
    </div>
    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Section</th>
                    <th>Date</th>
                    <th>Gross</th>
                    <th>Rate</th>
                    <th>TDS</th>
                    <th>Status</th>
                    <th>Reference</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                <tr>
                    <td><?= (int)$item['id'] ?></td>
                    <td><?= htmlspecialchars($item['tds_section'] ?? '') ?></td>
                    <td><?= htmlspecialchars($item['transaction_date'] ?? '') ?></td>
                    <td><?= htmlspecialchars((string)($item['gross_amount'] ?? '')) ?></td>
                    <td><?= htmlspecialchars((string)($item['tds_rate'] ?? '')) ?></td>
                    <td><?= htmlspecialchars((string)($item['tds_amount'] ?? '')) ?></td>
                    <td><?= htmlspecialchars($item['status'] ?? '') ?></td>
                    <td><?= htmlspecialchars($item['transaction_ref'] ?? '') ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <a href="/agent/dashboard" class="btn btn-secondary">Back</a>
</div>
</body>
</html>
