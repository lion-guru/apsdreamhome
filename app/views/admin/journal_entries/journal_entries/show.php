<?php
// Journal Entry Show View
$entry = $entry ?? [];
$lines = $lines ?? [];
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1>Journal Entry: <?= htmlspecialchars($entry['journal_number'] ?? '') ?></h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/accounting">Accounting</a></li>
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/journal-entries">Journal Entries</a></li>
                    <li class="breadcrumb-item active">View</li>
                </ol>
            </nav>
        </div>
        <div>
            <?php if (($entry['status'] ?? 'draft') === 'draft'): ?>
            <form method="POST" action="<?= BASE_URL ?>/admin/journal-entries/approve/<?= $entry['id'] ?>" class="d-inline" onsubmit="return confirm('Approve this journal entry?');">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                <button type="submit" class="btn btn-success"><i class="fas fa-check me-2"></i>Approve</button>
            </form>
            <form method="POST" action="<?= BASE_URL ?>/admin/journal-entries/reject/<?= $entry['id'] ?>" class="d-inline ms-2" onsubmit="return confirm('Reject this journal entry?');">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                <button type="submit" class="btn btn-danger"><i class="fas fa-times me-2"></i>Reject</button>
            </form>
            <?php endif; ?>
            <a href="<?= BASE_URL ?>/admin/journal-entries" class="btn btn-secondary ms-2">Back to List</a>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted">Journal Number</h6>
                    <p class="mb-0 fw-bold"><code><?= htmlspecialchars($entry['journal_number'] ?? '') ?></code></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted">Entry Date</h6>
                    <p class="mb-0 fw-bold"><?= date('M d, Y', strtotime($entry['entry_date'] ?? '')) ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted">Type</h6>
                    <p class="mb-0"><span class="badge bg-secondary"><?= ucfirst($entry['entry_type'] ?? 'manual') ?></span></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted">Status</h6>
                    <p class="mb-0"><span class="badge bg-<?= match($entry['status'] ?? 'draft') { 'approved' => 'success', 'rejected' => 'danger', default => 'warning' } ?>"><?= ucfirst($entry['status'] ?? 'draft') ?></span></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted">Reference</h6>
                    <p class="mb-0 fw-bold"><?= htmlspecialchars($entry['reference_number'] ?? '—') ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted">Source Document</h6>
                    <p class="mb-0"><?= ucfirst($entry['source_document'] ?? '—') ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted">Created By</h6>
                    <p class="mb-0"><?= htmlspecialchars($entry['created_by_name'] ?? '—') ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted">Created At</h6>
                    <p class="mb-0"><?= date('M d, Y H:i', strtotime($entry['created_at'] ?? '')) ?></p>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Narration</h5>
        </div>
        <div class="card-body">
            <p class="mb-0"><?= nl2br(htmlspecialchars($entry['description'] ?? '')) ?></p>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Journal Lines</h5>
            <div class="text-end">
                <span class="badge bg-success fs-6">Total Debit: <?= number_format(array_sum(array_column($lines, 'debit_amount')), 2) ?></span>
                <span class="badge bg-danger fs-6 ms-2">Total Credit: <?= number_format(array_sum(array_column($lines, 'credit_amount')), 2) ?></span>
                <span class="badge bg-<?= abs(array_sum(array_column($lines, 'debit_amount')) - array_sum(array_column($lines, 'credit_amount'))) < 0.01 ? 'success' : 'danger' ?> fs-6 ms-2">
                    <?= abs(array_sum(array_column($lines, 'debit_amount')) - array_sum(array_column($lines, 'credit_amount'))) < 0.01 ? 'Balanced' : 'UNBALANCED' ?>
                </span>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Account Code</th>
                            <th>Account Name</th>
                            <th>Type</th>
                            <th class="text-end">Debit</th>
                            <th class="text-end">Credit</th>
                            <th>Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($lines as $i => $line): ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td><code><?= htmlspecialchars($line['account_code'] ?? '') ?></code></td>
                            <td><?= htmlspecialchars($line['account_name'] ?? '') ?></td>
                            <td><span class="badge bg-<?= match($line['account_type'] ?? '') { 'asset' => 'success', 'liability' => 'danger', 'equity' => 'primary', 'income' => 'info', 'expense' => 'warning', default => 'secondary' } ?>"><?= ucfirst($line['account_type'] ?? '') ?></span></td>
                            <td class="text-end text-success fw-bold"><?= number_format((float)$line['debit_amount'], 2) ?></td>
                            <td class="text-end text-danger fw-bold"><?= number_format((float)$line['credit_amount'], 2) ?></td>
                            <td><?= htmlspecialchars($line['description'] ?? '') ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot class="table-light fw-bold">
                        <tr>
                            <td colspan="4" class="text-end">Totals</td>
                            <td class="text-end text-success"><?= number_format(array_sum(array_column($lines, 'debit_amount')), 2) ?></td>
                            <td class="text-end text-danger"><?= number_format(array_sum(array_column($lines, 'credit_amount')), 2) ?></td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>