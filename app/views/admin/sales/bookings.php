<?php
/** @var array $bookings */
/** @var array $pagination */
/** @var array $filters */
/** @var array $statuses */
$bookings = $bookings ?? [];
$filters = $filters ?? [];
$statuses = $statuses ?? [];
$csrf_token = $csrf_token ?? '';
$base = defined('BASE_URL') ? BASE_URL : '';
$statusBadge = function ($s) {
    $map = [
        'token_paid'        => 'bg-info',
        'agreement_signed'  => 'bg-primary',
        'emi_active'        => 'bg-warning text-dark',
        'partially_paid'    => 'bg-warning text-dark',
        'fully_paid'        => 'bg-success',
        'cancelled'         => 'bg-danger',
        'transferred'       => 'bg-secondary',
        'registration_done' => 'bg-success',
    ];
    return $map[$s] ?? 'bg-secondary';
};
?>
<div class="aps-cp-card mb-3">
    <div class="aps-cp-card-header">
        <h5 class="m-0"><i class="fas fa-bookmark me-2"></i><?= __('sale_plot_bookings') ?></h5>
    </div>
    <div class="aps-cp-card-body">
        <form method="get" class="row g-2 mb-3">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars((string)$csrf_token) ?>">
            <div class="col-md-3">
                <input type="text" name="search" value="<?= htmlspecialchars((string)($filters['search'] ?? '')) ?>" placeholder="<?= __('sale_search_placeholder') ?>" class="form-control form-control-sm">
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select form-select-sm">
                    <option value=""><?= __('sale_all_statuses') ?></option>
                    <?php foreach ($statuses as $st): ?>
                        <option value="<?= htmlspecialchars($st) ?>" <?= (($filters['status'] ?? '') === $st) ? 'selected' : '' ?>><?= htmlspecialchars($st) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <input type="date" name="date_from" value="<?= htmlspecialchars((string)($filters['date_from'] ?? '')) ?>" class="form-control form-control-sm">
            </div>
            <div class="col-md-2">
                <input type="date" name="date_to" value="<?= htmlspecialchars((string)($filters['date_to'] ?? '')) ?>" class="form-control form-control-sm">
            </div>
            <div class="col-md-3">
                <button class="btn btn-sm btn-primary" type="submit"><i class="fas fa-search me-1"></i><?= __('sale_filter') ?></button>
                <a href="<?= htmlspecialchars($base) ?>/admin/sales/bookings" class="btn btn-sm btn-link"><?= __('sale_reset') ?></a>
                <a href="<?= htmlspecialchars($base) ?>/admin/sales/bookings/new" class="btn btn-sm btn-success float-end"><i class="fas fa-plus me-1"></i><?= __('sale_new') ?></a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover table-sm">
                <thead>
                    <tr>
                        <th><?= __('sale_booking_num') ?></th>
                        <th><?= __('sale_customer') ?></th>
                        <th><?= __('sale_plot') ?></th>
                        <th><?= __('sale_channel') ?></th>
                        <th><?= __('sale_status') ?></th>
                        <th class="text-end"><?= __('sale_agreement') ?></th>
                        <th class="text-end"><?= __('sale_paid') ?></th>
                        <th><?= __('sale_booking_date') ?></th>
                        <th>Approval</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($bookings)): ?>
                        <tr><td colspan="9" class="text-center py-4 text-muted"><?= __('sale_no_bookings') ?></td></tr>
                    <?php else: foreach ($bookings as $b): ?>
                        <tr>
                            <td>
                                <a href="<?= htmlspecialchars($base) ?>/admin/sales/bookings/<?= (int)($b['id'] ?? 0) ?>" class="fw-bold">
                                    <?= htmlspecialchars((string)($b['booking_number'] ?? '')) ?>
                                </a>
                            </td>
                            <td><?= htmlspecialchars((string)($b['customer_name'] ?? '—')) ?></td>
                            <td><?= htmlspecialchars((string)($b['plot_code'] ?? '—')) ?></td>
                            <td><span class="badge bg-light text-dark"><?= htmlspecialchars((string)($b['channel'] ?? '')) ?></span></td>
                            <td><span class="badge <?= $statusBadge($b['status'] ?? '') ?>"><?= htmlspecialchars((string)($b['status'] ?? '')) ?></span></td>
                            <td class="text-end">&#8377;<?= number_format((float)($b['agreement_value'] ?? 0)) ?></td>
                            <td class="text-end text-success">&#8377;<?= number_format((float)($b['amount_paid'] ?? 0)) ?></td>
                                <td><?= htmlspecialchars((string)($b['booking_date'] ?? '')) ?></td>
                                <td>
                                    <?php
                                    $apStatus = $b['approval_status'] ?? null;
                                    $apBadge = match($apStatus) {
                                        'approved' => 'bg-success',
                                        'rejected' => 'bg-danger',
                                        'pending' => 'bg-warning text-dark',
                                        default => 'bg-light text-muted',
                                    };
                                    $apLabel = $apStatus ? ucfirst($apStatus) : '—';
                                    ?>
                                    <span class="badge <?= $apBadge ?>"><?= $apLabel ?></span>
                                </td>
                            </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>

        <?php if (($pagination['pages'] ?? 1) > 1): ?>
            <nav>
                <ul class="pagination pagination-sm justify-content-center">
                    <?php for ($p = 1; $p <= (int)($pagination['pages'] ?? 1); $p++): ?>
                        <li class="page-item <?= ($p === (int)($pagination['page'] ?? 1)) ? 'active' : '' ?>">
                            <a class="page-link" href="?<?= http_build_query(array_merge($filters, ['page' => $p])) ?>"><?= $p ?></a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        <?php endif; ?>
    </div>
</div>
