<?php
$page_title = $page_title ?? __('user_agreements_page_title', null, 'My Agreements');
$current_page = 'agreements';
$agreements = $agreements ?? [];
$user = $user ?? [];

$statusColors = [
    'draft' => 'secondary',
    'pending_signature' => 'warning',
    'signed' => 'success',
    'registered' => 'info',
    'cancelled' => 'danger',
    'expired' => 'dark',
];
$statusLabels = [
    'draft' => __('user_agreements_status_draft', null, 'Draft'),
    'pending_signature' => __('user_agreements_status_pending', null, 'Pending Signature'),
    'signed' => __('user_agreements_status_signed', null, 'Signed'),
    'registered' => __('user_agreements_status_registered', null, 'Registered'),
    'cancelled' => __('user_agreements_status_cancelled', null, 'Cancelled'),
    'expired' => __('user_agreements_status_expired', null, 'Expired'),
];
$typeLabels = [
    'sale_deed' => __('user_agreements_type_sale_deed', null, 'Sale Deed'),
    'allotment' => __('user_agreements_type_allotment', null, 'Allotment Letter'),
    'mortgage' => __('user_agreements_type_mortgage', null, 'Mortgage'),
    'lease' => __('user_agreements_type_lease', null, 'Lease Agreement'),
    'nda' => __('user_agreements_type_nda', null, 'NDA'),
    'joint_venture' => __('user_agreements_type_joint_venture', null, 'Joint Venture'),
    'other' => __('user_agreements_type_other', null, 'Other'),
];
?>

<div class="aps-cp-hero">
    <div class="row align-items-center">
        <div class="col-md-8">
            <h2><i class="fas fa-file-signature me-2"></i><?= __('user_agreements_heading', null, 'My Agreements') ?></h2>
            <p><?= __('user_agreements_subtitle', null, 'Review, sign, and download your property agreements.') ?></p>
        </div>
        <div class="col-md-4 mt-3 mt-md-0 text-md-end">
            <a href="<?= BASE_URL ?>/user/bookings" class="btn btn-light">
                <i class="fas fa-arrow-left me-2"></i><?= __('user_agreements_back_bookings', null, 'Back to Bookings') ?>
            </a>
        </div>
    </div>
</div>

<?php if (empty($agreements)): ?>
    <div class="aps-cp-card">
        <div class="aps-cp-card-body">
            <div class="aps-cp-empty">
                <div class="aps-cp-empty-icon"><i class="fas fa-file-signature"></i></div>
                <h5><?= __('user_agreements_empty_heading', null, 'No agreements yet') ?></h5>
                <p><?= __('user_agreements_empty_desc', null, 'Agreements are generated automatically when you book a plot. Book your dream plot to get started.') ?></p>
                <a href="<?= BASE_URL ?>/properties" class="btn btn-primary">
                    <i class="fas fa-search me-2"></i><?= __('user_agreements_browse_properties', null, 'Browse Properties') ?>
                </a>
            </div>
        </div>
    </div>
<?php else: ?>
    <?php
    $pendingCount = 0;
    $signedCount = 0;
    foreach ($agreements as $ag) {
        if (($ag['status'] ?? '') === 'pending_signature') $pendingCount++;
        if (($ag['status'] ?? '') === 'signed') $signedCount++;
    }
    ?>
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="aps-cp-stat" class="style-91500">
                <div class="stat-value"><?= count($agreements) ?></div>
                <div class="stat-label"><?= __('user_agreements_total', null, 'Total Agreements') ?></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="aps-cp-stat" class="style-11277">
                <div class="stat-value"><?= $pendingCount ?></div>
                <div class="stat-label"><?= __('user_agreements_pending_count', null, 'Pending Signature') ?></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="aps-cp-stat" class="style-99864">
                <div class="stat-value"><?= $signedCount ?></div>
                <div class="stat-label"><?= __('user_agreements_signed_count', null, 'Signed') ?></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="aps-cp-stat" class="style-50064">
                <div class="stat-value"><?= count($agreements) - $pendingCount - $signedCount ?></div>
                <div class="stat-label"><?= __('user_agreements_other_count', null, 'Other') ?></div>
            </div>
        </div>
    </div>

    <div class="aps-cp-card">
        <div class="aps-cp-card-header">
            <h5 class="mb-0"><i class="fas fa-list me-2"></i><?= __('user_agreements_list_heading', null, 'Agreement List') ?></h5>
        </div>
        <div class="aps-cp-card-body p-0">
            <div class="table-responsive">
                <table class="aps-cp-table mb-0">
                    <thead>
                        <tr>
                            <th><?= __('user_agreements_th_number', null, 'Agreement No') ?></th>
                            <th><?= __('user_agreements_th_type', null, 'Type') ?></th>
                            <th><?= __('user_agreements_th_plot', null, 'Plot / Colony') ?></th>
                            <th><?= __('user_agreements_th_value', null, 'Value') ?></th>
                            <th><?= __('user_agreements_th_status', null, 'Status') ?></th>
                            <th><?= __('user_agreements_th_date', null, 'Date') ?></th>
                            <th><?= __('user_agreements_th_actions', null, 'Actions') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($agreements as $ag): ?>
                            <?php
                            $status = $ag['status'] ?? 'draft';
                            $color = $statusColors[$status] ?? 'secondary';
                            $label = $statusLabels[$status] ?? ucfirst(str_replace('_', ' ', $status));
                            $type = $typeLabels[$ag['agreement_type'] ?? ''] ?? ucfirst(str_replace('_', ' ', $ag['agreement_type'] ?? 'other'));
                            ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($ag['agreement_number'] ?? 'AGR-' . str_pad($ag['id'], 4, '0', STR_PAD_LEFT)) ?></strong>
                                </td>
                                <td><?= $type ?></td>
                                <td>
                                    <span><?= htmlspecialchars($ag['plot_number'] ?? 'N/A') ?></span>
                                    <?php if (!empty($ag['block'])): ?>
                                        <small class="text-muted">(<?= htmlspecialchars($ag['block']) ?>)</small>
                                    <?php endif; ?>
                                    <br><small class="text-muted"><?= htmlspecialchars($ag['colony_name'] ?? '') ?></small>
                                </td>
                                <td>₹<?= number_format((float)($ag['total_value'] ?? $ag['total_plot_value'] ?? 0)) ?></td>
                                <td><span class="aps-cp-badge aps-cp-badge-<?= $color ?>"><?= $label ?></span></td>
                                <td><?= date('d M Y', strtotime($ag['created_at'] ?? 'now')) ?></td>
                                <td>
                                    <a href="<?= BASE_URL ?>/user/agreements/<?= $ag['id'] ?>" class="btn btn-sm btn-outline-primary" title="<?= __('user_agreements_view_details', null, 'View Details') ?>">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <?php if ($status === 'pending_signature'): ?>
                                        <a href="<?= BASE_URL ?>/user/agreements/<?= $ag['id'] ?>" class="btn btn-sm btn-success" title="<?= __('user_agreements_sign_now', null, 'Sign Now') ?>">
                                            <i class="fas fa-pen"></i>
                                        </a>
                                    <?php endif; ?>
                                    <?php if ($status === 'signed' || $status === 'registered'): ?>
                                        <a href="<?= BASE_URL ?>/user/agreements/<?= $ag['id'] ?>/preview" class="btn btn-sm btn-outline-secondary" title="<?= __('user_agreements_preview', null, 'Preview') ?>" target="_blank">
                                            <i class="fas fa-print"></i>
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php endif; ?>
