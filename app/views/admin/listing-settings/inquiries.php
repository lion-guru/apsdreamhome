<?php
$inquiries = $inquiries ?? [];
$total = $total ?? 0;
$page = $page ?? 1;
$totalPages = $totalPages ?? 1;
$base = defined('BASE_URL') ? BASE_URL : '/apsdreamhome';
?>
<style>
.inq-table { width: 100%; border-collapse: collapse; color: #f8fafc; }
.inq-table th { background: #0f172a; padding: 10px; text-align: left; font-size: 13px; color: #94a3b8; }
.inq-table td { padding: 10px; border-bottom: 1px solid #334155; font-size: 13px; }
.inq-card { background: #1e293b; border: 1px solid #334155; border-radius: 12px; padding: 20px; }
</style>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="style-43890"><i class="fas fa-inbox me-2"></i>Property Inquiries</h4>
        <span class="style-87977"><?= number_format($total) ?> total inquiries</span>
    </div>

    <div class="inq-card">
        <?php if (empty($inquiries)): ?>
        <p class="style-14163">No inquiries found.</p>
        <?php else: ?>
        <div class="style-6410">
        <table class="inq-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Property</th>
                    <th>Location</th>
                    <th>Name</th>
                    <th>Phone</th>
                    <th>Message</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($inquiries as $i): ?>
                <tr>
                    <td><?= $i['id'] ?></td>
                    <td><?= htmlspecialchars($i['property_name'] ?? 'N/A') ?></td>
                    <td><?= htmlspecialchars($i['property_location'] ?? 'N/A') ?></td>
                    <td><?= htmlspecialchars($i['name'] ?? $i['user_name'] ?? 'Anonymous') ?></td>
                    <td><?= htmlspecialchars($i['phone'] ?? $i['user_phone'] ?? '') ?></td>
                    <td class="style-49903"><?= htmlspecialchars($i['message'] ?? '') ?></td>
                    <td><?= htmlspecialchars($i['created_at'] ?? '') ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        </div>

        <?php if ($totalPages > 1): ?>
        <div class="d-flex justify-content-center mt-3">
            <nav>
                <ul class="pagination pagination-sm">
                    <?php if ($page > 1): ?>
                    <li class="page-item"><a class="page-link" href="?page=<?= $page - 1 ?>" class="style-99722">Prev</a></li>
                    <?php endif; ?>
                    <?php for ($p = max(1, $page - 2); $p <= min($totalPages, $page + 2); $p++): ?>
                    <li class="page-item <?= $p === $page ? 'active' : '' ?>"><a class="page-link" href="?page=<?= $p ?>" class="style-67855"><?= $p ?></a></li>
                    <?php endfor; ?>
                    <?php if ($page < $totalPages): ?>
                    <li class="page-item"><a class="page-link" href="?page=<?= $page + 1 ?>" class="style-99722">Next</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
        <?php endif; ?>
        <?php endif; ?>
    </div>
</div>
