<?php $page_title = 'Demand Letters'; ?>
<?php include __DIR__ . '/../../layouts/admin.php'; ?>

<div class="page-content">
    <div class="container-fluid">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0"><i class="fas fa-envelope me-2 text-primary"></i>Demand Letters</h1>
                <p class="text-muted mb-0">Manage and send EMI demand letters to customers</p>
            </div>
            <a href="<?= $base ?>/admin/finance/demand-letters/create" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i>Generate Letter
            </a>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle bg-primary bg-opacity-10 p-3 me-3">
                                <i class="fas fa-envelope fa-lg text-primary"></i>
                            </div>
                            <div>
                                <div class="text-muted small">Total Letters</div>
                                <div class="h4 mb-0 fw-bold"><?= (int)($stats['total'] ?? 0) ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle bg-info bg-opacity-10 p-3 me-3">
                                <i class="fas fa-paper-plane fa-lg text-info"></i>
                            </div>
                            <div>
                                <div class="text-muted small">Sent</div>
                                <div class="h4 mb-0 fw-bold"><?= (int)($stats['sent'] ?? 0) ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle bg-success bg-opacity-10 p-3 me-3">
                                <i class="fas fa-check-circle fa-lg text-success"></i>
                            </div>
                            <div>
                                <div class="text-muted small">Paid</div>
                                <div class="h4 mb-0 fw-bold"><?= (int)($stats['paid'] ?? 0) ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle bg-danger bg-opacity-10 p-3 me-3">
                                <i class="fas fa-exclamation-triangle fa-lg text-danger"></i>
                            </div>
                            <div>
                                <div class="text-muted small">Overdue</div>
                                <div class="h4 mb-0 fw-bold text-danger"><?= (int)($stats['overdue'] ?? 0) ?></div>
                                <div class="text-muted small">₹<?= number_format((float)($stats['total_amount'] ?? 0), 2, '.', ',') ?> outstanding</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <form method="GET" action="<?= $base ?>/admin/finance/demand-letters" class="row g-3 align-items-end">
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold">Status</label>
                        <select name="status" class="form-select form-select-sm">
                            <option value="">All Statuses</option>
                            <?php foreach (['drafted' => 'Drafted', 'sent' => 'Sent', 'viewed' => 'Viewed', 'paid' => 'Paid', 'overdue' => 'Overdue'] as $val => $label): ?>
                                <option value="<?= $val ?>" <?= ($filters['status'] ?? '') === $val ? 'selected' : '' ?>><?= $label ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold">Colony</label>
                        <select name="colony_id" class="form-select form-select-sm">
                            <option value="">All Colonies</option>
                            <?php foreach (($colonies ?? []) as $col): ?>
                                <option value="<?= (int)$col['id'] ?>" <?= (int)($filters['colony_id'] ?? 0) === (int)$col['id'] ? 'selected' : '' ?>><?= htmlspecialchars($col['name'] ?? '') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold">Overdue</label>
                        <select name="overdue_days" class="form-select form-select-sm">
                            <option value="">All</option>
                            <option value="0" <?= ($filters['overdue_days'] ?? '') === '0' ? 'selected' : '' ?>>Any Overdue</option>
                            <option value="7" <?= ($filters['overdue_days'] ?? '') === '7' ? 'selected' : '' ?>>7+ days</option>
                            <option value="15" <?= ($filters['overdue_days'] ?? '') === '15' ? 'selected' : '' ?>>15+ days</option>
                            <option value="30" <?= ($filters['overdue_days'] ?? '') === '30' ? 'selected' : '' ?>>30+ days</option>
                            <option value="60" <?= ($filters['overdue_days'] ?? '') === '60' ? 'selected' : '' ?>>60+ days</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold">Search</label>
                        <input type="text" name="search" class="form-control form-control-sm" placeholder="Booking # or customer..." value="<?= htmlspecialchars($filters['search'] ?? '') ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold">Date From</label>
                        <input type="date" name="date_from" class="form-control form-control-sm" value="<?= htmlspecialchars($filters['date_from'] ?? '') ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold">Date To</label>
                        <input type="date" name="date_to" class="form-control form-control-sm" value="<?= htmlspecialchars($filters['date_to'] ?? '') ?>">
                    </div>
                    <div class="col-md-6 text-muted small">
                        Filters: colony, status, overdue days — tenant-scoped.
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary btn-sm w-100"><i class="fas fa-filter me-1"></i>Filter</button>
                    </div>
                    <div class="col-md-3">
                        <a href="<?= $base ?>/admin/finance/demand-letters" class="btn btn-outline-secondary btn-sm w-100"><i class="fas fa-times me-1"></i>Clear</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-body p-0">
                <?php if (empty($letters)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-envelope fa-3x text-muted mb-3"></i>
                        <p class="text-muted mb-0">No demand letters found</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom bg-light">
                            <small class="text-muted">Showing <?= count($letters) ?> letters</small>
                            <a href="<?= $base ?>/admin/finance/demand-letters/export?<?= http_build_query($filters) ?>" class="btn btn-sm btn-outline-success"><i class="fas fa-file-csv me-1"></i>Export CSV</a>
                        </div>
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Letter #</th>
                                    <th>Booking #</th>
                                    <th>Colony</th>
                                    <th>Customer Name</th>
                                    <th class="text-end">Amount</th>
                                    <th class="text-center">Status</th>
                                    <th>Due Date</th>
                                    <th>Overdue</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($letters as $letter): ?>
                                    <?php
                                        $badgeClass = match($letter['status'] ?? 'drafted') {
                                            'paid' => 'success',
                                            'overdue' => 'danger',
                                            'sent' => 'info',
                                            'viewed' => 'primary',
                                            default => 'secondary',
                                        };
                                    ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($letter['letter_number'] ?? '') ?></strong></td>
                                        <td><span class="text-muted"><?= htmlspecialchars($letter['booking_number'] ?? '') ?></span></td>
                                        <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($letter['colony_name'] ?? '—') ?></span></td>
                                        <td>
                                            <div class="fw-semibold"><?= htmlspecialchars($letter['customer_name'] ?? '') ?></div>
                                            <small class="text-muted"><?= htmlspecialchars($letter['customer_email'] ?? '') ?></small>
                                        </td>
                                        <td class="text-end fw-bold">₹<?= number_format((float)($letter['amount'] ?? 0), 2, '.', ',') ?></td>
                                        <td class="text-center">
                                            <span class="badge bg-<?= $badgeClass ?>"><?= strtoupper($letter['status'] ?? 'drafted') ?></span>
                                        </td>
                                        <td><small><?= htmlspecialchars($letter['due_date'] ?? '') ?></small><br><small class="text-muted"><?= htmlspecialchars($letter['generated_date'] ?? '') ?></small></td>
                                        <td class="text-center">
                                            <?php $od = (int)($letter['overdue_days_calc'] ?? 0); if (($letter['status'] ?? '') !== 'paid' && $od > 0): ?>
                                                <span class="badge bg-danger"><?= $od ?>d overdue</span>
                                            <?php elseif (($letter['status'] ?? '') !== 'paid' && $od === 0): ?>
                                                <span class="badge bg-warning text-dark">Due today</span>
                                            <?php elseif (($letter['status'] ?? '') !== 'paid' && $od < 0): ?>
                                                <span class="badge bg-success"><?= abs($od) ?>d left</span>
                                            <?php else: ?>
                                                <span class="text-muted">—</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <a href="<?= $base ?>/admin/finance/demand-letters/<?= (int)$letter['id'] ?>/pdf" class="btn btn-sm btn-outline-primary" title="View PDF" target="_blank">
                                                <i class="fas fa-file-pdf"></i>
                                            </a>
                                            <?php if (in_array($letter['status'] ?? '', ['drafted', 'sent'])): ?>
                                                <button type="button" class="btn btn-sm btn-outline-success" title="Send via WhatsApp" onclick="sendWhatsApp(<?= (int)$letter['id'] ?>, '<?= htmlspecialchars($letter['customer_name'] ?? '') ?>')">
                                                    <i class="fab fa-whatsapp"></i>
                                                </button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <?php if (!empty($pagination) && ($pagination['total_pages'] ?? 1) > 1): ?>
            <nav class="mt-3">
                <ul class="pagination pagination-sm justify-content-center">
                    <li class="page-item <?= ($pagination['current_page'] ?? 1) <= 1 ? 'disabled' : '' ?>">
                        <a class="page-link" href="<?= $base ?>/admin/finance/demand-letters?<?= http_build_query(array_merge($filters, ['page' => ($pagination['current_page'] ?? 1) - 1])) ?>">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    </li>
                    <?php
                    $currentPage = $pagination['current_page'] ?? 1;
                    $totalPages = $pagination['total_pages'] ?? 1;
                    $startPage = max(1, $currentPage - 2);
                    $endPage = min($totalPages, $currentPage + 2);
                    ?>
                    <?php if ($startPage > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="<?= $base ?>/admin/finance/demand-letters?<?= http_build_query(array_merge($filters, ['page' => 1])) ?>">1</a>
                        </li>
                        <?php if ($startPage > 2): ?>
                            <li class="page-item disabled"><span class="page-link">...</span></li>
                        <?php endif; ?>
                    <?php endif; ?>
                    <?php for ($p = $startPage; $p <= $endPage; $p++): ?>
                        <li class="page-item <?= $p === $currentPage ? 'active' : '' ?>">
                            <a class="page-link" href="<?= $base ?>/admin/finance/demand-letters?<?= http_build_query(array_merge($filters, ['page' => $p])) ?>"><?= $p ?></a>
                        </li>
                    <?php endfor; ?>
                    <?php if ($endPage < $totalPages): ?>
                        <?php if ($endPage < $totalPages - 1): ?>
                            <li class="page-item disabled"><span class="page-link">...</span></li>
                        <?php endif; ?>
                        <li class="page-item">
                            <a class="page-link" href="<?= $base ?>/admin/finance/demand-letters?<?= http_build_query(array_merge($filters, ['page' => $totalPages])) ?>"><?= $totalPages ?></a>
                        </li>
                    <?php endif; ?>
                    <li class="page-item <?= ($pagination['current_page'] ?? 1) >= $totalPages ? 'disabled' : '' ?>">
                        <a class="page-link" href="<?= $base ?>/admin/finance/demand-letters?<?= http_build_query(array_merge($filters, ['page' => ($pagination['current_page'] ?? 1) + 1])) ?>">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    </li>
                </ul>
                <div class="text-center text-muted small mt-1">
                    Showing <?= (($pagination['current_page'] ?? 1) - 1) * ($pagination['per_page'] ?? 25) + 1 ?>
                    - <?= min(($pagination['current_page'] ?? 1) * ($pagination['per_page'] ?? 25), $pagination['total'] ?? 0) ?>
                    of <?= $pagination['total'] ?? 0 ?> letters
                </div>
            </nav>
        <?php endif; ?>

    </div>
</div>

<script>
function sendWhatsApp(letterId, customerName) {
    if (!confirm('Send demand letter via WhatsApp to ' + customerName + '?')) return;
    fetch('<?= $base ?>/admin/finance/demand-letters/' + letterId + '/send-whatsapp', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content || ''
        }
    })
    .catch(() => alert('Network error. Please try again.'))
    .then(() => location.reload());
}
</script>
