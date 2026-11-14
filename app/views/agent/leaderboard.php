<?php
$page_title = $page_title ?? 'Agent Leaderboard';
$rows = $rows ?? [];
$currentUserId = $currentUserId ?? 0;
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
                    <th scope="col">Rank</th>
                    <th scope="col">Agent</th>
                    <th scope="col">Referral Code</th>
                    <th scope="col">Bookings</th>
                    <th scope="col">Revenue</th>
                    <th scope="col">Commission</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $row): ?>
                <tr class="<?= (int)$row['id'] === (int)$currentUserId ? 'table-primary' : '' ?>">
                    <td><?= (int)$row['rank'] ?></td>
                    <td><?= htmlspecialchars($row['name'] ?? '') ?></td>
                    <td><?= htmlspecialchars($row['referral_code'] ?? '') ?></td>
                    <td><?= (int)$row['bookings'] ?></td>
                    <td><?= htmlspecialchars((string)$row['revenue']) ?></td>
                    <td><?= htmlspecialchars((string)$row['commission']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <a href="/agent/dashboard" class="btn btn-secondary">Back</a>
</div>
</body>
</html>
