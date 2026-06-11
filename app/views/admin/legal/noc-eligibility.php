<div class="aps-cp-card">
    <div class="aps-cp-card-header">
        <span>Registry / NOC Eligibility Check</span>
        <span class="badge bg-info">Financial Clearance</span>
    </div>
    <div class="aps-cp-card-body">
        <p class="text-muted mb-3">
            Select a booking and check whether all financial obligations are cleared
            before proceeding with Registry or NOC.
        </p>

        <form method="POST" action="<?= BASE_URL ?>/admin/legal/noc-check" class="row g-3 mb-4">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '', ENT_QUOTES, 'UTF-8') ?>">

            <div class="col-md-6">
                <label class="form-label fw-medium">Booking</label>
                <select name="booking_id" class="form-select" required>
                    <option value="">-- Select Booking --</option>
                    <?php foreach (($bookings ?? []) as $bk): ?>
                    <option value="<?= (int)$bk['id'] ?>" <?= ((int)($booking_id ?? 0) === (int)$bk['id']) ? 'selected' : '' ?>>
                        [#<?= (int)$bk['id'] ?>] <?= htmlspecialchars($bk['booking_number'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                        — <?= htmlspecialchars($bk['customer_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                        (Plot <?= htmlspecialchars($bk['plot_number'] ?? '', ENT_QUOTES, 'UTF-8') ?>)
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label fw-medium">Check Type</label>
                <select name="check_type" class="form-select">
                    <option value="registry" <?= (($check_type ?? 'registry') === 'registry') ? 'selected' : '' ?>>Registry</option>
                    <option value="noc" <?= (($check_type ?? '') === 'noc') ? 'selected' : '' ?>>NOC</option>
                </select>
            </div>

            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-search"></i> Check
                </button>
            </div>
        </form>

        <?php if ($result !== null): ?>
        <hr>

        <!-- Booking Summary -->
        <?php if (!empty($result['booking'])): $b = $result['booking']; ?>
        <div class="row mb-3">
            <div class="col-md-3"><strong>Booking:</strong> <?= htmlspecialchars($b['booking_number'] ?? '', ENT_QUOTES, 'UTF-8') ?></div>
            <div class="col-md-3"><strong>Customer:</strong> <?= htmlspecialchars($b['customer_name'] ?? '', ENT_QUOTES, 'UTF-8') ?></div>
            <div class="col-md-3"><strong>Plot:</strong> <?= htmlspecialchars($b['plot_number'] ?? '', ENT_QUOTES, 'UTF-8') ?> (<?= htmlspecialchars($b['colony_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>)</div>
            <div class="col-md-3"><strong>Total:</strong> ₹<?= number_format($b['total_amount'] ?? 0, 2) ?></div>
        </div>
        <?php endif; ?>

        <!-- Verdict -->
        <?php if ($result['eligible'] ?? false): ?>
        <div class="alert alert-success fs-5 text-center">
            <i class="fas fa-check-circle"></i>
            ELIGIBLE — No financial blocks. Can proceed with
            <?= htmlspecialchars($check_type ?? 'registry', ENT_QUOTES, 'UTF-8') ?>.
        </div>
        <?php else: ?>
        <div class="alert alert-danger">
            <i class="fas fa-times-circle"></i>
            <strong>NOT ELIGIBLE</strong> — The following blocks must be resolved:
            <ul class="mb-0 mt-1">
                <?php foreach (($result['blocks'] ?? []) as $block): ?>
                <li><?= htmlspecialchars($block, ENT_QUOTES, 'UTF-8') ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <!-- Financial Summary -->
        <div class="row g-3 mb-3">
            <div class="col-md-3">
                <div class="border rounded p-3 text-center <?= ($result['total_due'] ?? 0) > 0 ? 'bg-danger bg-opacity-10' : 'bg-success bg-opacity-10' ?>">
                    <div class="fs-4 fw-bold">₹<?= number_format($result['total_due'] ?? 0, 2) ?></div>
                    <div class="small text-muted">Outstanding Dues</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="border rounded p-3 text-center <?= ($result['total_penalty'] ?? 0) > 0 ? 'bg-danger bg-opacity-10' : 'bg-success bg-opacity-10' ?>">
                    <div class="fs-4 fw-bold">₹<?= number_format($result['total_penalty'] ?? 0, 2) ?></div>
                    <div class="small text-muted">Accrued Penalties</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="border rounded p-3 text-center <?= ($result['has_registry'] ?? false) ? 'bg-warning bg-opacity-10' : 'bg-success bg-opacity-10' ?>">
                    <div class="fs-4 fw-bold"><?= ($result['has_registry'] ?? false) ? 'Yes' : 'No' ?></div>
                    <div class="small text-muted">Existing Registry</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="border rounded p-3 text-center <?= ($result['has_noc'] ?? false) ? 'bg-warning bg-opacity-10' : 'bg-success bg-opacity-10' ?>">
                    <div class="fs-4 fw-bold"><?= ($result['has_noc'] ?? false) ? 'Yes' : 'No' ?></div>
                    <div class="small text-muted">Existing NOC</div>
                </div>
            </div>
        </div>

        <!-- Payment Schedule Table -->
        <?php if (!empty($schedule)): ?>
        <h5 class="mt-4">Payment Schedule</h5>
        <div class="table-responsive">
            <table class="table table-bordered table-sm">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Type</th>
                        <th>Due Date</th>
                        <th>Amount</th>
                        <th>Paid</th>
                        <th>Shortfall</th>
                        <th>Penalty</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($schedule as $inst): 
                        $shortfall = max(0, (float)$inst['amount'] - (float)$inst['paid_amount']);
                        $instStatus = $inst['status'] ?? 'pending';
                        $rowClass = match ($instStatus) {
                            'paid' => 'table-success',
                            'overdue' => 'table-danger',
                            'partial' => 'table-warning',
                            default => '',
                        };
                    ?>
                    <tr class="<?= $rowClass ?>">
                        <td><?= (int)$inst['installment_number'] ?></td>
                        <td><?= htmlspecialchars($inst['installment_type'] ?? 'emi', ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($inst['due_date'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                        <td>₹<?= number_format((float)$inst['amount'], 2) ?></td>
                        <td>₹<?= number_format((float)$inst['paid_amount'], 2) ?></td>
                        <td class="<?= $shortfall > 0 ? 'text-danger fw-bold' : '' ?>">
                            <?= $shortfall > 0 ? '₹' . number_format($shortfall, 2) : '—' ?>
                        </td>
                        <td class="<?= ((float)($inst['accrued_penalty'] ?? 0)) > 0 ? 'text-danger fw-bold' : '' ?>">
                            <?= ((float)($inst['accrued_penalty'] ?? 0)) > 0 ? '₹' . number_format((float)$inst['accrued_penalty'], 2) : '—' ?>
                        </td>
                        <td>
                            <span class="badge bg-<?= match($instStatus) { 'paid' => 'success', 'overdue' => 'danger', 'partial' => 'warning', default => 'secondary' } ?>">
                                <?= ucfirst($instStatus) ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>

        <!-- Existing Registry / NOC Status -->
        <div class="row g-3 mt-2">
            <?php if (!empty($registry_status)): ?>
            <div class="col-md-6">
                <div class="card border-info">
                    <div class="card-header bg-info text-white">Registry Status</div>
                    <div class="card-body small">
                        <div>Reg No: <?= htmlspecialchars($registry_status['registration_no'] ?? '—', ENT_QUOTES, 'UTF-8') ?></div>
                        <div>Office: <?= htmlspecialchars($registry_status['sub_registrar_office'] ?? '—', ENT_QUOTES, 'UTF-8') ?></div>
                        <div>Date: <?= htmlspecialchars($registry_status['registration_date'] ?? '—', ENT_QUOTES, 'UTF-8') ?></div>
                        <div>Status: <span class="badge bg-<?= $registry_status['status'] === 'completed' ? 'success' : 'warning' ?>"><?= $registry_status['status'] ?? '—' ?></span></div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            <?php if (!empty($noc_status)): ?>
            <div class="col-md-6">
                <div class="card border-warning">
                    <div class="card-header bg-warning text-white">NOC Status</div>
                    <div class="card-body small">
                        <div>Requested By: <?= htmlspecialchars($noc_status['requested_by_name'] ?? '—', ENT_QUOTES, 'UTF-8') ?></div>
                        <div>Status: <span class="badge bg-<?= $noc_status['status'] === 'approved' ? 'success' : ($noc_status['status'] === 'blocked' ? 'danger' : 'warning') ?>"><?= $noc_status['status'] ?? '—' ?></span></div>
                        <?php if (!empty($noc_status['rejection_reason'])): ?>
                        <div>Reason: <?= htmlspecialchars($noc_status['rejection_reason'], ENT_QUOTES, 'UTF-8') ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</div>
