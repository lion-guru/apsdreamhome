<?php

/**
 * Admin EMI Index View
 */
?>

<div class="admin-header py-4 bg-primary text-white">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h1 class="mb-0">
                    <i class="fas fa-money-bill-wave me-2"></i>
                    EMI Plans Management
                </h1>
                <p class="mb-0 opacity-75">Track and manage property EMI schedules.</p>
            </div>
            <div class="col-lg-6 text-lg-end">
                <button class="btn btn-light" data-bs-toggle="modal" data-bs-target="#addEMIPlanModal">
                    <i class="fas fa-plus me-2"></i>New EMI Plan
                </button>
            </div>
        </div>
    </div>
</div>

<section class="py-5">
    <div class="container">
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Plan ID</th>
                                <th>Customer</th>
                                <th>Total Amount</th>
                                <th>EMI Amount</th>
                                <th>Tenure</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (isset($plans) && !empty($plans)): ?>
                                <?php foreach ($plans as $plan): ?>
                                    <tr>
                                        <td>#EMI-<?php echo $plan['id']; ?></td>
                                        <td>
                                            <div class="fw-bold"><?php echo htmlspecialchars($plan['customer_name']); ?></div>
                                            <small class="text-muted">Booking Date: <?php echo date('d M Y', strtotime($plan['booking_date'])); ?></small>
                                        </td>
                                        <td>₹<?php echo number_format($plan['total_amount'], 2); ?></td>
                                        <td>₹<?php echo number_format($plan['emi_amount'], 2); ?></td>
                                        <td><?php echo $plan['tenure_months']; ?> Months</td>
                                        <td>
                                            <span class="badge bg-<?php echo $plan['status'] === 'active' ? 'success' : ($plan['status'] === 'completed' ? 'primary' : 'warning'); ?>">
                                                <?php echo ucfirst($plan['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="<?php echo BASE_URL; ?>admin/emi/show/<?php echo $plan['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye"></i> View Details
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">No EMI plans found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>
