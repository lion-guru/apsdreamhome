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
        <h4 style="color: #f8fafc;"><i class="fas fa-inbox me-2"></i>Property Inquiries</h4>
        <span style="color: #94a3b8;"><?= number_format($total) ?> total inquiries</span>
    </div>

    <div class="inq-card">
        <?php if (empty($inquiries)): ?>
        <p style="color: #64748b; text-align: center; padding: 40px 0;">No inquiries found.</p>
        <?php else: ?>
        <div style="overflow-x: auto;">
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
                    <td style="max-width: 250px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?= htmlspecialchars($i['message'] ?? '') ?></td>
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
                    <li class="page-item"><a class="page-link" href="?page=<?= $page - 1 ?>" style="background:#1e293b;color:#f8fafc;border-color:#334155;">Prev</a></li>
                    <?php endif; ?>
                    <?php for ($p = max(1, $page - 2); $p <= min($totalPages, $page + 2); $p++): ?>
                    <li class="page-item <?= $p === $page ? 'active' : '' ?>"><a class="page-link" href="?page=<?= $p ?>" style="background:<?= $p === $page ? '#3b82f6' : '#1e293b' ?>;color:#f8fafc;border-color:#334155;"><?= $p ?></a></li>
                    <?php endfor; ?>
                    <?php if ($page < $totalPages): ?>
                    <li class="page-item"><a class="page-link" href="?page=<?= $page + 1 ?>" style="background:#1e293b;color:#f8fafc;border-color:#334155;">Next</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
        <?php endif; ?>
        <?php endif; ?>
    </div>
</div>
