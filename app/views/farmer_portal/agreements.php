<?php
$page_title = 'My Agreements - APS Dream Home';
$current_page = 'farmer-agreements';
$extraHead = '<style>.badge-status { font-size:0.8rem; padding:0.35rem 0.75rem; }</style>';
$agreements = $agreements ?? [];
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="fas fa-file-contract text-info me-2"></i>My Agreements</h4>
        <a href="<?php echo BASE_URL; ?>/farmer/dashboard" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i>Back to Dashboard
        </a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <?php if (empty($agreements)): ?>
            <div class="text-center py-5 text-muted">
                <i class="fas fa-file-signature fa-4x mb-3"></i>
                <p>No agreements found.</p>
            </div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th>Type</th>
                            <th>Amount</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($agreements as $a): ?>
                        <?php
                        $aStatus = $a['status'] ?? 'draft';
                        $statusBadge = match($aStatus) {
                            'active' => 'success',
                            'completed' => 'primary',
                            'terminated', 'cancelled' => 'danger',
                            'draft' => 'secondary',
                            default => 'secondary'
                        };
                        ?>
                        <tr>
                            <td><span class="badge bg-info"><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $a['agreement_type'] ?? 'N/A'))); ?></span></td>
                            <td><strong>₹<?php echo number_format($a['amount'] ?? 0); ?></strong></td>
                            <td><?php echo $a['start_date'] ? date('d M Y', strtotime($a['start_date'])) : 'N/A'; ?></td>
                            <td><?php echo $a['end_date'] ? date('d M Y', strtotime($a['end_date'])) : 'N/A'; ?></td>
                            <td><span class="badge bg-<?php echo e($statusBadge); ?> badge-status"><?php echo ucfirst($aStatus); ?></span></td>
                            <td>
                                <a href="<?php echo BASE_URL; ?>/farmer/agreements/download/<?php echo e($a['id']); ?>" class="btn btn-sm btn-outline-primary" target="_blank">
                                    <i class="fas fa-download me-1"></i>Download
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
