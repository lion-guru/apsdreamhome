<?php
/**
 * User Investments View - APS Dream Home
 */
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h3 mb-0 text-gray-800 fw-bold"><?= __('user_inv_title', null, 'My Investments') ?></h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/dashboard" class="text-decoration-none"><?= __('user_inv_breadcrumb_dashboard', null, 'Dashboard') ?></a></li>
                <li class="breadcrumb-item active"><?= __('user_inv_breadcrumb_investments', null, 'Investments') ?></li>
            </ol>
        </nav>
    </div>

    <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
        <div class="card-body p-0">
            <div class="table-responsive">
                <div class="table-responsive"><table class="table table-hover align-middle mb-0 table-responsive">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4"><?= __('user_inv_th_plot', null, 'Plot Number') ?></th>
                            <th><?= __('user_inv_th_project', null, 'Project/Site') ?></th>
                            <th><?= __('user_inv_th_area', null, 'Area') ?></th>
                            <th><?= __('user_inv_th_total_price', null, 'Total Price') ?></th>
                            <th><?= __('user_inv_th_status', null, 'Status') ?></th>
                            <th><?= __('user_inv_th_updated', null, 'Last Updated') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($investments)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="fas fa-chart-line fa-3x mb-3 d-block"></i>
                                    <?= __('user_inv_empty', null, 'No investments found.') ?>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($investments as $inv): ?>
                                <tr>
                                    <td class="ps-4 fw-bold"><?= h($inv['plot_number']) ?></td>
                                    <td>
                                        <div class="fw-bold text-dark"><?= h($inv['site_name'] ?? 'N/A') ?></div>
                                        <div class="text-muted small"><?= h($inv['site_location'] ?? 'N/A') ?></div>
                                    </td>
                                    <td><?= h($inv['plot_area'] . ' ' . $inv['plot_area_unit']) ?></td>
                                    <td class="fw-bold text-success">₹<?= number_format($inv['total_price'] ?? 0, 2) ?></td>
                                    <td>
                                        <?php 
                                        $statusClass = 'bg-secondary';
                                        $status = $inv['plot_status'] ?? $inv['status'] ?? 'pending';
                                        if ($status == 'active' || $status == 'booked') $statusClass = 'bg-success';
                                        elseif ($status == 'pending') $statusClass = 'bg-warning';
                                        ?>
                                        <span class="badge <?= htmlspecialchars($statusClass, ENT_QUOTES, 'UTF-8') ?> rounded-pill px-3">
                                            <?= ucfirst($status) ?>
                                        </span>
                                    </td>
                                    <td class="text-muted small">
                                        <?= isset($inv['updated_at']) ? date('d M, Y', strtotime($inv['updated_at'])) : 'N/A' ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table></div>
            </div>
        </div>
    </div>
</div>
