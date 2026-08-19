<?php
$page_title = 'Bookings Management';
$active_page = 'bookings';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><?= __('admin_bookings_management') ?></h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="<?php echo BASE_URL; ?>/admin/bookings/create" class="btn btn-primary">
            <i class="fas fa-plus"></i> <?= __('admin_new_booking') ?>
        </a>
    </div>
</div>

<!-- Flash Messages -->
<?php if (isset($_SESSION['flash_message'])): ?>
    <div class="alert alert-<?= $_SESSION['flash_type'] ?? 'info' ?> alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($_SESSION['flash_message'] ?? '') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['flash_message'], $_SESSION['flash_type']); ?>
<?php endif; ?>

<!-- Stats Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body aps-cp-card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4><?= $total ?></h4>
                        <p class="mb-0"><?= __('admin_total_bookings') ?></p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-calendar fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body aps-cp-card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4><?= count(array_filter($bookings, fn($b) => $b['status'] == 'confirmed')) ?></h4>
                        <p class="mb-0"><?= __('admin_confirmed') ?></p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-check-circle fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body aps-cp-card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4><?= count(array_filter($bookings, fn($b) => $b['status'] == 'pending')) ?></h4>
                        <p class="mb-0"><?= __('admin_pending') ?></p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-clock fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-danger text-white">
            <div class="card-body aps-cp-card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4><?= count(array_filter($bookings, fn($b) => $b['status'] == 'cancelled')) ?></h4>
                        <p class="mb-0"><?= __('admin_cancelled') ?></p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-times-circle fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-header aps-cp-card-header">
        <h5 class="card-title mb-0">
            <i class="fas fa-filter"></i> <?= __('admin_filters') ?>
        </h5>
    </div>
    <div class="card-body aps-cp-card-body">
        <form method="GET" action="<?php echo BASE_URL; ?>/admin/bookings">
            <div class="row">
                <div class="col-md-3">
                    <label for="search" class="form-label"><?= __('admin_search') ?></label>
                    <input type="text" class="form-control" id="search" name="search"
                        value="<?= htmlspecialchars($filters['search'] ?? '') ?>"
                        placeholder="Booking #, Customer, Property">
                </div>
                <div class="col-md-2">
                    <label for="status" class="form-label"><?= __('admin_status') ?></label>
                    <select class="form-select" id="status" name="status">
                         <option value="">All Statuses</option>
                         <option value="token_paid" <?= $filters['status'] == 'token_paid' ? 'selected' : '' ?>>Token Paid</option>
                         <option value="agreement_signed" <?= $filters['status'] == 'agreement_signed' ? 'selected' : '' ?>>Agreement Signed</option>
                         <option value="emi_active" <?= $filters['status'] == 'emi_active' ? 'selected' : '' ?>>EMI Active</option>
                         <option value="partially_paid" <?= $filters['status'] == 'partially_paid' ? 'selected' : '' ?>>Partially Paid</option>
                         <option value="fully_paid" <?= $filters['status'] == 'fully_paid' ? 'selected' : '' ?>>Fully Paid</option>
                         <option value="cancelled" <?= $filters['status'] == 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                         <option value="transferred" <?= $filters['status'] == 'transferred' ? 'selected' : '' ?>>Transferred</option>
                         <option value="registration_done" <?= $filters['status'] == 'registration_done' ? 'selected' : '' ?>>Registry Done</option>
                    </select>
                </div>
                 <div class="col-md-2">
                     <label for="colony_id" class="form-label">Colony</label>
                     <select class="form-select" id="colony_id" name="colony_id">
                         <option value="">All Colonies</option>
                         <?php foreach ($colonies ?? [] as $col): ?>
                             <option value="<?= $col['id'] ?>" <?= ($filters['colony_id'] ?? '') == $col['id'] ? 'selected' : '' ?>>
                                 <?= htmlspecialchars($col['name'] ?? '') ?>
                             </option>
                         <?php endforeach; ?>
                     </select>
                 </div>
                 <div class="col-md-2">
                     <label for="associate_id" class="form-label"><?= __('admin_associate') ?></label>
                     <select class="form-select" id="associate_id" name="associate_id">
                         <option value="">All Associates</option>
                         <?php foreach ($associates ?? [] as $assoc): ?>
                             <option value="<?= $assoc['id'] ?>" <?= ($filters['associate_id'] ?? '') == $assoc['id'] ? 'selected' : '' ?>>
                                 <?= htmlspecialchars($assoc['name'] ?? '') ?>
                             </option>
                         <?php endforeach; ?>
                     </select>
                 </div>
                <div class="col-md-3">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> <?= __('admin_filter_btn') ?>
                        </button>
                        <a href="<?php echo BASE_URL; ?>/admin/bookings" class="btn btn-secondary">
                            <i class="fas fa-times"></i> <?= __('admin_clear') ?>
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Bookings Table -->
<div class="card aps-cp-card">
    <div class="card-header aps-cp-card-header">
        <h5 class="card-title mb-0">
            <i class="fas fa-list"></i> <?= __('admin_bookings_list') ?>
        </h5>
    </div>
    <div class="card-body aps-cp-card-body">
        <!-- Bulk Actions Bar (hidden by default) -->
        <div class="card border-0 shadow-sm mb-3" id="bulkActionsBar" style="display: none;">
            <div class="card-body py-2 d-flex align-items-center gap-3 flex-wrap">
                <span class="fw-semibold"><span id="selectedCount">0</span> selected</span>
                <select id="bulkStatus" class="form-select form-select-sm" style="width: auto; display: inline-block;">
                    <option value="pending">Pending</option>
                    <option value="token_paid">Token Paid</option>
                    <option value="agreement_signed">Agreement Signed</option>
                    <option value="emi_active">EMI Active</option>
                    <option value="partially_paid">Partially Paid</option>
                    <option value="fully_paid">Fully Paid</option>
                    <option value="registration_done">Registry Done</option>
                    <option value="transferred">Transferred</option>
                    <option value="cancelled">Cancelled</option>
                </select>
                <input type="text" id="bulkNotes" class="form-control form-control-sm" style="width: 200px; display: inline-block;" placeholder="Notes (optional)">
                <button type="button" class="btn btn-sm btn-warning" id="bulkApply">Apply Status</button>
                <button type="button" class="btn btn-sm btn-outline-danger" id="bulkCancel">Cancel</button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th width="30"><input type="checkbox" id="selectAll" class="form-check-input"></th>
                        <th>
                            <a href="?sort=booking_number&order=<?= $filters['order'] == 'ASC' ? 'DESC' : 'ASC' ?>&<?= http_build_query(array_diff_key($filters, ['sort' => '', 'order' => ''])) ?>">
                                Booking # <i class="fas fa-sort"></i>
                            </a>
                        </th>
                        <th><?= __('admin_property') ?></th>
                        <th><?= __('admin_customer') ?></th>
                        <th><?= __('admin_associate') ?></th>
                        <th><?= __('admin_amount') ?></th>
                        <th><?= __('admin_status') ?></th>
                        <th><?= __('admin_date') ?></th>
                        <th><?= __('admin_actions') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($bookings)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <i class="fas fa-file-contract fa-3x text-muted mb-3 d-block"></i>
                                <h5 class="text-muted">No bookings yet</h5>
                                <p class="text-muted mb-3">Start by creating your first booking to track payments, EMI schedules, and commissions.</p>
                                <a href="<?= BASE_URL ?>/admin/sales/bookings/new" class="btn btn-primary"><i class="fas fa-plus me-1"></i>Create Booking (Sales)</a>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($bookings as $booking): ?>
                            <tr data-booking-id="<?= $booking['id'] ?>">
                                <td><input type="checkbox" class="form-check-input booking-checkbox" value="<?= $booking['id'] ?>"></td>
                                <td>
                                <td>
                                    <strong><?= htmlspecialchars($booking['plot_number'] ?? 'N/A') ?></strong>
                                    <br>
                                    <small class="text-muted"><?= htmlspecialchars($booking['colony_name'] ?? '') ?></small>
                                </td>
                                <td>
                                    <?= htmlspecialchars($booking['customer_name'] ?? 'N/A') ?>
                                    <br>
                                    <small class="text-muted"><?= htmlspecialchars($booking['customer_email'] ?? '') ?></small>
                                </td>
                                <td>
                                    <?php if (!empty($booking['associate_name'])): ?>
                                        <?= htmlspecialchars($booking['associate_name'] ?? '') ?>
                                        <br>
                                        <small class="text-muted"><?= htmlspecialchars($booking['associate_email'] ?? '') ?></small>
                                    <?php else: ?>
                                        <span class="text-muted"><?= __('admin_direct_booking') ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong>₹<?= number_format(floatval($booking['total_amount'] ?? 0), 2) ?></strong>
                                </td>
                                <td>
                                    <?php
                                    $statusColors = [
                                        'pending' => 'warning',
                                        'confirmed' => 'success',
                                        'completed' => 'info',
                                        'cancelled' => 'danger'
                                    ];
                                    $color = $statusColors[$booking['status']] ?? 'secondary';
                                    ?>
                                    <span class="badge bg-<?= $color ?>">
                                        <?= ucfirst(htmlspecialchars($booking['status'] ?? '')) ?>
                                    </span>
                                </td>
                                <td>
                                    <?= date('d M Y', strtotime($booking['created_at'])) ?>
                                    <br>
                                    <small class="text-muted"><?= date('h:i A', strtotime($booking['created_at'])) ?></small>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="<?php echo BASE_URL; ?>/admin/bookings/<?= $booking['id'] ?>" class="btn btn-outline-primary" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="<?php echo BASE_URL; ?>/admin/bookings/<?= $booking['id'] ?>/edit" class="btn btn-outline-warning" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" class="btn btn-outline-danger"
                                            onclick="confirmDelete(<?= $booking['id'] ?>, '<?= htmlspecialchars($booking['booking_number'] ?? '') ?>')"
                                            title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
            <nav aria-label="Bookings pagination">
                <ul class="pagination justify-content-center">
                    <?php if ($current_page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?= $current_page - 1 ?>&<?= http_build_query(array_diff_key($filters, ['page' => ''])) ?>">
                                <?= __('admin_previous') ?>
                            </a>
                        </li>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <?php if ($i == $current_page): ?>
                            <li class="page-item active">
                                <span class="page-link"><?= $i ?></span>
                            </li>
                        <?php else: ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?= $i ?>&<?= http_build_query(array_diff_key($filters, ['page' => ''])) ?>">
                                    <?= $i ?>
                                </a>
                            </li>
                        <?php endif; ?>
                    <?php endfor; ?>

                    <?php if ($current_page < $total_pages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?= $current_page + 1 ?>&<?= http_build_query(array_diff_key($filters, ['page' => ''])) ?>">
                                <?= __('admin_next') ?>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        <?php endif; ?>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel"><?= __('admin_confirm_delete') ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <?= __('admin_confirm_delete_booking') ?> <strong id="deleteBookingNumber"></strong>?<br>
                <?= __('admin_delete_warning') ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?= __('admin_cancel') ?></button>
                <form id="deleteForm" method="POST" action="<?= BASE_URL ?>/admin/bookings/0/destroy" class="style-26772">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                    <button type="submit" class="btn btn-danger"><?= __('admin_delete') ?></button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    var baseUrl = '<?php echo BASE_URL; ?>';

    function confirmDelete(bookingId, bookingNumber) {
        document.getElementById('deleteBookingNumber').textContent = bookingNumber;
        document.getElementById('deleteForm').action = baseUrl + '/admin/bookings/' + bookingId + '/destroy';

        const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
        modal.show();
    }

    // Bulk Actions
    document.addEventListener('DOMContentLoaded', function() {
        const selectAll = document.getElementById('selectAll');
        const checkboxes = document.querySelectorAll('.booking-checkbox');
        const bulkBar = document.getElementById('bulkActionsBar');
        const countEl = document.getElementById('selectedCount');
        const bulkStatus = document.getElementById('bulkStatus');
        const bulkNotes = document.getElementById('bulkNotes');
        const bulkApply = document.getElementById('bulkApply');
        const bulkCancel = document.getElementById('bulkCancel');

        if (!selectAll || !bulkBar) return;

        function getSelected() {
            return [...checkboxes].filter(cb => cb.checked).map(cb => parseInt(cb.value));
        }

        function updateUI() {
            const count = getSelected().length;
            if (countEl) countEl.textContent = count;
            bulkBar.style.display = count > 0 ? 'block' : 'none';
        }

        selectAll.addEventListener('change', function() {
            checkboxes.forEach(cb => cb.checked = this.checked);
            updateUI();
        });
        checkboxes.forEach(cb => cb.addEventListener('change', updateUI));

        bulkApply.addEventListener('click', function() {
            const ids = getSelected();
            const status = bulkStatus.value;
            const notes = bulkNotes.value;
            if (!ids.length) return alert('No bookings selected');
            if (!confirm('Update status of ' + ids.length + ' booking(s) to ' + status + '?')) return;

            fetch(baseUrl + '/admin/bookings/bulk-action', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded', 'X-CSRF-TOKEN': '<?= $_SESSION['csrf_token'] ?? '' ?>'},
                body: 'csrf_token=<?= $_SESSION['csrf_token'] ?? '' ?>&action=status&value=' + encodeURIComponent(status) + '&notes=' + encodeURIComponent(notes) + '&booking_ids[]=' + ids.join('&booking_ids[]=')
            }).then(r => r.json()).then(d => {
                if (d.success) location.reload();
                else alert(d.error || 'Failed');
            });
        });

        bulkCancel.addEventListener('click', function() {
            const ids = getSelected();
            if (!ids.length) return alert('No bookings selected');
            if (!confirm('Cancel ' + ids.length + ' booking(s)?')) return;

            fetch(baseUrl + '/admin/bookings/bulk-action', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded', 'X-CSRF-TOKEN': '<?= $_SESSION['csrf_token'] ?? '' ?>'},
                body: 'csrf_token=<?= $_SESSION['csrf_token'] ?? '' ?>&action=cancel&booking_ids[]=' + ids.join('&booking_ids[]=')
            }).then(r => r.json()).then(d => {
                if (d.success) location.reload();
                else alert(d.error || 'Failed');
            });
        });
    });
</script>

