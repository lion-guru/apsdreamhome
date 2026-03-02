<?php
/**
 * User Enquiries View - APS Dream Home
 */
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h3 mb-0 text-gray-800 fw-bold">My Enquiries</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>dashboard" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item active">Enquiries</li>
            </ol>
        </nav>
    </div>

    <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Date</th>
                            <th>Property</th>
                            <th>Subject</th>
                            <th>Message</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($enquiries)): ?>
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    <i class="fas fa-envelope-open-text fa-3x mb-3 d-block"></i>
                                    No enquiries found.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($enquiries as $inquiry): ?>
                                <tr>
                                    <td class="ps-4 text-muted small">
                                        <?= date('d M, Y', strtotime($inquiry['created_at'])) ?>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark"><?= h($inquiry['property_title'] ?? 'N/A') ?></div>
                                        <div class="text-muted small"><?= h($inquiry['city'] . ', ' . $inquiry['state']) ?></div>
                                    </td>
                                    <td><?= h($inquiry['subject']) ?></td>
                                    <td>
                                        <div class="text-truncate" style="max-width: 250px;" title="<?= h($inquiry['message']) ?>">
                                            <?= h($inquiry['message']) ?>
                                        </div>
                                    </td>
                                    <td>
                                        <?php 
                                        $statusClass = 'bg-secondary';
                                        if ($inquiry['status'] == 'new') $statusClass = 'bg-info';
                                        elseif ($inquiry['status'] == 'responded') $statusClass = 'bg-success';
                                        elseif ($inquiry['status'] == 'closed') $statusClass = 'bg-dark';
                                        ?>
                                        <span class="badge <?= $statusClass ?> rounded-pill px-3">
                                            <?= ucfirst($inquiry['status']) ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
